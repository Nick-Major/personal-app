<?php

namespace App\Filament\Resources\ShiftSettingResource\Pages;

use App\Filament\Resources\ShiftSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShiftSetting extends EditRecord
{
    protected static string $resource = ShiftSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
