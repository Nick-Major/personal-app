<?php

namespace App\Filament\Resources\CompensationResource\Pages;

use App\Filament\Resources\CompensationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompensation extends EditRecord
{
    protected static string $resource = CompensationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
