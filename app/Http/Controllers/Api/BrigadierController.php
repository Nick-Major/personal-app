<?php
// app/Http/Controllers/Api/BrigadierController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BrigadierAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrigadierController extends Controller
{
    // Получить доступных бригадиров для назначения
    public function availableBrigadiers(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $brigadiers = User::role('brigadier')
            ->whereDoesntHave('brigadierAssignments', function($query) use ($date) {
                $query->where('assignment_date', $date)
                      ->where('status', 'confirmed');
            })
            ->select('id', 'name', 'email', 'specialization')
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

        try {
            $assignment = BrigadierAssignment::create([
                'brigadier_id' => $validated['brigadier_id'],
                'initiator_id' => auth()->id(),
                'assignment_date' => $validated['assignment_date'],
                'status' => 'pending'
            ]);

            return response()->json([
                'message' => 'Бригадир назначен',
                'assignment' => $assignment
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при назначении бригадира'], 500);
        }
    }
}
