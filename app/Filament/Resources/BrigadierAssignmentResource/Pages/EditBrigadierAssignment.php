<?php

namespace App\Filament\Resources\BrigadierAssignmentResource\Pages;

use App\Filament\Resources\BrigadierAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrigadierAssignment extends EditRecord
{
    protected static string $resource = BrigadierAssignmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Назначение бригадира сохранено';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить назначение'),
        ];
    }
}
