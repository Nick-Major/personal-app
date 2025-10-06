<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrigadierAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'brigadier' => new UserResource($this->whenLoaded('brigadier')),
            'initiator' => new UserResource($this->whenLoaded('initiator')),
            'assignment_date' => $this->assignment_date,
            'status' => $this->status,
            'confirmed_at' => $this->confirmed_at,
            'rejected_at' => $this->rejected_at,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
