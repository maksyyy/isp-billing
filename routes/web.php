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

    $prtgUrl = $adminUser->prtg_url ?: env('PRTG_URL');
    $prtgUsername = $adminUser->prtg_username ?: env('PRTG_USER');
    $prtgPassword = $adminUser->prtg_password ?: env('PRTG_PASSHASH');

    if (empty($prtgUrl)) {
        return response()->json(['sensors' => [], 'error' => 'URL PRTG belum dikonfigurasi.']);
    }

    $cacheKey = 'prtg_sensors_admin_' . $adminUser->id;

    $response = \Illuminate\Support\Facades\Cache::remember($cacheKey, 15, function () use ($prtgUrl, $prtgUsername, $prtgPassword) {
        try {
            $res = Http::timeout(5)->get($prtgUrl.'/api/table.json', [
                'content' => 'sensors',
                'id' => 0,
                'output' => 'json',
                'columns' => 'device,status,status_raw,message,lastup,lastup_raw,lastdown,lastdown_raw',
                'username' => $prtgUsername,
                'passhash' => $prtgPassword,
            ]);

            if ($res->successful()) {
                return $res->json();
            }

            return ['sensors' => [], 'error' => 'Respon tidak sukses dari server PRTG.'];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PRTG API Route Error: " . $e->getMessage());
            return ['sensors' => [], 'error' => 'Gagal menghubungi server PRTG: ' . $e->getMessage()];
        }
    });

    return response()->json($response);

})->middleware(['auth', 'role:noc,admin,teknisi']);

Route::get('/api/mikrotik/dashboard-data', function () {
    $user = auth()->user();
    $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;

    $cacheKey = 'mikrotik_dashboard_admin_' . $adminUser->id;

    $response = \Illuminate\Support\Facades\Cache::remember($cacheKey, 15, function () use ($adminUser) {
        $service = new \App\Services\MikrotikService();
        $connected = $service->connect($adminUser);

        if (!$connected) {
            return [
                'connected' => false,
                'message' => 'Router MikroTik tidak terhubung atau kredensial belum dikonfigurasi.'
            ];
        }

        // PENTING: getInterfaces() harus dipanggil PERTAMA sebelum operasi berat lainnya
        // RouterOS API buffer bisa exhausted setelah banyak data di-fetch
        $interfaces = $service->getInterfaces();
        $resources   = $service->getSystemResources();
        $activeUsers = $service->getActiveUsers();
        $addressLists = $service->getMatchedAddressLists();

        $service->disconnect();

        return [
            'connected'     => true,
            'resources'     => $resources,
            'active_users'  => $activeUsers,
            'address_lists' => $addressLists,
            'interfaces'    => $interfaces,
        ];
    });

    return response()->json($response);
})->middleware(['auth', 'role:noc,admin,teknisi']);


Route::post('/api/mikrotik/test-connection', [SettingsController::class, 'testMikrotikConnection'])
    ->middleware(['auth', 'role:admin,noc'])
    ->name('api.mikrotik.test');

// Lightweight traffic poll — hanya fetch 1 interface yang sedang di-select
Route::get('/api/mikrotik/interface-traffic', function (Request $request) {
    $ifaceName = $request->get('name');
    if (empty($ifaceName)) {
        return response()->json(['error' => 'Parameter name wajib diisi.'], 422);
    }

    $user = auth()->user();
    $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;

    $service = new \App\Services\MikrotikService();
    $connected = $service->connect($adminUser);

    if (!$connected) {
        return response()->json(['error' => 'Tidak dapat terhubung ke MikroTik.'], 503);
    }

    $data = $service->getInterfaceTraffic($ifaceName);
    $service->disconnect();

    if (!$data) {
        return response()->json(['error' => "Interface '{$ifaceName}' tidak ditemukan."], 404);
    }

    return response()->json($data);
})->middleware(['auth', 'role:noc,admin,teknisi']);


Route::get('/api/dashboard-data', function (Request $request) {

    $user  = auth()->user();
    $role  = $user->role;
    $month = $request->get('month', now()->format('Y-m'));

    // Resolve admin_id scope
    if ($role === 'admin') {
        $adminId = $user->id;
    } elseif ($role === 'master') {
        $adminId = null; // master sees all
    } else {
        // finance / noc / teknisi: scope to parent admin
        $adminId = $user->parent_admin_id;
    }

    $start = Carbon::parse($month . '-01')->startOfMonth();
    $end   = Carbon::parse($month . '-01')->endOfMonth();

    $cacheKey = 'api_dashboard_data_' . ($adminId ?? 'master') . '_' . $month . '_' . $role;

    $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 10, function () use ($adminId, $month, $start, $end, $role) {
        // Riwayat Tiket Bulanan (6 bulan terakhir) - Optimasi Kueri Tunggal
        $historyStart = now()->subMonths(5)->startOfMonth();
        $historyEnd = now()->endOfMonth();

        $ticketsInRange = \App\Models\Ticket::whereBetween('tanggal', [$historyStart->format('Y-m-d'), $historyEnd->format('Y-m-d')])
            ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
            ->select('status', 'tanggal')
            ->get();

        $monthly_history = [];
        for ($i = 5; $i >= 0; $i--) {
            $m      = now()->subMonths($i);
            $startM = $m->copy()->startOfMonth()->format('Y-m-d');
            $endM   = $m->copy()->endOfMonth()->format('Y-m-d');

            $ticketsInMonth = $ticketsInRange->filter(function ($t) use ($startM, $endM) {
                return $t->tanggal >= $startM && $t->tanggal <= $endM;
            });

            $openCount = $ticketsInMonth->where('status', 'open')->count();
            $doneCount = $ticketsInMonth->where('status', 'done')->count();

            $monthly_history[] = [
                'label' => $m->translatedFormat('F Y'),
                'month' => $m->format('Y-m'),
                'open'  => $openCount,
                'done'  => $doneCount,
                'total' => $openCount + $doneCount,
            ];
        }

        $res = [
            'month' => $month,

            // =========================
            // TOTAL
            // =========================
            'total_customers' => Customer::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->count(),

            'total_invoices' => Invoice::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->whereBetween('created_at', [$start, $end])->count(),

            'unpaid_invoices' => Invoice::where('status', 'unpaid')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->whereBetween('created_at', [$start, $end])
                ->count(),

            // 🔥 INCOME DARI PEMBAYARAN REAL
            'total_income' => Payment::whereHas('invoice', function ($q) use ($adminId) {
                    if ($adminId !== null) $q->where('admin_id', $adminId);
                })->whereBetween('payment_date', [$start, $end])->sum('amount'),

            // 🔥 TOTAL SISA TAGIHAN
            'unpaid_total_amount' => Invoice::where('status', '!=', 'paid')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->whereBetween('created_at', [$start, $end])
                ->sum(\DB::raw('(amount - paid_amount)')),

            // =========================
            // DETAIL / TICKETS
            // =========================
            'tickets_open_total' => \App\Models\Ticket::where('status', 'open')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->count(),
            'tickets_done_total' => \App\Models\Ticket::where('status', 'done')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->count(),
            'tickets_open_month' => \App\Models\Ticket::where('status', 'open')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->count(),
            'tickets_done_month' => \App\Models\Ticket::where('status', 'done')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->count(),
            'open_tickets_list' => \App\Models\Ticket::with(['customer:id,name', 'teknisi:id,name'])
                ->where('status', 'open')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->latest()
                ->limit(15)
                ->get(),
            'done_tickets_list' => \App\Models\Ticket::with(['customer:id,name', 'teknisi:id,name'])
                ->where('status', 'done')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->latest()
                ->limit(15)
                ->get(),
            'monthly_ticket_history' => $monthly_history,

            'customers' => Customer::select('id', 'name')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->latest()
                ->limit(10)
                ->get(),

            'unpaid_list' => Invoice::with('customer:id,name')
                ->where('status', '!=', 'paid')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->whereBetween('created_at', [$start, $end])
                ->latest()
                ->limit(10)
                ->get(),

            'invoice_list' => Invoice::with('customer:id,name')
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->whereBetween('created_at', [$start, $end])
                ->latest()
                ->limit(10)
                ->get(),
        ];

        if ($role == 'noc') {
            $res = [
                'month'                  => $month,
                'total_customers'        => $res['total_customers'],
                'customers'              => $res['customers'],
                'tickets_open_total'     => $res['tickets_open_total'],
                'tickets_done_total'     => $res['tickets_done_total'],
                'tickets_open_month'     => $res['tickets_open_month'],
                'tickets_done_month'     => $res['tickets_done_month'],
                'open_tickets_list'      => $res['open_tickets_list'],
                'done_tickets_list'      => $res['done_tickets_list'],
                'monthly_ticket_history' => $res['monthly_ticket_history'],
            ];
        }

        return $res;
    });

    return response()->json($data);

})->middleware(['auth', 'role:admin,finance,noc,teknisi']);

Route::get('/api/monthly-tickets', function (Request $request) {
    $request->validate([
        'month' => 'required|date_format:Y-m'
    ]);

    $user  = auth()->user();
    $month = $request->month;
    $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
    $end   = \Carbon\Carbon::parse($month . '-01')->endOfMonth();

    // Resolve admin scope
    $adminId = $user->role === 'admin' ? $user->id : $user->parent_admin_id;

    $tickets = \App\Models\Ticket::with(['customer:id,name', 'teknisi:id,name'])
        ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
        ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
        ->latest()
        ->get();

    return response()->json($tickets);
})->middleware(['auth', 'role:admin,finance,noc,teknisi']);

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::post('/api/telegram/webhook/{token}', [App\Http\Controllers\TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook');

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

    Route::post('/dashboard/master/broadcast', [DashboardController::class, 'broadcastEmail'])
        ->name('dashboard.broadcast')
        ->middleware('role:master');

    // =========================
    // SECURE WEB SSH TERMINAL (MASTER ONLY)
    // =========================
    Route::middleware('role:master')->group(function () {
        Route::get('/terminal', [\App\Http\Controllers\TerminalController::class, 'index'])->name('terminal.index');
        Route::post('/terminal/connect', [\App\Http\Controllers\TerminalController::class, 'connect'])->name('terminal.connect');
        Route::post('/terminal/execute', [\App\Http\Controllers\TerminalController::class, 'execute'])->name('terminal.execute');
        Route::delete('/terminal/disconnect', [\App\Http\Controllers\TerminalController::class, 'disconnect'])->name('terminal.disconnect');
    });

    // =========================
    // API PAGE (React redirect to Unified Dashboard)
    // =========================
    Route::get('/api-page', function () {
        return redirect()->route('dashboard');
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

        // Backbone Monitoring Page & API
        Route::get('/backbone-alerts', [App\Http\Controllers\BackboneDeviceController::class, 'index'])->name('backbone.index');
        Route::get('/api/backbone-devices', [App\Http\Controllers\BackboneDeviceController::class, 'apiIndex'])->name('backbone.api.index');
        Route::post('/api/backbone-devices', [App\Http\Controllers\BackboneDeviceController::class, 'apiStore'])->name('backbone.api.store');
        Route::put('/api/backbone-devices/{id}', [App\Http\Controllers\BackboneDeviceController::class, 'apiUpdate'])->name('backbone.api.update');
        Route::delete('/api/backbone-devices/{id}', [App\Http\Controllers\BackboneDeviceController::class, 'apiDestroy'])->name('backbone.api.destroy');
    });

    // =========================
    // PRESENSI
    // =========================
    Route::get('/presensi', [\App\Http\Controllers\PresensiController::class, 'index'])->name('presensi.index');
    Route::post('/presensi', [\App\Http\Controllers\PresensiController::class, 'store'])->name('presensi.store');

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
        Route::post('/invoices/delete-selected', [InvoiceController::class, 'destroySelected'])->name('invoices.destroy.selected');
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
        Route::post('/customers/import-prtg', [CustomerController::class, 'importFromPrtg'])->name('customers.import-prtg');
        Route::post('/customers/sync-mikrotik', [CustomerController::class, 'syncAllToMikrotik'])->name('customers.sync-mikrotik');
        Route::post('/customers/sync-prtg', [CustomerController::class, 'syncAllToPrtg'])->name('customers.sync-prtg');
        Route::post('/customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
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
        Route::post('/settings/email', [SettingsController::class, 'updateEmailSettings'])->name('settings.email');
        Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
        Route::post('/settings/prtg', [SettingsController::class, 'updatePrtg'])->name('settings.prtg');
        Route::post('/settings/mikrotik', [SettingsController::class, 'updateMikrotik'])->name('settings.mikrotik');
        Route::post('/settings/telegram', [SettingsController::class, 'updateTelegram'])->name('settings.telegram');
        Route::post('/settings/telegram/webhook', [SettingsController::class, 'setupWebhook'])->name('settings.telegram.webhook');

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



});
 
require __DIR__.'/auth.php';
