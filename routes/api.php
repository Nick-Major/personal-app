<?php
// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WorkRequestController;
use App\Http\Controllers\Api\BrigadierController;
use App\Http\Controllers\Api\BrigadierAssignmentController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // Public routes
    Route::get('/debug', function () {
        return response()->json(['message' => 'API is working']);
    });
    
    Route::post('/login', [AuthController::class, 'login']);
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
});
