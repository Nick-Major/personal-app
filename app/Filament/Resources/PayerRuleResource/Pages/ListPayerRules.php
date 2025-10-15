<?php

namespace App\Filament\Resources\PayerRuleResource\Pages;

use App\Filament\Resources\PayerRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayerRules extends ListRecords
{
    protected static string $resource = PayerRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
