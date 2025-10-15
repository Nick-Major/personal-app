<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayerRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payer_company' => $this->payer_company,
            'priority' => $this->priority,
            'description' => $this->description,
            'is_custom' => $this->is_custom,
            'purpose' => new PurposeResource($this->whenLoaded('purpose')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'address_program' => new AddressProgramResource($this->whenLoaded('addressProgram')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
