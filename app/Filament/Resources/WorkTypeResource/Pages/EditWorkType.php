<?php

namespace App\Filament\Resources\WorkTypeResource\Pages;

use App\Filament\Resources\WorkTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkType extends EditRecord
{
    protected static string $resource = WorkTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Тип работ сохранен';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить тип работ'),
        ];
    }
}
