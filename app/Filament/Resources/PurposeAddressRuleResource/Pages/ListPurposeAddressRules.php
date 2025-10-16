<?php

namespace App\Filament\Resources\PurposeAddressRuleResource\Pages;

use App\Filament\Resources\PurposeAddressRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurposeAddressRules extends ListRecords
{
    protected static string $resource = PurposeAddressRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
