<?php

namespace App\Filament\Resources\PurposeResource\Pages;

use App\Filament\Resources\PurposeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurpose extends EditRecord
{
    protected static string $resource = PurposeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Назначение сохранено';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить назначение'),
        ];
    }
}
