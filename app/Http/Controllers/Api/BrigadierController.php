<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrigadierController extends Controller
{
    // Получить доступных бригадиров для назначения на дату
    public function availableBrigadiers(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        // Ищем пользователей с ролью 'brigadier'
        $brigadiers = User::role('brigadier')
            ->whereDoesntHave('assignments', function($query) use ($date) {
                $query->where('assignment_type', 'brigadier_schedule')
                      ->where('planned_date', $date)
                      ->where('status', 'confirmed');
            })
            ->select('id', 'name', 'surname', 'phone')
            ->get();

        return response()->json($brigadiers);
    }

    // Назначить бригадира на дату
    public function assignBrigadier(Request $request)
    {
        $validated = $request->validate([
            'brigadier_id' => 'required|exists:users,id',
            'planned_date' => 'required|date',
            'planned_start_time' => 'required|date_format:H:i',
            'planned_duration_hours' => 'required|numeric|min:1|max:24',
            'planned_address_id' => 'nullable|exists:addresses,id',
            'assignment_comment' => 'nullable|string|max:1000',
        ]);

        // Проверяем, что пользователь является бригадиром
        $brigadier = User::role('brigadier')->find($validated['brigadier_id']);
        if (!$brigadier) {
            return response()->json(['error' => 'Пользователь не является бригадиром'], 400);
        }

        // Проверяем, что бригадир доступен на эту дату
        $existingAssignment = Assignment::where('user_id', $validated['brigadier_id'])
            ->where('assignment_type', 'brigadier_schedule')
            ->where('planned_date', $validated['planned_date'])
            ->where('status', 'confirmed')
            ->first();

        if ($existingAssignment) {
            return response()->json(['error' => 'Бригадир уже назначен на эту дату'], 400);
        }

        try {
            $assignment = Assignment::create([
                'assignment_type' => 'brigadier_schedule',
                'user_id' => $validated['brigadier_id'],
                'role_in_shift' => 'brigadier',
                'source' => 'initiator',
                'planned_date' => $validated['planned_date'],
                'planned_start_time' => $validated['planned_start_time'],
                'planned_duration_hours' => $validated['planned_duration_hours'],
                'planned_address_id' => $validated['planned_address_id'] ?? null,
                'assignment_comment' => $validated['assignment_comment'] ?? null,
                'status' => 'pending'
            ]);

            return response()->json([
                'message' => 'Бригадир назначен',
                'assignment' => $assignment
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при назначении бригадира: ' . $e->getMessage()], 500);
        }
    }
}
