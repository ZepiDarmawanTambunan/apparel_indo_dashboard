<?php

use App\Http\Controllers\MonitoringController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'permission:akses-monitoring'])
    ->prefix('monitoring')
    ->name('monitoring.')
    ->group(function () {
        Route::get('/', [MonitoringController::class, 'index'])->name('index');

        Route::get('/deadline', [MonitoringController::class, 'deadline'])->name('deadline');
        Route::get('/tracking', [MonitoringController::class, 'tracking'])->name('tracking');
});

require __DIR__.'/auth.php';
