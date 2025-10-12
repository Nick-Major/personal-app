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

Route::get('/test-storage', function() {
    return [
        'disk' => config('filesystems.default'),
        'public_url' => Storage::disk('public')->url('test.jpg'),
        'public_path' => Storage::disk('public')->path('test.jpg'),
    ];
});
