<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrigadierAssignmentController;
use App\Http\Controllers\Api\ContractorController;
use App\Http\Controllers\Api\InitiatorGrantController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkRequestController;
use Illuminate\Support\Facades\Route;

// API Routes with /api prefix
Route::prefix('api')->group(function () {
    // Public routes - аутентификация
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Protected routes - требуют аутентификации
    Route::middleware(['auth:sanctum'])->group(function () {
        // Auth
        Route::get('/user', [AuthController::class, 'user']);

        // Users
        Route::apiResource('users', UserController::class);
        Route::get('/users/role/{role}', [UserController::class, 'byRole']);

        // Contractors
        Route::apiResource('contractors', ContractorController::class);
        Route::get('/contractors/active', [ContractorController::class, 'active']);

        // Work Requests
        Route::apiResource('work-requests', WorkRequestController::class);
        Route::get('/work-requests/status/{status}', [WorkRequestController::class, 'byStatus']);
        Route::post('/work-requests/{workRequest}/publish', [WorkRequestController::class, 'publish']);

        // Brigadier Assignments
        Route::apiResource('brigadier-assignments', BrigadierAssignmentController::class);
        Route::get('/brigadier-assignments/brigadier/{brigadierId}', [BrigadierAssignmentController::class, 'byBrigadier']);
        Route::post('/brigadier-assignments/{brigadierAssignment}/confirm', [BrigadierAssignmentController::class, 'confirm']);
        Route::post('/brigadier-assignments/{brigadierAssignment}/reject', [BrigadierAssignmentController::class, 'reject']);

        // Shifts
        Route::apiResource('shifts', ShiftController::class);
        Route::get('/shifts/work-request/{workRequestId}', [ShiftController::class, 'byWorkRequest']);
        Route::post('/shifts/{shift}/start', [ShiftController::class, 'start']);
        Route::post('/shifts/{shift}/complete', [ShiftController::class, 'complete']);

        // Initiator Grants
        Route::apiResource('initiator-grants', InitiatorGrantController::class);
        Route::get('/initiator-grants/active', [InitiatorGrantController::class, 'active']);
        Route::post('/initiator-grants/{initiatorGrant}/activate', [InitiatorGrantController::class, 'activate']);
        Route::post('/initiator-grants/{initiatorGrant}/deactivate', [InitiatorGrantController::class, 'deactivate']);
    });
});

// Filament Admin Panel (оставляем для админки)
Route::get('/admin/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
