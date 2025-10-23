<?php

namespace App\Filament\Resources\ShiftExpenseResource\Pages;

use App\Filament\Resources\ShiftExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageShiftExpenses extends ManageRecords
{
    protected static string $resource = ShiftExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
