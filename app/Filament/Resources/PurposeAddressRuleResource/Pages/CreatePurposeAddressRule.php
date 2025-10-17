<?php

namespace App\Filament\Resources\PurposeAddressRuleResource\Pages;

use App\Filament\Resources\PurposeAddressRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurposeAddressRule extends CreateRecord
{
    protected static string $resource = PurposeAddressRuleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Правило создано';
    }
}
