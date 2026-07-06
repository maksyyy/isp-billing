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

            $totalTenants = User::where('role', 'admin')->count();
            $totalCapacity = User::where('role', 'admin')->sum('customer_limit') ?: 0;
            $totalStaff = User::whereNotNull('parent_admin_id')->count();
            $showEvaluationSetting = (bool) auth()->user()->show_evaluation;

            return view('dashboard.master', compact('users', 'totalTenants', 'totalCapacity', 'totalStaff', 'showEvaluationSetting'));
        }

        abort(403);
    }

    public function broadcastEmail(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            return back()->with('success', 'Tidak ada admin penyewa terdaftar untuk dikirimkan email.');
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($admins as $admin) {
            try {
                \Illuminate\Support\Facades\Mail::to($admin->email)->send(
                     new \App\Mail\BroadcastEmail($request->subject, $request->message)
                );
                $successCount++;
            } catch (\Exception $e) {
                \Log::error("Failed to send broadcast email to {$admin->email}: " . $e->getMessage());
                $failedCount++;
            }
        }

        if ($failedCount > 0) {
            return back()->with('success', "Email broadcast berhasil dikirim ke {$successCount} admin. Gagal terkirim ke {$failedCount} admin.");
        }

        return back()->with('success', "Email broadcast berhasil dikirim ke seluruh ({$successCount}) admin penyewa.");
    }

    public function toggleEvaluation(Request $request)
    {
        $user = auth()->user();
        $user->show_evaluation = $request->boolean('show_evaluation');
        $user->save();

        return back()->with('success', 'Status tampilan halaman evaluasi berhasil diperbarui.');
    }
}
