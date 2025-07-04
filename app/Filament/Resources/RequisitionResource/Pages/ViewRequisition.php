<?php

namespace App\Filament\Resources\RequisitionResource\Pages;

use App\Filament\Resources\RequisitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRequisition extends ViewRecord
{
    protected static string $resource = RequisitionResource::class;

    // This is the correct method to define header actions for a page
    protected function getHeaderActions(): array
    {
        return [
            // The Print button we moved from the infolist
            Actions\Action::make('print')
                ->label('Print / Save as PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn ($record) => route('requisitions.print', $record), shouldOpenInNewTab: true),

            // The default Edit button, visible only to admins
            Actions\EditAction::make()->visible(auth()->user()->is_admin),
        ];
    }
}
