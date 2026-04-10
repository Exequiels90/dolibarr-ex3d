<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

// Health check endpoint for Render.com
Route::get('/health', [HealthController::class, 'index']);

// Filament admin panel routes (auto-registered)
// Route::middleware([
//     'auth:sanctum',
//     config('filament.auth.middleware', ['auth']),
//     'verified'
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     })->name('dashboard');
// });
