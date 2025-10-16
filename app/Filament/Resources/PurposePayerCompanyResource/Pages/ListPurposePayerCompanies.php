<?php

namespace App\Filament\Resources\PurposePayerCompanyResource\Pages;

use App\Filament\Resources\PurposePayerCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurposePayerCompanies extends ListRecords
{
    protected static string $resource = PurposePayerCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
