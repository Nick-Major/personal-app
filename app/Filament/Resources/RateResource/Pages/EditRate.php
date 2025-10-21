<?php

namespace App\Filament\Resources\RateResource\Pages;

use App\Filament\Resources\RateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRate extends EditRecord
{
    protected static string $resource = RateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Ставка сохранена';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить ставку'),
        ];
    }

    // Добавляем кастомную валидацию для редактирования
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $exists = \App\Models\Rate::where('user_id', $data['user_id'])
            ->where('specialty_id', $data['specialty_id'])
            ->where('work_type_id', $data['work_type_id'])
            ->where('id', '!=', $this->record->id)
            ->exists();

        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => 'Для этого пользователя, специальности и вида работ уже существует ставка',
            ]);
        }

        return $data;
    }
}
