<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Client Resources
    Route::get('/profiles', [ClientController::class, 'profiles'])->name('profiles.index');
    Route::get('/clients/lookup/{cnic}', [ClientController::class, 'lookupByCnic'])->name('clients.lookup');
    Route::resource('clients', ClientController::class)->except(['destroy']);
    Route::middleware('can:delete clients')->group(function () {
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore');
        Route::post('activity-logs/{activity_log}/rollback', [ClientController::class, 'rollback'])->name('activity-logs.rollback');
    });
    Route::post('clients/{client}/installments', [ClientController::class, 'storeInstallments'])->name('clients.installments.store');
    Route::middleware('can:delete installments')->group(function () {
        Route::delete('clients/{client}/installments/clear', [ClientController::class, 'clearInstallments'])->name('clients.installments.clear');
        Route::delete('clients/{client}/installments/{installment}', [ClientController::class, 'destroyInstallment'])->name('clients.installments.destroy');
    });

    // Payments
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::middleware('can:delete payments')->group(function () {
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // Receipts
    Route::get('/receipts/{receipt}/download', [ReceiptController::class, 'download'])->name('receipts.download');

    // Documents
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin-only Settings
Route::middleware(['auth', 'can:manage settings'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

// User Management
Route::middleware(['auth', 'can:manage users'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
});

require __DIR__.'/auth.php';
