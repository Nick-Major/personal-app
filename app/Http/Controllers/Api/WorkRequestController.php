<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkRequestResource;
use App\Models\WorkRequest;
use Illuminate\Http\Request;

class WorkRequestController extends Controller
{
    public function index()
    {
        $requests = WorkRequest::with(['initiator', 'brigadier', 'dispatcher', 'shifts'])->get();
        return WorkRequestResource::collection($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'initiator_id' => 'required|exists:users,id',
            'brigadier_id' => 'required|exists:users,id',
            'specialization' => 'required|string|max:255',
            'specialty_id' => 'nullable|exists:specialties,id',
            'work_type_id' => 'nullable|exists:work_types,id',
            'executor_type' => 'required|in:our_staff,contractor',
            'workers_count' => 'required|integer|min:1',
            'shift_duration' => 'required|integer|min:1',
            'project' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'payer_company' => 'required|string|max:255',
            'comments' => 'nullable|string',
            'status' => 'sometimes|in:draft,published,in_work,staffed,in_progress,completed,cancelled',
        ]);

        $workRequest = WorkRequest::create($validated);
        return new WorkRequestResource($workRequest->load(['initiator', 'brigadier', 'dispatcher', 'shifts']));
    }

    public function show(WorkRequest $workRequest)
    {
        return new WorkRequestResource($workRequest->load(['initiator', 'brigadier', 'dispatcher', 'shifts']));
    }

    public function update(Request $request, WorkRequest $workRequest)
    {
        $validated = $request->validate([
            'initiator_id' => 'sometimes|exists:users,id',
            'brigadier_id' => 'sometimes|exists:users,id',
            'dispatcher_id' => 'nullable|exists:users,id',
            'specialization' => 'sometimes|string|max:255',
            'specialty_id' => 'nullable|exists:specialties,id',
            'work_type_id' => 'nullable|exists:work_types,id',
            'executor_type' => 'sometimes|in:our_staff,contractor',
            'workers_count' => 'sometimes|integer|min:1',
            'shift_duration' => 'sometimes|integer|min:1',
            'project' => 'sometimes|string|max:255',
            'purpose' => 'sometimes|string|max:255',
            'payer_company' => 'sometimes|string|max:255',
            'comments' => 'nullable|string',
            'status' => 'sometimes|in:draft,published,in_work,staffed,in_progress,completed,cancelled',
            'published_at' => 'nullable|date',
            'staffed_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        $workRequest->update($validated);
        return new WorkRequestResource($workRequest->load(['initiator', 'brigadier', 'dispatcher', 'shifts']));
    }

    public function destroy(WorkRequest $workRequest)
    {
        $workRequest->delete();
        return response()->json(null, 204);
    }

    public function byStatus($status)
    {
        $requests = WorkRequest::where('status', $status)
            ->with(['initiator', 'brigadier', 'dispatcher', 'shifts'])
            ->get();
        return WorkRequestResource::collection($requests);
    }

    public function publish(WorkRequest $workRequest)
    {
        $workRequest->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return new WorkRequestResource($workRequest->load(['initiator', 'brigadier', 'dispatcher', 'shifts']));
    }

    public function myRequests()
    {
        $userId = auth()->id();
        
        $requests = WorkRequest::where('initiator_id', $userId)
            ->with(['initiator', 'brigadier', 'dispatcher', 'shifts'])
            ->latest()
            ->get();
            
        return WorkRequestResource::collection($requests);
    }
}
