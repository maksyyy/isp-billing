<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function edit(): View
    {
        return view('settings.branding');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'company_logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $request->file('company_logo')->move(public_path(), 'company-logo.png');

        return back()->with('success', 'Logo perusahaan berhasil diperbarui');
    }
}
