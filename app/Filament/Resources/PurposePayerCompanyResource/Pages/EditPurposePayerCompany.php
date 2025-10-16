<?php

namespace App\Filament\Resources\PurposePayerCompanyResource\Pages;

use App\Filament\Resources\PurposePayerCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurposePayerCompany extends EditRecord
{
    protected static string $resource = PurposePayerCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
