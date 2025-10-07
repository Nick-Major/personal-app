<?php

use App\Http\Controllers\Api\BrigadierAssignmentController;
use App\Http\Controllers\Api\ContractorController;
use App\Http\Controllers\Api\InitiatorGrantController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkRequestController;
use Illuminate\Support\Facades\Route;

// Filament Admin Panel (оставляем для админки)
Route::get('/admin/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
