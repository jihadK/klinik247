<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// ===== Root sementara: redirect ke admin login =====
Route::get('/', function () {
    return redirect()->route('admin.login');
})->name('home');

// ===== Portal customer (placeholder Phase berikutnya) =====
// Route::get('/portal', [...])->name('portal.home');

// ===== Admin Portal =====
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest
    Route::middleware('guest')->group(function () {
        Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    });

    // Authenticated
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/',          [DashboardController::class, 'index'])->name('home');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});
