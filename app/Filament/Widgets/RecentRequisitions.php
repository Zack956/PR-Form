<?php

namespace App\Filament\Widgets;

use App\Enums\RequisitionStatus;
use App\Filament\Resources\RequisitionResource;
use App\Models\Requisition; // Import the Requisition model
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentRequisitions extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Start with the base query from the resource
        $query = RequisitionResource::getEloquentQuery();

        // If the user is NOT an admin, only show their own requisitions
        if (! auth()->user()->is_admin) {
            $query->where('user_id', auth()->id());
        }

        return $table
            ->query($query) // Use our potentially modified query
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Req. #'),
                Tables\Columns\TextColumn::make('requester.name'),
                Tables\Columns\TextColumn::make('vendor.name'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => RequisitionStatus::PENDING,
                        'success' => RequisitionStatus::APPROVED,
                        'danger' => RequisitionStatus::REJECTED,
                    ]),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->actions([
                // --- THIS IS THE REVISED ACTION ---
                Tables\Actions\Action::make('view_or_edit')
                    // The label is always 'View' for consistency
                    ->label('View') 
                    // The URL depends on the user's role
                    ->url(function (Requisition $record): string {
                        if (auth()->user()->is_admin) {
                            // Admins go to the 'edit' page
                            return RequisitionResource::getUrl('edit', ['record' => $record]);
                        }
                        
                        // Standard users go to the 'view' page
                        return RequisitionResource::getUrl('view', ['record' => $record]);
                    }),
            ]);
    }
}