<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurposePayerCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purpose_id' => $this->purpose_id,
            'payer_company' => $this->payer_company,
            'description' => $this->description,
            'order' => $this->order,
            'purpose' => new PurposeResource($this->whenLoaded('purpose')),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
