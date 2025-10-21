<?php

namespace App\Filament\Resources\BrigadierAssignmentDateResource\Pages;

use App\Filament\Resources\BrigadierAssignmentDateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBrigadierAssignmentDates extends ListRecords
{
    protected static string $resource = BrigadierAssignmentDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
