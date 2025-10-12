<?php
// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WorkRequestController;
use App\Http\Controllers\Api\BrigadierController;
use App\Http\Controllers\Api\BrigadierAssignmentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ExecutorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // Public routes
    Route::get('/debug', function () {
        return response()->json(['message' => 'API is working']);
    });

    Route::get('/debug/work-requests-public', function () {
        try {
            $requests = \App\Models\WorkRequest::with(['initiator', 'brigadier', 'specialty', 'workType'])->get();
            return response()->json($requests);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    });
    
    Route::post('/login', [AuthController::class, 'login']);
    // Простые маршруты без контроллеров
    Route::get('/specialties', function () {
        return \App\Models\Specialty::all();
    });
    
    Route::get('/work-types', function () {
        return \App\Models\WorkType::all();
    });

    // Добавить временные endpoints для отладки
    Route::get('/debug/specialties', function () {
        return \App\Models\Specialty::all();
    });

    Route::get('/debug/work-types', function () {
        return \App\Models\WorkType::all();
    });

    Route::get('/debug/work-requests', function () {
        return \App\Models\WorkRequest::with(['initiator', 'brigadier', 'specialty', 'workType'])->get();
    });

    Route::get('/debug/my-work-requests', function (Request $request) {
        $user = $request->user();
        return \App\Models\WorkRequest::with(['initiator', 'brigadier', 'specialty', 'workType'])
            ->where('initiator_id', $user->id)
            ->get();
    });
});

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Users
    Route::get('/users/brigadiers', [UserController::class, 'getBrigadiers']);
    Route::get('/users/by-role/{role}', [UserController::class, 'byRole']);
    
    // Work Requests
    Route::get('/my/work-requests', [WorkRequestController::class, 'myRequests']);
    Route::apiResource('work-requests', WorkRequestController::class);
    Route::post('/work-requests/{workRequest}/publish', [WorkRequestController::class, 'publish']);
    Route::get('/work-requests/status/{status}', [WorkRequestController::class, 'byStatus']);
    
    // Brigadiers
    Route::get('/brigadiers/available', [BrigadierController::class, 'availableBrigadiers']);
    // Route::post('/brigadier-assignments', [BrigadierController::class, 'assignBrigadier']);
    
    // Brigadier Assignments
    Route::apiResource('brigadier-assignments', BrigadierAssignmentController::class);
    Route::post('/brigadier-assignments/{brigadierAssignment}/confirm', [BrigadierAssignmentController::class, 'confirm']);
    Route::post('/brigadier-assignments/{brigadierAssignment}/reject', [BrigadierAssignmentController::class, 'reject']);
    Route::get('/my/brigadier-assignments', [BrigadierAssignmentController::class, 'myAssignments']);
    
    // === ЛК ИСПОЛНИТЕЛЯ (Executor) ===
    Route::prefix('my')->group(function () {
        // Смены
        Route::get('/shifts', [ExecutorController::class, 'myShifts']);
        Route::get('/shifts/active', [ExecutorController::class, 'activeShifts']);
        Route::post('/shifts/{shift}/start', [ExecutorController::class, 'startShift']);
        Route::post('/shifts/{shift}/end', [ExecutorController::class, 'endShift']);
        
        // Локации
        Route::post('/shifts/{shift}/locations', [ExecutorController::class, 'addLocation']);
        Route::put('/locations/{location}', [ExecutorController::class, 'updateLocation']);
        
        // Фото
        Route::post('/shifts/{shift}/photos', [ExecutorController::class, 'addPhoto']);
    });

    
});
