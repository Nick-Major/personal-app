<?php

namespace App\Filament\Resources\BrigadierAssignmentDateResource\Pages;

use App\Filament\Resources\BrigadierAssignmentDateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrigadierAssignmentDate extends EditRecord
{
    protected static string $resource = BrigadierAssignmentDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
