<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'default_payer_company' => $this->default_payer_company,
            'status' => $this->status,
            'purposes' => PurposeResource::collection($this->whenLoaded('purposes')),
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'purposes_count' => $this->whenCounted('purposes'),
            'addresses_count' => $this->whenCounted('addresses'),
            'work_requests_count' => $this->whenCounted('workRequests'),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
