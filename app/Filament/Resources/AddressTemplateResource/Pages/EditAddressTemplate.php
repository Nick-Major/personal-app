<?php

namespace App\Filament\Resources\AddressTemplateResource\Pages;

use App\Filament\Resources\AddressTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAddressTemplate extends EditRecord
{
    protected static string $resource = AddressTemplateResource::class;

    protected static ?string $title = 'Редактировать шаблон адреса';

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Шаблон адреса обновлен';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить шаблон'),
        ];
    }
}
