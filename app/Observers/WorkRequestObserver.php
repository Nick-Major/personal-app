<?php
// app/Observers/WorkRequestObserver.php

namespace App\Observers;

use App\Models\WorkRequest;
use App\Models\WorkRequestStatus;

class WorkRequestObserver
{
    public function updated(WorkRequest $workRequest)
    {
        // Автоматически логируем изменение статуса
        if ($workRequest->isDirty('status')) {
            WorkRequestStatus::create([
                'work_request_id' => $workRequest->id,
                'status' => $workRequest->status,
                'changed_by_id' => auth()->id(),
                'changed_at' => now(),
                'notes' => 'Status changed via system'
            ]);
        }
    }

    public function created(WorkRequest $workRequest)
    {
        // Логируем создание заявки
        WorkRequestStatus::create([
            'work_request_id' => $workRequest->id,
            'status' => $workRequest->status,
            'changed_by_id' => $workRequest->initiator_id,
            'changed_at' => now(),
            'notes' => 'Work request created'
        ]);
    }
}
