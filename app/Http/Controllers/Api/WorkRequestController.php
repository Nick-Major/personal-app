<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkRequestResource;
use App\Models\WorkRequest;
use App\Models\PayerRule;
use Illuminate\Http\Request;

class WorkRequestController extends Controller
{
    public function index()
    {
        $requests = WorkRequest::with([
            'initiator', 
            'brigadier', 
            'dispatcher', 
            'specialty', 
            'workType',
            'project',    // ДОБАВЛЕНО
            'purpose',    // ДОБАВЛЕНО
            'address'     // ДОБАВЛЕНО
        ])->get();
        
        return WorkRequestResource::collection($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'initiator_id' => 'required|exists:users,id',
            'brigadier_id' => 'required|exists:users,id',
            'specialty_id' => 'required|exists:specialties,id',
            'work_type_id' => 'required|exists:work_types,id',
            'executor_type' => 'required|in:our_staff,contractor',
            'workers_count' => 'required|integer|min:1',
            'shift_duration' => 'required|integer|min:1',
            'work_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            // === ИЗМЕНЕНИЯ ===
            'project_id' => 'required|exists:projects,id',      // было 'project'
            'purpose_id' => 'required|exists:purposes,id',      // было 'purpose'
            'address_id' => 'required|exists:addresses,id',     // ДОБАВЛЕНО
            'is_custom_payer' => 'boolean',                     // ДОБАВЛЕНО
            'payer_company' => 'required_if:is_custom_payer,true|string|max:255', // Условная валидация
            'comments' => 'nullable|string',
            'status' => 'sometimes|in:draft,published,in_work,staffed,in_progress,completed,cancelled',
        ]);

        // Автоматически определяем плательщика, если не индивидуальный
        if (empty($validated['is_custom_payer'])) {
            $workRequestTemp = new WorkRequest($validated);
            $validated['payer_company'] = $workRequestTemp->determinePayer();
        }

        $workRequest = WorkRequest::create($validated);
        
        return new WorkRequestResource($workRequest->load([
            'initiator', 'brigadier', 'dispatcher', 'specialty', 'workType', 'project', 'purpose', 'address'
        ]));
    }

    public function show(WorkRequest $workRequest)
    {
        return new WorkRequestResource($workRequest->load([
            'initiator', 'brigadier', 'dispatcher', 'specialty', 'workType', 'project', 'purpose', 'address'
        ]));
    }

    public function update(Request $request, WorkRequest $workRequest)
    {
        $validated = $request->validate([
            'initiator_id' => 'sometimes|exists:users,id',
            'brigadier_id' => 'sometimes|exists:users,id',
            'dispatcher_id' => 'nullable|exists:users,id',
            'specialty_id' => 'sometimes|exists:specialties,id',
            'work_type_id' => 'sometimes|exists:work_types,id',
            'executor_type' => 'sometimes|in:our_staff,contractor',
            'workers_count' => 'sometimes|integer|min:1',
            'shift_duration' => 'sometimes|integer|min:1',
            'work_date' => 'sometimes|date',
            // === ИЗМЕНЕНИЯ ===
            'project_id' => 'sometimes|exists:projects,id',
            'purpose_id' => 'sometimes|exists:purposes,id',
            'address_id' => 'sometimes|exists:addresses,id',
            'is_custom_payer' => 'boolean',
            'payer_company' => 'required_if:is_custom_payer,true|string|max:255',
            'comments' => 'nullable|string',
            'status' => 'sometimes|in:draft,published,in_work,staffed,in_progress,completed,cancelled',
            'published_at' => 'nullable|date',
            'staffed_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        // Переопределяем плательщика при изменении связей
        if (isset($validated['project_id']) || isset($validated['purpose_id']) || isset($validated['address_id'])) {
            if (empty($validated['is_custom_payer'])) {
                $updatedData = array_merge($workRequest->toArray(), $validated);
                $workRequestTemp = new WorkRequest($updatedData);
                $validated['payer_company'] = $workRequestTemp->determinePayer();
            }
        }

        $workRequest->update($validated);
        
        return new WorkRequestResource($workRequest->load([
            'initiator', 'brigadier', 'dispatcher', 'specialty', 'workType', 'project', 'purpose', 'address'
        ]));
    }

    public function destroy(WorkRequest $workRequest)
    {
        $workRequest->delete();
        return response()->json(null, 204);
    }

    public function byStatus($status)
    {
        $requests = WorkRequest::where('status', $status)
            ->with([
                'initiator', 'brigadier', 'dispatcher', 'specialty', 'workType', 'project', 'purpose', 'address'
            ])
            ->get();
        return WorkRequestResource::collection($requests);
    }

    public function publish(WorkRequest $workRequest)
    {
        $workRequest->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return new WorkRequestResource($workRequest->load([
            'initiator', 'brigadier', 'dispatcher', 'specialty', 'workType', 'project', 'purpose', 'address'
        ]));
    }

    public function myRequests()
    {
        $userId = auth()->id();

        $requests = WorkRequest::where('initiator_id', $userId)
            ->with([
                'initiator', 'brigadier', 'dispatcher', 'specialty', 'workType', 'project', 'purpose', 'address'
            ])
            ->latest()
            ->get();

        return WorkRequestResource::collection($requests);
    }

    // === НОВЫЙ МЕТОД: Переопределить плательщика ===
    public function redeterminePayer(WorkRequest $workRequest)
    {
        if ($workRequest->is_custom_payer) {
            return response()->json([
                'message' => 'Плательщик определяется индивидуально для этой заявки'
            ], 422);
        }

        $payerCompany = $workRequest->determinePayer();
        $workRequest->update(['payer_company' => $payerCompany]);

        return response()->json([
            'payer_company' => $payerCompany,
            'message' => 'Плательщик переопределен'
        ]);
    }
}
