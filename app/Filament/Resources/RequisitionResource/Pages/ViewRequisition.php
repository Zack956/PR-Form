<?php

namespace App\Filament\Resources\RequisitionResource\Pages;

use App\Filament\Resources\RequisitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRequisition extends ViewRecord
{
    protected static string $resource = RequisitionResource::class;

    // This adds an "Edit" button to the top right of the view page for admins
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->visible(auth()->user()->is_admin),
        ];
    }
}