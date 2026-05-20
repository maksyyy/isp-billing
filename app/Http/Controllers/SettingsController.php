<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        // 🔥 Identifikasi ID admin (jika sub-user, gunakan parent_admin_id)
        $adminId = $user->parent_admin_id ?: $user->id;
        $adminUser = $user->parent_admin_id ? User::find($user->parent_admin_id) : $user;

        // Ambil data branding
        $logoPath = 'company-logo-' . $adminId . '.png';
        $companyName = $adminUser->company_name ?? 'Laravel Billing & Monitoring';

        // Ambil data PRTG
        $prtgUsername = $adminUser->prtg_username;
        $prtgPassword = $adminUser->prtg_password;

        $search = $request->get('search');

        // Ambil data staf/admin
        if ($user->role == 'master') {
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
            $staffTitle = 'Daftar Admin Penyewa';
        } else {
            $users = User::where('parent_admin_id', $adminId)
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('role', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->get();
            $staffTitle = 'Sub User Staf Operasional';
        }

        // Tentukan tab aktif default
        $activeTab = $request->get('tab', $request->has('search') ? 'staff' : 'branding');

        return view('settings.index', compact(
            'users', 
            'staffTitle', 
            'logoPath', 
            'companyName', 
            'prtgUsername', 
            'prtgPassword',
            'activeTab'
        ));
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $request->validate([
            'company_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $adminUser = $user->parent_admin_id ? User::find($user->parent_admin_id) : $user;
        
        // Simpan pembaruan nama perusahaan ke record admin utama
        $adminUser->company_name = $request->company_name;
        $adminUser->save();

        $adminId = $adminUser->id;
        $fileName = 'company-logo-' . $adminId . '.png';

        if ($request->hasFile('company_logo')) {
            $request->file('company_logo')->move(public_path(), $fileName);
        }

        return redirect()->route('settings.index', ['tab' => 'branding'])
            ->with('success', 'Branding perusahaan berhasil diperbarui');
    }

    public function updatePrtg(Request $request): RedirectResponse
    {
        $request->validate([
            'prtg_username' => ['nullable', 'string', 'max:255'],
            'prtg_password' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $adminUser = $user->parent_admin_id ? User::find($user->parent_admin_id) : $user;

        // Simpan konfigurasi PRTG
        $adminUser->prtg_username = $request->prtg_username;
        $adminUser->prtg_password = $request->prtg_password;
        $adminUser->save();

        return redirect()->route('settings.index', ['tab' => 'prtg'])
            ->with('success', 'Kredensial integrasi PRTG berhasil diperbarui');
    }
}
