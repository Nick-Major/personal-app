<?php

namespace App\Filament\Resources\ShiftExpenseResource\Pages;

use App\Filament\Resources\ShiftExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShiftExpense extends EditRecord
{
    protected static string $resource = ShiftExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
