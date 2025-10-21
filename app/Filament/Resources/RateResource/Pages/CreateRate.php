<?php

namespace App\Filament\Resources\RateResource\Pages;

use App\Filament\Resources\RateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\Rule;

class CreateRate extends CreateRecord
{
    protected static string $resource = RateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Ставка создана';
    }

    // Добавляем кастомную валидацию
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Проверяем уникальность комбинации полей
        $exists = \App\Models\Rate::where('user_id', $data['user_id'])
            ->where('specialty_id', $data['specialty_id'])
            ->where('work_type_id', $data['work_type_id'])
            ->exists();

        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => 'Для этого пользователя, специальности и вида работ уже существует ставка',
            ]);
        }

        return $data;
    }
}
