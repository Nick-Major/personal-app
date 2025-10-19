<?php

namespace App\Filament\Resources\InitiatorGrantResource\Pages;

use App\Filament\Resources\InitiatorGrantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInitiatorGrant extends CreateRecord
{
    protected static string $resource = InitiatorGrantResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Право инициатора создано';
    }
}
