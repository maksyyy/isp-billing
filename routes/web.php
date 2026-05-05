<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| AUTH (WAJIB LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // =========================
    // DASHBOARD (SEMUA ROLE)
    // =========================
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // =========================
    // 🔥 TICKETS
    // =========================

    // ✅ SEMUA ROLE BISA LIHAT
    Route::get('/tickets', [TicketController::class, 'index'])
        ->name('tickets.index');

    // ✅ ADMIN + NOC (FULL CRUD)
    Route::middleware(['role:admin,noc'])->group(function () {

        Route::get('/tickets/create', [TicketController::class, 'create'])
            ->name('tickets.create');

        Route::post('/tickets', [TicketController::class, 'store'])
            ->name('tickets.store');

        Route::get('/tickets/{id}/edit', [TicketController::class, 'edit'])
            ->name('tickets.edit');

        Route::put('/tickets/{id}', [TicketController::class, 'update'])
            ->name('tickets.update');

        Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])
            ->name('tickets.destroy');
    });

    // ✅ TEKNISI (SELESAIKAN + UPLOAD BUKTI)
    Route::middleware(['role:teknisi'])->group(function () {

        Route::post('/tickets/{id}/selesai', [TicketController::class, 'selesai'])
            ->name('tickets.selesai');
    });

    // =========================
    // 🔥 INVOICE
    // =========================
    Route::middleware(['role:admin,finance,teknisi'])->group(function () {

        Route::get('/invoices', [InvoiceController::class, 'index'])
            ->name('invoices.index');

        Route::post('/invoices/{id}/pay', [InvoiceController::class, 'pay'])
            ->name('invoices.pay');
    });

    Route::middleware(['role:admin,finance'])->group(function () {

        Route::post('/invoices/{id}/selesai', [InvoiceController::class, 'selesai'])
            ->name('invoices.selesai');

        Route::post('/invoices/generate', [InvoiceController::class, 'generate'])
            ->name('invoices.generate');

        Route::get('/invoices/generate/form', [InvoiceController::class, 'generateForm'])
            ->name('invoices.generate.form');

        Route::post('/invoices/generate/multiple', [InvoiceController::class, 'generateMultiple'])
            ->name('invoices.generate.multiple');

        Route::post('/invoices/print-selected', [InvoiceController::class, 'printSelected'])
            ->name('invoices.print.selected');

        Route::get('/invoices/print/all', [InvoiceController::class, 'printAll'])
            ->name('invoices.printAll');

        Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])
            ->name('invoices.print');
    });

    // =========================
    // 🔥 CUSTOMER
    // =========================

    // ✅ SEMUA ROLE LIHAT
    Route::get('/customers', [CustomerController::class, 'index'])
        ->name('customers.index');

    Route::get('/customers/{id}/history', [CustomerController::class, 'history'])
        ->name('customers.history');

    // ✅ ADMIN & FINANCE CRUD
    Route::middleware(['role:admin,finance'])->group(function () {

        Route::get('/customers/create', [CustomerController::class, 'create'])
            ->name('customers.create');

        Route::post('/customers', [CustomerController::class, 'store'])
            ->name('customers.store');

        Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])
            ->name('customers.edit');

        Route::put('/customers/{id}', [CustomerController::class, 'update'])
            ->name('customers.update');

        Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])
            ->name('customers.destroy');
    });

    // =========================
    // 🔥 MASTER DATA (ADMIN ONLY)
    // =========================
    Route::middleware(['role:admin'])->group(function () {

        Route::resource('packages', PackageController::class);
        Route::resource('users', UserController::class);
    });

    // =========================
    // PROFILE
    // =========================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth'])->group(function () {

    Route::resource('tickets', TicketController::class);

    Route::post('/tickets/{id}/selesai',
        [TicketController::class, 'selesai'])
        ->name('tickets.selesai');
});
/*
|--------------------------------------------------------------------------
| ROLE PAGE (OPTIONAL)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', fn() => "Dashboard Admin");
    });

    Route::middleware('role:teknisi')->group(function () {
        Route::get('/teknisi', fn() => "Dashboard Teknisi");
    });

    Route::middleware('role:noc')->group(function () {
        Route::get('/noc', fn() => "Dashboard NOC");
    });
});

require __DIR__.'/auth.php';