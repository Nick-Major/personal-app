<?php

namespace App\Filament\Resources\SpecialtyResource\Pages;

use App\Filament\Resources\SpecialtyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecialty extends EditRecord
{
    protected static string $resource = SpecialtyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Специальность сохранена';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить специальность'),
        ];
    }
}
