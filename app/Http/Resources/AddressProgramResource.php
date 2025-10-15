<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'payer_rules' => PayerRuleResource::collection($this->whenLoaded('payerRules')),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
