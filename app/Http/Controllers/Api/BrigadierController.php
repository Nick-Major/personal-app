<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BrigadierAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrigadierController extends Controller
{
    // Получить доступных бригадиров для назначения на дату
    public function availableBrigadiers(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        // ИСПРАВЛЕНО: ищем пользователей с ролью 'brigadier', а не 'executor'
        $brigadiers = User::role('brigadier')
            ->whereDoesntHave('brigadierAssignments', function($query) use ($date) {
                $query->whereHas('assignmentDates', function($q) use ($date) {
                    $q->where('assignment_date', $date)
                      ->where('status', 'confirmed');
                });
            })
            ->select('id', 'name', 'surname', 'specialization', 'phone')
            ->get();

        return response()->json($brigadiers);
    }

    // Назначить бригадира на дату
    public function assignBrigadier(Request $request)
    {
        $validated = $request->validate([
            'brigadier_id' => 'required|exists:users,id',
            'assignment_date' => 'required|date',
        ]);

        // Проверяем, что пользователь является бригадиром
        $brigadier = User::role('brigadier')->find($validated['brigadier_id']);
        if (!$brigadier) {
            return response()->json(['error' => 'Пользователь не является бригадиром'], 400);
        }

        // Проверяем, что бригадир доступен на эту дату
        $existingAssignment = BrigadierAssignment::where('brigadier_id', $validated['brigadier_id'])
            ->whereHas('assignmentDates', function($query) use ($validated) {
                $query->where('assignment_date', $validated['assignment_date'])
                      ->where('status', 'confirmed');
            })
            ->first();

        if ($existingAssignment) {
            return response()->json(['error' => 'Бригадир уже назначен на эту дату'], 400);
        }

        try {
            $assignment = BrigadierAssignment::create([
                'brigadier_id' => $validated['brigadier_id'],
                'initiator_id' => auth()->id(),
                'status' => 'pending'
            ]);

            // Создаем запись даты назначения
            $assignment->assignmentDates()->create([
                'assignment_date' => $validated['assignment_date'],
                'status' => 'pending'
            ]);

            return response()->json([
                'message' => 'Бригадир назначен',
                'assignment' => $assignment->load('assignmentDates')
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при назначении бригадира: ' . $e->getMessage()], 500);
        }
    }
}
