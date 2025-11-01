<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Assignment::with(['user', 'workRequest', 'plannedAddress'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'assignment_type' => 'required|in:brigadier_schedule,work_request,mass_personnel',
            'user_id' => 'required|exists:users,id',
            'work_request_id' => 'nullable|exists:work_requests,id',
            'role_in_shift' => 'required|in:executor,brigadier',
            'planned_date' => 'required|date',
            'planned_start_time' => 'required|date_format:H:i',
            'planned_duration_hours' => 'required|numeric|min:1|max:24',
            'planned_address_id' => 'nullable|exists:addresses,id',
            'assignment_comment' => 'nullable|string|max:1000',
        ]);

        // Для назначений на заявки проверяем work_request_id
        if ($validated['assignment_type'] === 'work_request' && empty($validated['work_request_id'])) {
            return response()->json(['error' => 'Для назначения на заявку требуется work_request_id'], 400);
        }

        // Для плановых назначений бригадиров work_request_id должен быть null
        if ($validated['assignment_type'] === 'brigadier_schedule') {
            $validated['work_request_id'] = null;
        }

        $assignment = Assignment::create($validated);

        return response()->json($assignment->load(['user', 'workRequest', 'plannedAddress']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Assignment $assignment)
    {
        return $assignment->load(['user', 'workRequest', 'plannedAddress', 'shift']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'planned_date' => 'sometimes|date',
            'planned_start_time' => 'sometimes|date_format:H:i',
            'planned_duration_hours' => 'sometimes|numeric|min:1|max:24',
            'planned_address_id' => 'nullable|exists:addresses,id',
            'assignment_comment' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:pending,confirmed,rejected,completed',
        ]);

        $assignment->update($validated);

        return response()->json($assignment->load(['user', 'workRequest', 'plannedAddress']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return response()->json(['message' => 'Назначение удалено']);
    }

    /**
     * Подтвердить назначение
     */
    public function confirm(Assignment $assignment)
    {
        if ($assignment->status !== 'pending') {
            return response()->json(['error' => 'Назначение уже обработано'], 400);
        }

        $assignment->confirm();

        return response()->json([
            'message' => 'Назначение подтверждено',
            'assignment' => $assignment->load(['user', 'workRequest', 'plannedAddress', 'shift'])
        ]);
    }

    /**
     * Отклонить назначение
     */
    public function reject(Assignment $assignment, Request $request)
    {
        if ($assignment->status !== 'pending') {
            return response()->json(['error' => 'Назначение уже обработано'], 400);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $assignment->reject($validated['rejection_reason']);

        return response()->json([
            'message' => 'Назначение отклонено',
            'assignment' => $assignment->load(['user', 'workRequest', 'plannedAddress'])
        ]);
    }

    /**
     * Мои назначения (для исполнителей/бригадиров)
     */
    public function myAssignments(Request $request)
    {
        $user = $request->user();

        $assignments = Assignment::with(['workRequest', 'plannedAddress'])
            ->where('user_id', $user->id)
            ->orderBy('planned_date', 'desc')
            ->get();

        return response()->json($assignments);
    }
}
