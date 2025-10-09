<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrigadierAssignmentResource;
use App\Models\BrigadierAssignment;
use App\Models\BrigadierAssignmentDate;
use Illuminate\Http\Request;

class BrigadierAssignmentController extends Controller
{
    public function index()
    {
        $assignments = BrigadierAssignment::with(['brigadier', 'initiator', 'assignmentDates'])->get();
        return BrigadierAssignmentResource::collection($assignments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brigadier_id' => 'required|exists:users,id',
            'initiator_id' => 'required|exists:users,id',
            // поддерживаем либо одну дату, либо массив дат
            'assignment_date' => 'nullable|date',
            'assignment_dates' => 'nullable|array',
            'assignment_dates.*' => 'date',
            'status' => 'sometimes|in:pending,confirmed,rejected',
        ]);

        $dates = [];
        if (!empty($validated['assignment_dates'])) {
            $dates = $validated['assignment_dates'];
        } elseif (!empty($validated['assignment_date'])) {
            $dates = [$validated['assignment_date']];
        } else {
            return response()->json(['error' => 'assignment_date(s) required'], 422);
        }

        // Используем существующее назначение для пары (brigadier_id, initiator_id) или создаём новое,
        // чтобы не нарушать уникальный индекс на таблице назначений
        $assignment = BrigadierAssignment::firstOrCreate([
            'brigadier_id' => $validated['brigadier_id'],
            'initiator_id' => $validated['initiator_id'],
        ]);

        $status = $validated['status'] ?? 'pending';
        $uniqueDates = collect($dates)
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->values();

        foreach ($uniqueDates as $date) {
            BrigadierAssignmentDate::firstOrCreate(
                [
                    'assignment_id' => $assignment->id,
                    'assignment_date' => $date,
                ],
                [
                    'status' => $status,
                ]
            );
        }

        return new BrigadierAssignmentResource($assignment->load(['brigadier', 'initiator', 'assignmentDates']));
    }

    public function show(BrigadierAssignment $brigadierAssignment)
    {
        return new BrigadierAssignmentResource($brigadierAssignment->load(['brigadier', 'initiator', 'assignmentDates']));
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
        return new BrigadierAssignmentResource($brigadierAssignment->load(['brigadier', 'initiator', 'assignmentDates']));
    }

    public function destroy(BrigadierAssignment $brigadierAssignment)
    {
        $brigadierAssignment->delete();
        return response()->json(null, 204);
    }

    public function byBrigadier($brigadierId)
    {
        $assignments = BrigadierAssignment::where('brigadier_id', $brigadierId)
            ->with(['brigadier', 'initiator', 'assignmentDates'])
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

        return new BrigadierAssignmentResource($brigadierAssignment->load(['brigadier', 'initiator', 'assignmentDates']));
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

        return new BrigadierAssignmentResource($brigadierAssignment->load(['brigadier', 'initiator', 'assignmentDates']));
    }

    // Добавьте этот метод в существующий BrigadierAssignmentController.php
    public function myAssignments()
    {
        $userId = auth()->id();
        
        $assignments = BrigadierAssignment::where('initiator_id', $userId)
            ->with(['brigadier', 'initiator', 'assignmentDates'])
            ->latest()
            ->get();
            
        return BrigadierAssignmentResource::collection($assignments);
    }
}
