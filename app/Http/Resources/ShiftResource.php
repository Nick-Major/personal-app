<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_request' => new WorkRequestResource($this->whenLoaded('workRequest')),
            'user' => new UserResource($this->whenLoaded('user')),
            'contractor' => new ContractorResource($this->whenLoaded('contractor')),
            'contractor_worker_name' => $this->contractor_worker_name,
            'work_date' => $this->work_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'shift_started_at' => $this->shift_started_at,
            'shift_ended_at' => $this->shift_ended_at,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
