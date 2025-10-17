<?php

namespace App\Filament\Resources\PurposeTemplateResource\Pages;

use App\Filament\Resources\PurposeTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurposeTemplates extends ListRecords
{
    protected static string $resource = PurposeTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
