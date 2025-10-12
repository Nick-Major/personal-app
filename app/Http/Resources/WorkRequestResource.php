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
            'specialty' => new SpecialtyResource($this->whenLoaded('specialty')), // ДОБАВЬТЕ ЭТУ СТРОКУ
            'work_type' => new WorkTypeResource($this->whenLoaded('workType')), // ДОБАВЬТЕ ЭТУ СТРОКУ
            'specialization' => $this->specialization,
            'executor_type' => $this->executor_type,
            'workers_count' => $this->workers_count,
            'shift_duration' => $this->shift_duration,
            'work_date' => $this->work_date, // ДОБАВЬТЕ ЭТУ СТРОКУ - ОСНОВНАЯ ПРОБЛЕМА!
            'start_time' => $this->start_time, // добавьте это
            'project' => $this->project,
            'purpose' => $this->purpose,
            'payer_company' => $this->payer_company,
            'comments' => $this->comments,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'staffed_at' => $this->staffed_at,
            'completed_at' => $this->completed_at,
            'shifts' => ShiftResource::collection($this->whenLoaded('shifts')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
