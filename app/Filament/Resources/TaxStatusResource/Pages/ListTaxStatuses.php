<?php

namespace App\Filament\Resources\TaxStatusResource\Pages;

use App\Filament\Resources\TaxStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxStatuses extends ListRecords
{
    protected static string $resource = TaxStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
