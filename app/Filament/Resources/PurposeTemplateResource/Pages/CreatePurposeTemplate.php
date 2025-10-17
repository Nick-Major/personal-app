<?php

namespace App\Filament\Resources\PurposeTemplateResource\Pages;

use App\Filament\Resources\PurposeTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurposeTemplate extends CreateRecord
{
    protected static string $resource = PurposeTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Шаблон создан';
    }
}
