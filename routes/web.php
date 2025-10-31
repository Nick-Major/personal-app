<?php

use App\Livewire\MainDashboard;
use App\Livewire\Auth\Login;
use App\Livewire\Initiator\PlanningDashboard;
use App\Livewire\Executor\ExecutorDashboard;
use App\Livewire\WorkRequests\Index as WorkRequestsIndex;
use App\Livewire\WorkRequests\Create as WorkRequestsCreate;
use Illuminate\Support\Facades\Route;

// Главная страница - MainDashboard
Route::get('/', MainDashboard::class);

// Аутентификация
Route::get('/login', Login::class)->name('login');

// Маршруты для ЛК Инициатора
Route::get('/planning', PlanningDashboard::class)->name('planning');

// Маршруты для ЛК Исполнителя
Route::get('/executor', ExecutorDashboard::class)->name('executor.dashboard');

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

// Выход
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');
