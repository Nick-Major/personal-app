<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Пользователь создан';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Автоматически хешируем пароль если он указан
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Генерируем случайный пароль если не указан
            $data['password'] = Hash::make(bin2hex(random_bytes(8)));
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Логирование создания пользователя
        \Log::info('Создан новый пользователь', [
            'user_id' => $this->record->id,
            'email' => $this->record->email,
            'roles' => $this->record->roles->pluck('name')->toArray(),
        ]);
    }
}
