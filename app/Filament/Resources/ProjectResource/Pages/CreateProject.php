<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Редирект на список
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Проект создан';
    }

    // Опционально: русские названия для кнопок
    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Создать проект');
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action  
    {
        return parent::getCreateAnotherFormAction()
            ->label('Создать и добавить ещё');
    }
}
