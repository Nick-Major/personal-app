<?php
// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WorkRequestController;
use App\Http\Controllers\Api\BrigadierController;
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
    
    // Work Requests
    Route::get('/my/work-requests', [WorkRequestController::class, 'myRequests']);
    Route::apiResource('work-requests', WorkRequestController::class);
    Route::post('/work-requests/{workRequest}/publish', [WorkRequestController::class, 'publish']);
    Route::get('/work-requests/status/{status}', [WorkRequestController::class, 'byStatus']);
    
    // Brigadiers
    Route::get('/brigadiers/available', [BrigadierController::class, 'availableBrigadiers']);
    Route::post('/brigadier-assignments', [BrigadierController::class, 'assignBrigadier']);
});
