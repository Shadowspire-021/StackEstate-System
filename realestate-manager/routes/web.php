<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

// Health check (no auth — required for load balancers and monitoring)
Route::get('/health', HealthController::class)->name('health');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search
    Route::get('/search', [\App\Http\Controllers\GlobalSearchController::class, 'search'])->name('search');

    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{id}', [\App\Http\Controllers\ActivityLogController::class, 'show'])->name('activity-logs.show');

    // Client Resources
    Route::get('/profiles', [ClientController::class, 'profiles'])->name('profiles.index');
    Route::get('/clients/lookup/{cnic}', [ClientController::class, 'lookupByCnic'])->name('clients.lookup')->middleware('throttle:60,1');
    Route::get('/clients/units-by-property', [ClientController::class, 'getUnitsByProperty'])->name('clients.units-by-property')->middleware('throttle:60,1');
    Route::get('/clients/unit-availability', [ClientController::class, 'checkUnitAvailability'])->name('clients.unit-availability')->middleware('throttle:60,1');
    Route::resource('clients', ClientController::class)->except(['destroy']);
    Route::middleware('can:delete clients')->group(function () {
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('clients/bulk-destroy', [ClientController::class, 'bulkDestroy'])->name('clients.bulk-destroy');
        Route::post('clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore');
        Route::post('activity-logs/{activity_log}/rollback', [ClientController::class, 'rollback'])->name('activity-logs.rollback');
    });
    Route::post('clients/{client}/installments', [ClientController::class, 'storeInstallments'])->name('clients.installments.store');
    Route::middleware('can:delete installments')->group(function () {
        Route::delete('clients/{client}/installments/clear', [ClientController::class, 'clearInstallments'])->name('clients.installments.clear');
        Route::delete('clients/{client}/installments/{installment}', [ClientController::class, 'destroyInstallment'])->name('clients.installments.destroy');
        Route::patch('clients/{client}/installments/{installment}/late-fee', [ClientController::class, 'updateLateFee'])->name('clients.installments.late-fee');
    });

    // Payments
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::middleware('can:delete payments')->group(function () {
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // Online Payments
    Route::get('/checkout/{client}/{installment}', [\App\Http\Controllers\OnlinePaymentController::class, 'checkout'])->name('payments.checkout');
    Route::post('/pay/{client}/{installment}', [\App\Http\Controllers\OnlinePaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/success', [\App\Http\Controllers\OnlinePaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/failure', [\App\Http\Controllers\OnlinePaymentController::class, 'failure'])->name('payments.failure');

    // Invoices
    Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [\App\Http\Controllers\InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download');

    // CSV Exports
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/clients/csv', [\App\Http\Controllers\ExportController::class, 'clientsCsv'])->name('clients.csv');
        Route::get('/payments/csv', [\App\Http\Controllers\ExportController::class, 'paymentsCsv'])->name('payments.csv');
        Route::get('/installments/csv', [\App\Http\Controllers\ExportController::class, 'installmentsCsv'])->name('installments.csv');
    });

    // Receipts
    Route::get('/receipts/{receipt}/download', [ReceiptController::class, 'download'])->name('receipts.download');

    // Documents
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/versions', [DocumentController::class, 'versions'])->name('documents.versions');
    Route::get('/documents/{document}/latest-version', [DocumentController::class, 'latestVersion'])->name('documents.latest-version');
    Route::post('/documents/{document}/rollback/{targetVersion}', [DocumentController::class, 'rollbackVersion'])->name('documents.rollback');
    Route::get('/documents/audit-integrity', [DocumentController::class, 'auditDocumentIntegrity'])->name('documents.audit-integrity');
    Route::get('/documents/audit-integrity/{client}', [DocumentController::class, 'auditDocumentIntegrity'])->name('documents.audit-integrity-client');

    // Unit Management
    Route::resource('units', \App\Http\Controllers\UnitController::class);

    // Installment Plan Templates
    Route::resource('templates', \App\Http\Controllers\InstallmentPlanTemplateController::class)->except(['show']);

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Google OAuth (authenticated endpoints)
Route::middleware(['auth', 'can:manage settings'])->prefix('google-oauth')->name('google-oauth.')->group(function () {
    Route::get('/connect', [\App\Http\Controllers\GoogleOAuthController::class, 'redirectToGoogle'])->name('connect');
    Route::get('/status', [\App\Http\Controllers\GoogleOAuthController::class, 'status'])->name('status');
    Route::post('/disconnect', [\App\Http\Controllers\GoogleOAuthController::class, 'disconnect'])->name('disconnect');
});

// Google OAuth callback (no auth — Google redirects here without session)
Route::get('/google-oauth/callback', [\App\Http\Controllers\GoogleOAuthController::class, 'handleCallback'])->name('google-oauth.callback');

// Admin-only Settings
Route::middleware(['auth', 'can:manage settings'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

// Backup Management (Admin only)
Route::middleware(['auth', 'can:manage settings'])->prefix('backups')->name('backups.')->group(function () {
    Route::get('/', [\App\Http\Controllers\BackupController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\BackupController::class, 'store'])->name('store');
    Route::post('/queued', [\App\Http\Controllers\BackupController::class, 'storeQueued'])->name('store.queued');
    Route::post('/{filename}/verify', [\App\Http\Controllers\BackupController::class, 'verify'])->name('verify');
    Route::delete('/{filename}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('destroy');
});

// Queue Management (Admin only)
Route::middleware(['auth', 'can:manage users'])->prefix('queue')->name('queue.')->group(function () {
    Route::get('/failed', [\App\Http\Controllers\QueueController::class, 'failedJobs'])->name('failed');
    Route::post('/failed/{jobId}/retry', [\App\Http\Controllers\QueueController::class, 'retryJob'])->name('retry');
    Route::delete('/failed/{jobId}', [\App\Http\Controllers\QueueController::class, 'deleteJob'])->name('delete');
});

// User Management
Route::middleware(['auth', 'can:manage users'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
});

// Notifications
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
});

// Reports & Exports
Route::middleware('auth')->group(function () {
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/clients-csv', [\App\Http\Controllers\ReportController::class, 'clientsCsv'])->name('reports.clients-csv');
    Route::get('/reports/payments-csv', [\App\Http\Controllers\ReportController::class, 'paymentsCsv'])->name('reports.payments-csv');
    Route::get('/reports/installments-csv', [\App\Http\Controllers\ReportController::class, 'installmentsCsv'])->name('reports.installments-csv');
});

// Payment Gateway Webhooks (no auth required)
Route::post('/webhook/payment/{gateway}', [\App\Http\Controllers\OnlinePaymentController::class, 'webhook'])->name('payments.webhook');

require __DIR__.'/auth.php';
