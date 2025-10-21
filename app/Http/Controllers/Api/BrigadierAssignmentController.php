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
        \Log::info('=== BRIGADIER ASSIGNMENT STORE ===');
        \Log::info('Raw request data:', $request->all());

        $validated = $request->validate([
            'brigadier_id' => 'required|exists:users,id',
            'initiator_id' => 'required|exists:users,id',
            'assignment_date' => 'nullable|date',
            'assignment_dates' => 'nullable|array',
            'assignment_dates.*' => 'date',
            'dates' => 'nullable|array',
            'dates.*' => 'date',
            'comment' => 'nullable|string',
            'status' => 'sometimes|in:pending,confirmed,rejected',
        ]);

        \Log::info('Validated data with comment:', [
            'has_comment' => isset($validated['comment']),
            'comment' => $validated['comment'] ?? 'NOT SET'
        ]);

        $dates = [];
        if (!empty($validated['assignment_dates'])) {
            $dates = $validated['assignment_dates'];
        } elseif (!empty($validated['assignment_date'])) {
            $dates = [$validated['assignment_date']];
        } elseif (!empty($validated['dates'])) {
            $dates = $validated['dates'];
        } else {
            return response()->json(['error' => 'assignment_date(s) required'], 422);
        }

        // Используем существующее назначение для пары (brigadier_id, initiator_id) или создаём новое
        $assignment = BrigadierAssignment::firstOrCreate([
            'brigadier_id' => $validated['brigadier_id'],
            'initiator_id' => $validated['initiator_id'],
        ], [
            'status' => $validated['status'] ?? 'pending',
            'comment' => $validated['comment'] ?? null,
            'can_create_requests' => false, // значение по умолчанию
        ]);

        // Для существующих записей - обновляем комментарий только если он передан и не пустой
        if (!$assignment->wasRecentlyCreated && isset($validated['comment']) && !empty($validated['comment'])) {
            $assignment->update(['comment' => $validated['comment']]);
        }

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

        \Log::info('Assignment created/updated:', [
            'assignment_id' => $assignment->id,
            'was_recently_created' => $assignment->wasRecentlyCreated,
            'dates_added' => $uniqueDates->toArray()
        ]);

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
            'comment' => 'nullable|string',
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
