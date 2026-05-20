<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\SettingsController;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;

use Illuminate\Support\Facades\Http;

Route::get('/api/prtg', function () {
    $user = auth()->user();
    $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;

    $prtgUsername = $adminUser->prtg_username ?: env('PRTG_USER');
    $prtgPassword = $adminUser->prtg_password ?: env('PRTG_PASSHASH');

    $response = Http::get(env('PRTG_URL').'/api/table.json', [
        'content' => 'sensors',
        'id' => 0,
        'output' => 'json',
        'columns' => 'device,status,message',
        'username' => $prtgUsername,
        'passhash' => $prtgPassword,
    ]);

    return response()->json($response->json());

})->middleware(['auth', 'role:noc,admin']);

Route::get('/api/dashboard-data', function (Request $request) {

    $role = auth()->user()->role;
    $month = $request->get('month', now()->format('Y-m'));

    $start = Carbon::parse($month . '-01')->startOfMonth();
    $end   = Carbon::parse($month . '-01')->endOfMonth();

    $data = [
        'month' => $month,

        // =========================
        // TOTAL
        // =========================
        'total_customers' => Customer::count(),

        'total_invoices' => Invoice::whereBetween('created_at', [$start, $end])->count(),

        'unpaid_invoices' => Invoice::where('status', 'unpaid')
            ->whereBetween('created_at', [$start, $end])
            ->count(),

        // 🔥 INCOME DARI PEMBAYARAN REAL (FIX)
        'total_income' => Payment::whereBetween('payment_date', [$start, $end])
            ->sum('amount'),

        // 🔥 TOTAL SISA TAGIHAN
        'unpaid_total_amount' => Invoice::where('status', '!=', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->sum(\DB::raw('(amount - paid_amount)')),

        // =========================
        // DETAIL
        // =========================
        'customers' => Customer::select('id', 'name')
            ->latest()
            ->limit(10)
            ->get(),

        'unpaid_list' => Invoice::with('customer:id,name')
            ->where('status', '!=', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(10)
            ->get(),

        'invoice_list' => Invoice::with('customer:id,name')
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(10)
            ->get(),
    ];

    if ($role == 'noc') {
        $data = [
            'month' => $month,
            'total_customers' => $data['total_customers'],
            'customers' => $data['customers'],
        ];
    }

    return response()->json($data);

})->middleware(['auth', 'role:admin,finance,noc']); // 🔥 WAJIB TAMBAH INI
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
    // DASHBOARD
    // =========================
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // =========================
    // API PAGE (React)
    // =========================
    Route::get('/api-page', function () {
        return view('api');
    })->name('api.page');

    // =========================
    // TICKETS
    // =========================

    // SEMUA ROLE
    Route::get('/tickets', [TicketController::class, 'index'])
        ->name('tickets.index');

    // ADMIN + NOC
    Route::middleware(['role:admin,noc'])->group(function () {
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{id}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
        Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    });

    // TEKNISI
    Route::middleware(['role:teknisi'])->group(function () {
        Route::post('/tickets/{id}/selesai', [TicketController::class, 'selesai'])
            ->name('tickets.selesai');
    });

    // =========================
    // INVOICES
    // =========================
    Route::middleware(['role:admin,finance,teknisi'])->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::post('/invoices/{id}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
    });

    Route::middleware(['role:admin,finance'])->group(function () {
        Route::post('/invoices/{id}/selesai', [InvoiceController::class, 'selesai'])->name('invoices.selesai');
        Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
        Route::get('/invoices/generate/form', [InvoiceController::class, 'generateForm'])->name('invoices.generate.form');
        Route::post('/invoices/generate/multiple', [InvoiceController::class, 'generateMultiple'])->name('invoices.generate.multiple');
        Route::post('/invoices/print-selected', [InvoiceController::class, 'printSelected'])->name('invoices.print.selected');
        Route::get('/invoices/print/all', [InvoiceController::class, 'printAll'])->name('invoices.printAll');
        Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });

    // =========================
    // CUSTOMERS
    // =========================
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{id}/history', [CustomerController::class, 'history'])->name('customers.history');

    Route::middleware(['role:admin,finance'])->group(function () {
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // =========================
    // MASTER DATA (ADMIN ONLY)
    // =========================
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('packages', PackageController::class);
    });

    Route::middleware(['role:master,admin'])->group(function () {
        // Unified Settings (Branding, Staf User, PRTG)
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/branding', [SettingsController::class, 'updateBranding'])->name('settings.branding');
        Route::post('/settings/prtg', [SettingsController::class, 'updatePrtg'])->name('settings.prtg');

        // Keep resource routes for sub-users/staf, index is redirected in controller
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/branding', function() {
            return redirect()->route('settings.index', ['tab' => 'branding']);
        })->name('branding.edit');
        Route::post('/branding', [SettingsController::class, 'updateBranding'])->name('branding.update');
    });

    // =========================
    // PROFILE
    // =========================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // =========================
    // ROLE PAGE
    // =========================
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
