<?php

namespace App\Filament\Resources\AddressProgramResource\Pages;

use App\Filament\Resources\AddressProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAddressPrograms extends ListRecords
{
    protected static string $resource = AddressProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
