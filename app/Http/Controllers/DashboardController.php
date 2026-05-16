<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Invoice;

class DashboardController extends Controller
{
    public function index()
    {
          $role = auth()->user()->role;

    if ($role == 'admin') {
        return view('dashboard.admin');
    }

    if ($role == 'finance') {
        return view('dashboard.finance');
    }

    if ($role == 'teknisi') {
        return view('dashboard.teknisi');
    }

    if ($role == 'noc') {
        return view('dashboard.noc');
    }

    if ($role == 'master') {
        return view('dashboard.master');
    }

    abort(403);
        return view('dashboard', [
            'totalCustomers' => Customer::count(),
            'totalPackages' => Package::count(),
            'totalInvoices' => Invoice::count(),
            'paidInvoices' => Invoice::where('status', 'paid')->count(),
        ]);
        
    }
}
