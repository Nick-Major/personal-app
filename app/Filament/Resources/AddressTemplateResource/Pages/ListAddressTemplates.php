<?php

namespace App\Filament\Resources\AddressTemplateResource\Pages;

use App\Filament\Resources\AddressTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAddressTemplates extends ListRecords
{
    protected static string $resource = AddressTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
