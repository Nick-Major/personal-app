<?php

namespace App\Filament\Resources\ShiftSettingResource\Pages;

use App\Filament\Resources\ShiftSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageShiftSettings extends ManageRecords
{
    protected static string $resource = ShiftSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
