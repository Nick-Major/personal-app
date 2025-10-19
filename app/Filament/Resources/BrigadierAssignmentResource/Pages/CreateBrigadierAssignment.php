<?php

namespace App\Filament\Resources\BrigadierAssignmentResource\Pages;

use App\Filament\Resources\BrigadierAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBrigadierAssignment extends CreateRecord
{
    protected static string $resource = BrigadierAssignmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Назначение бригадира создано';
    }
}
