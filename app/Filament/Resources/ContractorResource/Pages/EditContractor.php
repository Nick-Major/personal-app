<?php

namespace App\Filament\Resources\ContractorResource\Pages;

use App\Filament\Resources\ContractorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractor extends EditRecord
{
    protected static string $resource = ContractorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Подрядчик сохранен';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить подрядчика'),
        ];
    }
}
