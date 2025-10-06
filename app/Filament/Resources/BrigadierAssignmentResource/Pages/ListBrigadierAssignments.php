<?php

namespace App\Filament\Resources\BrigadierAssignmentResource\Pages;

use App\Filament\Resources\BrigadierAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBrigadierAssignments extends ListRecords
{
    protected static string $resource = BrigadierAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
