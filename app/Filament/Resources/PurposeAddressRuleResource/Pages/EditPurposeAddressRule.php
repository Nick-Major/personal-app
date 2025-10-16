<?php

namespace App\Filament\Resources\PurposeAddressRuleResource\Pages;

use App\Filament\Resources\PurposeAddressRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurposeAddressRule extends EditRecord
{
    protected static string $resource = PurposeAddressRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
