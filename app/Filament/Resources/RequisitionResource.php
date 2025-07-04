<?php

namespace App\Filament\Resources;


use App\Filament\Infolists\Components\QuotationEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use App\Filament\Resources\RequisitionResource\Pages;
use App\Filament\Resources\RequisitionResource\RelationManagers;
use App\Models\Requisition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product; 
use Filament\Forms\Components\Section; 
use Filament\Forms\Components\Repeater;
use App\Enums\RequisitionStatus; 
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Illuminate\Support\Facades\Storage;

class RequisitionResource extends Resource
{
    protected static ?string $model = Requisition::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
{
    return $form->schema([
        Section::make('Requisition Details')->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('requester', 'name')
                ->label('Requester')
                ->default(auth()->id())
                ->required(),
            Forms\Components\Select::make('status')
                ->options(RequisitionStatus::class)
                ->default(RequisitionStatus::PENDING)
                ->required(),
                Forms\Components\TextInput::make('total_amount')
                ->prefix('MYR')
                ->numeric()
                ->readOnly() // User cannot edit this
                ->label('Grand Total'),
        ])->columns(2),

        Section::make('Vendor Quotation')->schema([
            Forms\Components\Select::make('vendor_id')
                ->relationship('vendor', 'name')
                ->searchable()
                ->createOptionForm([ // Allows creating new vendors on the fly!
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('email')->email(),
                    Forms\Components\TextInput::make('phone'),
                ])
                ->required(),

                Forms\Components\TextInput::make('quotation_number')
                ->label('Quotation Reference #')
                ->required(),

            FileUpload::make('quotation_file_path')
                ->label('Quotation Document (PDF)')
                ->disk('public') // Tells Filament to use the public disk
                ->directory('quotations') // Organizes files in storage/app/public/quotations
                ->required(),

        ])->columns(3),

        Section::make('Items')
    ->schema([
        Repeater::make('items')
            ->relationship()
            ->schema([
                // This 'Select' for products is now well-configured
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name') 
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('price', Product::find($state)?->price ?? 0))
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('MYR'),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        return Product::create($data)->id;
                    }),

                // This field is now simpler
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->reactive(), // Still reactive to trigger the parent update

                // This field is also simpler
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->prefix('MYR')
                    ->reactive(), // Still reactive to trigger the parent update
            ])
            // The logic is moved from the fields to the parent Repeater
            ->reactive()
            ->afterStateUpdated(function (callable $get, callable $set) {
                // This function now calculates the GRAND TOTAL
                self::updateTotals($get, $set); 
            })
            ->columns(3) // We are back to 3 columns now
            ->defaultItems(1)
            ->reorderableWithButtons()
            ->addActionLabel('Add Item'),
    ]),
    ]);
}

public static function table(Table $table): Table
{
    return $table->columns([
        Tables\Columns\TextColumn::make('id')->sortable(),
        Tables\Columns\TextColumn::make('requester.name')->searchable()->sortable(),
        BadgeColumn::make('status')
            ->colors([
                'warning' => RequisitionStatus::PENDING,
                'success' => RequisitionStatus::APPROVED,
                'danger' => RequisitionStatus::REJECTED,
            ]),
        Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
    ])
            ->filters([
                //
            ])
            ->actions([
                // --- THIS IS THE FIX ---
                // Add the ViewAction to show the 'eye' icon and trigger the infolist.
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\EditAction::make()
                // Only visible if user is an admin OR if the status is still Pending
                ->visible(fn ($record) => auth()->user()->is_admin || $record->status === RequisitionStatus::PENDING),
                
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function ($record) {
                        $record->status = RequisitionStatus::APPROVED;
                        $record->approved_by_id = auth()->id();
                        $record->approved_at = now();
                        $record->save();
                    })
                    ->visible(fn ($record) => $record->status === RequisitionStatus::PENDING && auth()->user()->is_admin),
                
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->action(function ($record) {
                        $record->status = RequisitionStatus::REJECTED;
                        $record->approved_by_id = auth()->id();
                        $record->approved_at = now();
                        $record->save();
                    })
                    ->visible(fn ($record) => $record->status === RequisitionStatus::PENDING && auth()->user()->is_admin),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function updateTotals(callable $get, callable $set): void
{
    // Retrieve all repeater items
    $items = $get('items');
    
    $grandTotal = 0;
    
    // Loop through each item and calculate its total
    foreach ($items as $item) {
        $grandTotal += $item['quantity'] * $item['price'];
    }
    
    // Set the grand total in the read-only field
    $set('total_amount', number_format($grandTotal, 2, '.', ''));
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canView(Model $record): bool
{
    return true;
}

// Controls access to the Edit PAGE itself.
// Should match the logic on your Edit button.
public static function canEdit(Model $record): bool
{
    // Allows admins to edit anytime.
    // Allows the original user to edit ONLY if the status is still Pending.
    if (auth()->user()->is_admin) {
        return true;
    }

    return $record->user_id === auth()->id() && $record->status === \App\Enums\RequisitionStatus::PENDING;
}

// Controls access to the Delete ACTION.
// Should match the logic on your Delete button.
public static function canDelete(Model $record): bool
{
    // Allows admins to delete anytime.
    // Allows the original user to delete ONLY if the status is still Pending.
    if (auth()->user()->is_admin) {
        return true;
    }
    
    return $record->user_id === auth()->id() && $record->status === \App\Enums\RequisitionStatus::PENDING;
}

public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            // Main details at the top
            InfolistSection::make('Requisition Information')
                ->schema([
                    TextEntry::make('requester.name'),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('total_amount')->money('MYR'),
                    TextEntry::make('created_at')->dateTime(),
                ])->columns(4),

            // --- THIS IS THE NEW, INTEGRATED QUOTATION SECTION ---
            InfolistSection::make('Quotation Details')
                ->schema([
                    TextEntry::make('vendor.name')->label('Vendor'),
                    TextEntry::make('quotation_number')->label('Quotation #'),
                    // This action will only display if a file path exists
                    Actions::make([
                        Action::make('download_quotation')
                            ->label('View & Download Quotation')
                            ->icon('heroicon-o-document-arrow-down')
                            ->color('gray')
                            ->url(fn ($record) => Storage::url($record->quotation_file_path), shouldOpenInNewTab: true)
                            ->visible(fn ($record) => $record->quotation_file_path),
                    ])->label('Attachment'),
                ])->columns(3),

            // The items section remains the same
            InfolistSection::make('Items')
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            TextEntry::make('product.name')->label('Product')->weight('bold'),
                            TextEntry::make('quantity'),
                            TextEntry::make('price')->money('MYR')->label('Price per Item'),
                        ])->columns(3),
                ]),
            ]);
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequisitions::route('/'),
            'create' => Pages\CreateRequisition::route('/create'),
            'view' => Pages\ViewRequisition::route('/{record}'), 
            'edit' => Pages\EditRequisition::route('/{record}/edit'),
        ];
    }
}
