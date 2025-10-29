<?php
// routes/web.php

use App\Livewire\Dashboard;
use App\Livewire\Auth\Login;
use App\Livewire\WorkRequests\Index as WorkRequestsIndex;
use App\Livewire\WorkRequests\Create as WorkRequestsCreate;
use Illuminate\Support\Facades\Route;

// Главная страница - Dashboard
Route::get('/', Dashboard::class)->name('dashboard');

// Аутентификация
Route::get('/login', Login::class)->name('login');

// Заявки
Route::get('/work-requests', WorkRequestsIndex::class)->name('work-requests');
Route::get('/work-requests/create', WorkRequestsCreate::class)->name('work-requests.create');

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
