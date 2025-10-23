<?php

namespace App\Filament\Resources\ContractorRateResource\Pages;

use App\Filament\Resources\ContractorRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContractorRates extends ListRecords
{
    protected static string $resource = ContractorRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
