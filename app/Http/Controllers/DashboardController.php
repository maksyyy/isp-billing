<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = auth()->user()->role;

        if (in_array($role, ['admin', 'finance', 'noc', 'teknisi'])) {
            return view('dashboard.react');
        }

        if ($role == 'master') {
            $search = $request->get('search');
            $users = User::where('role', 'admin')
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->withCount('subUsers')
                ->latest()
                ->get();
            return view('dashboard.master', compact('users'));
        }

        abort(403);
    }
}
