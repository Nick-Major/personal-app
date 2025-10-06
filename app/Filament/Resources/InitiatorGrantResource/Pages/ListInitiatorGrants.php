<?php

namespace App\Filament\Resources\InitiatorGrantResource\Pages;

use App\Filament\Resources\InitiatorGrantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInitiatorGrants extends ListRecords
{
    protected static string $resource = InitiatorGrantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
