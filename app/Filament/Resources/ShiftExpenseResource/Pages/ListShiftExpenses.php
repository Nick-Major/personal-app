<?php

namespace App\Filament\Resources\ShiftExpenseResource\Pages;

use App\Filament\Resources\ShiftExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShiftExpenses extends ListRecords
{
    protected static string $resource = ShiftExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
