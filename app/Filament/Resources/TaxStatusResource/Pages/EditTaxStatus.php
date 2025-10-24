<?php

namespace App\Filament\Resources\TaxStatusResource\Pages;

use App\Filament\Resources\TaxStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxStatus extends EditRecord
{
    protected static string $resource = TaxStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
