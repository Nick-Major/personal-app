<?php

namespace App\Filament\Resources\CompensationResource\Pages;

use App\Filament\Resources\CompensationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompensation extends ListRecords
{
    protected static string $resource = CompensationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
