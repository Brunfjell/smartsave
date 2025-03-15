<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/analytics', [AnalyticsController::class, 'index'])->middleware(['auth', 'verified'])->name('analytics');

Route::get('/analytics/energy-data', [AnalyticsController::class, 'getEnergyData'])->middleware(['auth', 'verified']);

Route::get('/devices', function () {
    return view('devices');
})->middleware(['auth', 'verified'])->name('devices');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
