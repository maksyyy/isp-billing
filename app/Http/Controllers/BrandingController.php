<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        
        // 🔥 Identifikasi ID admin (jika sub-user, gunakan parent_admin_id)
        $adminId = $user->parent_admin_id ?: $user->id;
        $logoPath = 'company-logo-' . $adminId . '.png';

        // Ambil nama perusahaan, gunakan default jika masih kosong
        $companyName = $user->company_name ?? 'Laravel Billing & Monitoring';

        return view('settings.branding', compact('logoPath', 'companyName'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'company_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        
        // Simpan pembaruan nama perusahaan
        $user->company_name = $request->company_name;
        $user->save();

        $adminId = $user->parent_admin_id ?: $user->id;
        $fileName = 'company-logo-' . $adminId . '.png';

        if ($request->hasFile('company_logo')) {
            $request->file('company_logo')->move(public_path(), $fileName);
        }

        return back()->with('success', 'Branding perusahaan berhasil diperbarui');
    }
}
