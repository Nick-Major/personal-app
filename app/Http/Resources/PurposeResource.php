<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurposeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'description' => $this->description,
            'has_custom_payer_selection' => $this->has_custom_payer_selection,
            'is_active' => $this->is_active,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'payer_companies' => PurposePayerCompanyResource::collection($this->whenLoaded('payerCompanies')),
            'address_rules' => PurposeAddressRuleResource::collection($this->whenLoaded('addressRules')),
            'payer_companies_count' => $this->whenCounted('payerCompanies'),
            'address_rules_count' => $this->whenCounted('addressRules'),
            'work_requests_count' => $this->whenCounted('workRequests'),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
