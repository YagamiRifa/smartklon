<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpiryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ScannerStateController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------
// Auth Routes
// -------------------------------------------------------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// -------------------------------------------------------
// Protected Routes (requires authentication)
// -------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Stock Management
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('/stock/items', [StockController::class, 'storeItem'])->name('stock.items.store');
    Route::put('/stock/items/{id}', [StockController::class, 'updateItem'])->name('stock.items.update');
    Route::get('/stock/items/{item}/tags', [StockController::class, 'getTagsByItem'])->name('stock.items.tags');

    // Scanner State
    Route::post('/scanner/mode', [ScannerStateController::class, 'update'])->name('scanner.mode.update');
    Route::get('/scanner/mode', [ScannerStateController::class, 'current'])->name('scanner.mode.current');

    // Expiry Management
    Route::get('/expiry', [ExpiryController::class, 'index'])->name('expiry.index');
    Route::post('/expiry', [ExpiryController::class, 'store'])->name('expiry.store');
    Route::put('/expiry/{id}', [ExpiryController::class, 'update'])->name('expiry.update');
    Route::get('/api/items/{id}/batches', [ExpiryController::class, 'getBatches']);
    Route::delete('/api/batches/{id}', [ExpiryController::class, 'destroyBatch']);

    // Notification Management

    // Rute khusus untuk membersihkan notifikasi (diakses via AJAX)
    Route::post('/notifications/clear', [NotificationController::class, 'clear'])
        ->name('notifications.clear')
        ->middleware('auth'); // Wajib login agar auth()->user() tidak error
});
