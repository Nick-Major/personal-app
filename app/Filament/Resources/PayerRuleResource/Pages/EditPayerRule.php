<?php

namespace App\Filament\Resources\PayerRuleResource\Pages;

use App\Filament\Resources\PayerRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayerRule extends EditRecord
{
    protected static string $resource = PayerRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
