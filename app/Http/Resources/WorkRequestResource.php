<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_number' => $this->request_number,
            'initiator' => new UserResource($this->whenLoaded('initiator')),
            'brigadier' => new UserResource($this->whenLoaded('brigadier')),
            'dispatcher' => new UserResource($this->whenLoaded('dispatcher')),
            'specialty' => new SpecialtyResource($this->whenLoaded('specialty')),
            'work_type' => new WorkTypeResource($this->whenLoaded('workType')),
            // === НОВЫЕ ПОЛЯ ===
            'project' => new ProjectResource($this->whenLoaded('project')),
            'purpose' => new PurposeResource($this->whenLoaded('purpose')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'is_custom_payer' => $this->is_custom_payer,
            // ===
            'executor_type' => $this->executor_type,
            'workers_count' => $this->workers_count,
            'shift_duration' => $this->shift_duration,
            'work_date' => $this->work_date->format('Y-m-d'),
            'start_time' => $this->start_time?->format('H:i'),
            'payer_company' => $this->payer_company,
            'comments' => $this->comments,
            'status' => $this->status,
            'published_at' => $this->published_at?->format('Y-m-d H:i'),
            'staffed_at' => $this->staffed_at?->format('Y-m-d H:i'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i'),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i'),
        ];
    }
}
