<?php

namespace App\Filament\Resources\AddressTemplateResource\Pages;

use App\Filament\Resources\AddressTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAddressTemplate extends CreateRecord
{
    protected static string $resource = AddressTemplateResource::class;

    protected static ?string $title = 'Создать шаблон адреса';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Шаблон адреса создан';
    }
}
