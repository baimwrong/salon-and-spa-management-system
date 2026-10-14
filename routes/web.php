<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkImportController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Salon & Spa Management System
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication & Demo Quick-Login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Application Routes
Route::middleware('auth')->group(function () {
    // Dashboard (All Authenticated Staff & Admin)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Appointments & Scheduling
    Route::resource('appointments', AppointmentController::class)->except(['edit', 'update']);
    Route::post('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');

    // Customer Relationship Management (CRM)
    Route::resource('customers', CustomerController::class);

    // Front Desk POS & Billing
    Route::get('/pos', [PosController::class, 'terminal'])->name('pos.terminal');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/invoices', [PosController::class, 'invoices'])->name('pos.invoices.index');
    Route::get('/pos/invoices/{invoice}', [PosController::class, 'showInvoice'])->name('pos.invoices.show');

    // Management & Administration Routes
    Route::middleware('role:admin,manager')->group(function () {
        // Services & Categories
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::post('/services/categories', [ServiceController::class, 'storeCategory'])->name('services.categories.store');

        // Products & Inventory
        Route::resource('products', ProductController::class)->except(['show']);
        Route::post('/products/{product}/restock', [ProductController::class, 'restock'])->name('products.restock');

        // Staff & Stylists Management
        Route::resource('staff', StaffController::class);

        // Commission Ledger
        Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
        Route::post('/commissions/{payout}/mark-paid', [CommissionController::class, 'markPaid'])->name('commissions.markPaid');
        Route::post('/commissions/mark-all-paid', [CommissionController::class, 'markAllPaid'])->name('commissions.markAllPaid');
    });

    // Admin-Only Bulk Data Import Hub
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/import', [BulkImportController::class, 'index'])->name('import.index');
        Route::post('/import', [BulkImportController::class, 'import'])->name('import.process');
        Route::get('/import/sample/{type}', [BulkImportController::class, 'downloadSample'])->name('import.sample');
    });
});
