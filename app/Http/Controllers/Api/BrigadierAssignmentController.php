<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrigadierAssignmentResource;
use App\Models\BrigadierAssignment;
use Illuminate\Http\Request;

class BrigadierAssignmentController extends Controller
{
    public function index()
    {
        $assignments = BrigadierAssignment::with(['brigadier', 'initiator'])->get();
        return BrigadierAssignmentResource::collection($assignments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brigadier_id' => 'required|exists:users,id',
            'initiator_id' => 'required|exists:users,id',
            'assignment_date' => 'required|date',
            'status' => 'sometimes|in:pending,confirmed,rejected',
        ]);

        $assignment = BrigadierAssignment::create($validated);
        return new BrigadierAssignmentResource($assignment->load(['brigadier', 'initiator']));
    }

    public function show(BrigadierAssignment $brigadierAssignment)
    {
        return new BrigadierAssignmentResource($brigadierAssignment->load(['brigadier', 'initiator']));
    }

    public function update(Request $request, BrigadierAssignment $brigadierAssignment)
    {
        $validated = $request->validate([
            'brigadier_id' => 'sometimes|exists:users,id',
            'initiator_id' => 'sometimes|exists:users,id',
            'assignment_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,confirmed,rejected',
            'confirmed_at' => 'nullable|date',
            'rejected_at' => 'nullable|date',
            'rejection_reason' => 'nullable|string',
        ]);

        // Автоматически устанавливаем временные метки при изменении статуса
        if (isset($validated['status'])) {
            if ($validated['status'] === 'confirmed') {
                $validated['confirmed_at'] = now();
                $validated['rejected_at'] = null;
            } elseif ($validated['status'] === 'rejected') {
                $validated['rejected_at'] = now();
                $validated['confirmed_at'] = null;
            }
        }

        $brigadierAssignment->update($validated);
        return new BrigadierAssignmentResource($brigadierAssignment->load(['brigadier', 'initiator']));
    }

    public function destroy(BrigadierAssignment $brigadierAssignment)
    {
        $brigadierAssignment->delete();
        return response()->json(null, 204);
    }

    public function byBrigadier($brigadierId)
    {
        $assignments = BrigadierAssignment::where('brigadier_id', $brigadierId)
            ->with(['brigadier', 'initiator'])
            ->get();
        return BrigadierAssignmentResource::collection($assignments);
    }

    public function confirm(BrigadierAssignment $brigadierAssignment)
    {
        $brigadierAssignment->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'rejected_at' => null,
        ]);

        return new BrigadierAssignmentResource($brigadierAssignment->load(['brigadier', 'initiator']));
    }

    public function reject(BrigadierAssignment $brigadierAssignment, Request $request)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $brigadierAssignment->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'confirmed_at' => null,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return new BrigadierAssignmentResource($brigadierAssignment->load(['brigadier', 'initiator']));
    }
}
