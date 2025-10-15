<?php

namespace App\Filament\Resources\AddressProgramResource\Pages;

use App\Filament\Resources\AddressProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAddressProgram extends EditRecord
{
    protected static string $resource = AddressProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
