<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'customer_limit' => ['required', 'integer', 'in:200,500,1000,2000,3000,4000,5000'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Kirim email notifikasi ke Master Admin
        Mail::raw("Halo Master Admin,\n\n" .
            "Terdapat pendaftaran akun Admin baru di ISP Billing yang memerlukan persetujuan Anda.\n\n" .
            "Berikut rincian pendaftar:\n" .
            "- Nama: " . $request->name . "\n" .
            "- Email: " . $request->email . "\n" .
            "- No. HP: " . $request->phone . "\n" .
            "- Paket Jumlah User: " . $request->customer_limit . " User\n" .
            "- Password: " . $request->password . "\n\n" .
            "Catatan: Pendaftaran ini belum dimasukkan ke database sistem dan membutuhkan persetujuan Anda secara manual.", function ($message) {
                $message->to('fadhilakbar394@gmail.com')
                    ->subject('🔔 Pendaftaran Admin Baru Perlu Persetujuan');
            });

        return redirect()->route('login')->with('status', 'Pendaftaran berhasil dikirim! Akun Anda sedang ditinjau oleh Master Admin. Silakan tunggu konfirmasi selanjutnya.');
    }
}
