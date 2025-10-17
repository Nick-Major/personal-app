<?php

namespace App\Filament\Resources\PurposePayerCompanyResource\Pages;

use App\Filament\Resources\PurposePayerCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurposePayerCompany extends EditRecord
{
    protected static string $resource = PurposePayerCompanyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Вариант оплаты сохранен';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить вариант оплаты'),
        ];
    }
}
