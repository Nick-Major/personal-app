<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\VisitedLocation;
use App\Models\ShiftPhoto;
use App\Models\BrigadierAssignment;
use App\Models\WorkRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExecutorController extends Controller
{
    /**
     * Получить все смены пользователя (история)
     */
    public function myShifts(Request $request)
    {
        $user = $request->user();

        $shifts = Shift::with([
                'workRequest',
                'specialty',
                'workType',
                'visitedLocations',
                'photos'
            ])
            ->where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->paginate(10);

        return response()->json($shifts);
    }

    /**
     * Получить активные смены (текущие и сегодняшние)
     */
    public function activeShifts(Request $request)
    {
        $user = $request->user();

        $shifts = Shift::with(['workRequest', 'specialty', 'workType'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['planned', 'active'])
            ->whereDate('work_date', '>=', today())
            ->orderBy('work_date')
            ->get();

        return response()->json($shifts);
    }

    /**
     * Начать смену
     */
    public function startShift(Request $request, Shift $shift)
    {
        if ($shift->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($shift->status === 'active') {
            return response()->json(['error' => 'Shift already started'], 400);
        }

        $shift->update([
            'status' => 'active',
            'shift_started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Shift started successfully',
            'shift' => $shift->fresh()->load('workRequest')
        ]);
    }

    /**
     * Завершить смену
     */
    public function endShift(Request $request, Shift $shift)
    {
        if ($shift->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($shift->status === 'completed') {
            return response()->json(['error' => 'Shift already completed'], 400);
        }

        $shift->update([
            'status' => 'completed',
            'shift_ended_at' => now(),
        ]);

        return response()->json([
            'message' => 'Shift completed successfully',
            'shift' => $shift->fresh()->load('workRequest')
        ]);
    }

    /**
     * Добавить посещенную локацию
     */
    public function addLocation(Request $request, Shift $shift)
    {
        if ($shift->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $location = $shift->visitedLocations()->create($validated);

        return response()->json([
            'message' => 'Location added successfully',
            'location' => $location->load('photos')
        ]);
    }

    /**
     * Обновить локацию (завершить время)
     */
    public function updateLocation(Request $request, VisitedLocation $location)
    {
        if ($location->shift->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'ended_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Автоматически рассчитываем продолжительность
        $duration = now()->parse($validated['ended_at'])->diffInMinutes($location->started_at);

        $location->update([
            'ended_at' => $validated['ended_at'],
            'notes' => $validated['notes'],
            'duration_minutes' => $duration,
        ]);

        // Обновляем общее время смены
        $location->shift->calculateTotalTime();

        return response()->json([
            'message' => 'Location updated successfully',
            'location' => $location->fresh()->load('photos')
        ]);
    }

    /**
     * Добавить фото к смене или локации
     */
    public function addPhoto(Request $request, Shift $shift)
    {
        if ($shift->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'photo' => 'required|image|max:10240', // 10MB max
            'visited_location_id' => 'nullable|exists:visited_locations,id',
            'description' => 'nullable|string|max:500',
        ]);

        // Проверяем, что локация принадлежит смене
        if ($request->visited_location_id) {
            $location = VisitedLocation::find($request->visited_location_id);
            if ($location && $location->shift_id !== $shift->id) {
                return response()->json(['error' => 'Location does not belong to this shift'], 400);
            }
        }

        $file = $request->file('photo');
        $path = $file->store("shifts/{$shift->id}/photos", 'public');

        $photo = $shift->photos()->create([
            'visited_location_id' => $request->visited_location_id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
            'taken_at' => now(),
        ]);

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo' => $photo
        ]);
    }

    // === МЕТОДЫ БРИГАДИРА ===

    // Для ожидающих подтверждения
    public function getPendingAssignments(Request $request)
    {
        $user = $request->user();

        $assignments = BrigadierAssignment::with([
                'initiator',
                'assignmentDates' => function($query) {
                    $query->whereDate('assignment_date', '>=', today())
                        ->orderBy('assignment_date');
                }
            ])
            ->where('brigadier_id', $user->id)
            ->where('status', 'pending')
            ->get();

        return response()->json($assignments);
    }

    // Для активных/подтвержденных
    public function getConfirmedAssignments(Request $request)
    {
        $user = $request->user();

        $assignments = BrigadierAssignment::with([
                'initiator',
                'assignmentDates' => function($query) {
                    $query->whereDate('assignment_date', '>=', today())
                        ->orderBy('assignment_date');
                }
            ])
            ->where('brigadier_id', $user->id)
            ->whereIn('status', ['confirmed', 'active'])
            ->get();

        return response()->json($assignments);
    }

    /**
     * Подтвердить назначение бригадиром
     */
    public function confirmAssignment(Request $request, BrigadierAssignment $assignment)
    {
        if ($assignment->brigadier_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $assignment->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Подтверждаем все даты назначения
        $assignment->assignmentDates()->update(['status' => 'confirmed']);

        return response()->json([
            'message' => 'Assignment confirmed successfully',
            'assignment' => $assignment->fresh(['initiator', 'assignmentDates'])
        ]);
    }

    /**
     * Отклонить назначение бригадиром
     */
    public function rejectAssignment(Request $request, BrigadierAssignment $assignment)
    {
        if ($assignment->brigadier_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $assignment->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Отклоняем все даты назначения
        $assignment->assignmentDates()->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Assignment rejected successfully',
            'assignment' => $assignment->fresh(['initiator', 'assignmentDates'])
        ]);
    }

    /**
     * Получить заявки где пользователь бригадир
     */
    public function getBrigadierRequests(Request $request)
    {
        $user = $request->user();

        $requests = WorkRequest::with([
                'initiator',
                'specialty',
                'workType',
                'shifts.user',
                'shifts.specialty'
            ])
            ->where('brigadier_id', $user->id)
            ->whereDate('work_date', '>=', today())
            ->orderBy('work_date')
            ->get();

        return response()->json($requests);
    }
}
