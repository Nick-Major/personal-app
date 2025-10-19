<?php

namespace App\Filament\Resources\InitiatorGrantResource\Pages;

use App\Filament\Resources\InitiatorGrantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInitiatorGrant extends EditRecord
{
    protected static string $resource = InitiatorGrantResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Право инициатора сохранено';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить право'),
        ];
    }
}
