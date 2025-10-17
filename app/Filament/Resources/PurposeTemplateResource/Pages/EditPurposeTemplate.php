<?php

namespace App\Filament\Resources\PurposeTemplateResource\Pages;

use App\Filament\Resources\PurposeTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurposeTemplate extends EditRecord
{
    protected static string $resource = PurposeTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Шаблон сохранен';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить шаблон'),
        ];
    }
}
