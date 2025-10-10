<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with(['workRequest', 'user', 'contractor', 'specialty', 'workType'])->get();
        return ShiftResource::collection($shifts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:work_requests,id',
            'user_id' => 'nullable|exists:users,id',
            'contractor_id' => 'nullable|exists:contractors,id',
            'contractor_worker_name' => 'nullable|string|max:255',
            'work_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'status' => 'sometimes|in:scheduled,started,completed,cancelled,no_show',
            'notes' => 'nullable|string',
            'specialty_id' => 'nullable|exists:specialties,id',
            'work_type_id' => 'nullable|exists:work_types,id',
        ]);

        // Проверяем, что указан либо user_id, либо contractor_id
        if (empty($validated['user_id']) && empty($validated['contractor_id'])) {
            return response()->json([
                'message' => 'Необходимо указать либо исполнителя, либо подрядчика'
            ], 422);
        }

        $shift = Shift::create($validated);
        return new ShiftResource($shift->load(['workRequest', 'user', 'contractor', 'specialty', 'workType']));
    }

    public function show(Shift $shift)
    {
        return new ShiftResource($shift->load(['workRequest', 'user', 'contractor']));
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'request_id' => 'sometimes|exists:work_requests,id',
            'user_id' => 'nullable|exists:users,id',
            'contractor_id' => 'nullable|exists:contractors,id',
            'contractor_worker_name' => 'nullable|string|max:255',
            'work_date' => 'sometimes|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'status' => 'sometimes|in:scheduled,started,completed,cancelled,no_show',
            'shift_started_at' => 'nullable|date',
            'shift_ended_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'specialty_id' => 'nullable|exists:specialties,id',
            'work_type_id' => 'nullable|exists:work_types,id',
        ]);

        // Автоматически устанавливаем временные метки при изменении статуса
        if (isset($validated['status'])) {
            if ($validated['status'] === 'started' && !$shift->shift_started_at) {
                $validated['shift_started_at'] = now();
            } elseif ($validated['status'] === 'completed' && !$shift->shift_ended_at) {
                $validated['shift_ended_at'] = now();
            }
        }

        $shift->update($validated);
        return new ShiftResource($shift->load(['workRequest', 'user', 'contractor', 'specialty', 'workType']));
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return response()->json(null, 204);
    }

    public function byWorkRequest($workRequestId)
    {
        $shifts = Shift::where('request_id', $workRequestId)
            ->with(['workRequest', 'user', 'contractor'])
            ->get();
        return ShiftResource::collection($shifts);
    }

    public function start(Shift $shift)
    {
        $shift->update([
            'status' => 'started',
            'shift_started_at' => now(),
        ]);

        return new ShiftResource($shift->load(['workRequest', 'user', 'contractor', 'specialty', 'workType']));
    }

    public function complete(Shift $shift)
    {
        $shift->update([
            'status' => 'completed',
            'shift_ended_at' => now(),
        ]);

        return new ShiftResource($shift->load(['workRequest', 'user', 'contractor', 'specialty', 'workType']));
    }
}
