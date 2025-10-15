<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurposeAddressRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purpose_id' => $this->purpose_id,
            'address_id' => $this->address_id,
            'payer_company' => $this->payer_company,
            'priority' => $this->priority,
            'purpose' => new PurposeResource($this->whenLoaded('purpose')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
