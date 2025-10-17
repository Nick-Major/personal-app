<?php

namespace App\Filament\Resources\PurposePayerCompanyResource\Pages;

use App\Filament\Resources\PurposePayerCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurposePayerCompany extends CreateRecord
{
    protected static string $resource = PurposePayerCompanyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Вариант оплаты создан';
    }
}
