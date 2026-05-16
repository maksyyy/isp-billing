<?php

use Illuminate\Support\Facades\Route;
use App\Models\Customer;
use App\Models\Invoice;

Route::get('/dashboard-data', function () {
    return response()->json([
        'total_customers' => Customer::count(),
        'total_invoices' => Invoice::count(),
        'unpaid_invoices' => Invoice::where('status', 'unpaid')->count(),
        'total_income' => Invoice::where('status', 'paid')->sum('amount'),
    ]);
});