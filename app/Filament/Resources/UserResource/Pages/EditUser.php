<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Пользователь сохранен';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить пользователя')
                ->before(function () {
                    // Логирование перед удалением
                    \Log::info('Удаление пользователя', [
                        'user_id' => $this->record->id,
                        'email' => $this->record->email,
                    ]);
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Хешируем пароль только если он был изменен
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Убираем пароль из данных если он не изменен
            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Логирование изменения пользователя
        \Log::info('Пользователь изменен', [
            'user_id' => $this->record->id,
            'email' => $this->record->email,
            'roles' => $this->record->roles->pluck('name')->toArray(),
        ]);
    }
}
