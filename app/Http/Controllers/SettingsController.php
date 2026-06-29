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
        $timezone = $adminUser->timezone ?? 'Asia/Jakarta';

        // Ambil data PRTG
        $prtgUrl = $adminUser->prtg_url;
        $prtgUsername = $adminUser->prtg_username;
        $prtgPassword = $adminUser->prtg_password;

        // Ambil data MikroTik
        $mikrotikHost = $adminUser->mikrotik_host;
        $mikrotikUsername = $adminUser->mikrotik_username;
        $mikrotikPassword = $adminUser->mikrotik_password;
        $mikrotikPort = $adminUser->mikrotik_port ?? 8728;

        // Ambil data Telegram
        $telegramBotToken = $adminUser->telegram_bot_token;

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

        // Ambil data absensi staf (hanya untuk admin / noc)
        $todayAttendance = [];
        $staffMembers = [];
        $selectedEmployee = $request->get('employee_id');
        $selectedMonth = $request->get('month', \Carbon\Carbon::today()->format('Y-m'));

        if ($user->role == 'admin' || $user->role == 'noc') {
            // Ambil seluruh daftar staf di bawah admin ini
            $staffMembers = User::where('parent_admin_id', $adminId)
                ->orderBy('name')
                ->get();

            $query = \App\Models\Presensi::with('user')
                ->whereHas('user', function ($q) use ($adminId) {
                    $q->where('parent_admin_id', $adminId);
                });

            // Filter per-karyawan (staf)
            if ($selectedEmployee) {
                $query->where('user_id', $selectedEmployee);
            }

            // Filter per-bulan
            if ($selectedMonth) {
                $startOfMonth = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth()->format('Y-m-d');
                $endOfMonth = \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');
                $query->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);
            }

            $todayAttendance = $query->orderBy('tanggal', 'desc')
                ->orderBy('jam_masuk', 'desc')
                ->get();
        }

        // Tentukan tab aktif default
        $activeTab = $request->get('tab', $request->has('search') ? 'staff' : 'branding');
        if ($user->role == 'master' && $activeTab == 'staff') {
            $activeTab = 'branding';
        }

        return view('settings.index', compact(
            'users', 
            'staffTitle', 
            'logoPath', 
            'companyName', 
            'timezone',
            'prtgUrl',
            'prtgUsername', 
            'prtgPassword',
            'mikrotikHost',
            'mikrotikUsername',
            'mikrotikPassword',
            'mikrotikPort',
            'telegramBotToken',
            'activeTab',
            'todayAttendance',
            'staffMembers',
            'selectedEmployee',
            'selectedMonth'
        ));
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $request->validate([
            'company_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'company_name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
            'enable_teknisi_payment' => ['nullable', 'boolean'],
        ]);

        $user = auth()->user();
        $adminUser = $user->parent_admin_id ? User::find($user->parent_admin_id) : $user;
        
        // Simpan pembaruan nama perusahaan, timezone, dan status pembayaran teknisi
        $adminUser->company_name = $request->company_name;
        $adminUser->timezone = $request->timezone;
        $adminUser->enable_teknisi_payment = $request->has('enable_teknisi_payment');
        $adminUser->save();

        $adminId = $adminUser->id;
        $fileName = 'company-logo-' . $adminId . '.png';

        if ($request->hasFile('company_logo')) {
            $request->file('company_logo')->move(public_path(), $fileName);
        }

        return redirect()->route('settings.index', ['tab' => 'branding'])
            ->with('success', 'Branding & konfigurasi wilayah waktu perusahaan berhasil diperbarui');
    }

    public function updatePrtg(Request $request): RedirectResponse
    {
        $request->validate([
            'prtg_url' => ['nullable', 'string', 'max:255'],
            'prtg_username' => ['nullable', 'string', 'max:255'],
            'prtg_password' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $adminUser = $user->parent_admin_id ? User::find($user->parent_admin_id) : $user;

        // Simpan konfigurasi PRTG
        $adminUser->prtg_url = $request->prtg_url;
        $adminUser->prtg_username = $request->prtg_username;
        $adminUser->prtg_password = $request->prtg_password;
        $adminUser->save();

        return redirect()->route('settings.index', ['tab' => 'prtg'])
            ->with('success', 'Konfigurasi integrasi PRTG berhasil diperbarui');
    }

    public function updateMikrotik(Request $request): RedirectResponse
    {
        $request->validate([
            'mikrotik_host' => ['nullable', 'string', 'max:255'],
            'mikrotik_username' => ['nullable', 'string', 'max:255'],
            'mikrotik_password' => ['nullable', 'string', 'max:255'],
            'mikrotik_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ]);

        $user = auth()->user();
        $adminUser = $user->parent_admin_id ? User::find($user->parent_admin_id) : $user;

        $host = $request->mikrotik_host;
        $port = $request->mikrotik_port ?: 8728;

        // Auto-split jika user mengetik IP:Port
        if (!empty($host) && str_contains($host, ':')) {
            $parts = explode(':', $host);
            $host = $parts[0];
            $port = (int)$parts[1];
        }

        // Simpan konfigurasi MikroTik
        $adminUser->mikrotik_host = $host;
        $adminUser->mikrotik_username = $request->mikrotik_username;
        $adminUser->mikrotik_password = $request->mikrotik_password;
        $adminUser->mikrotik_port = $port;
        $adminUser->save();

        return redirect()->route('settings.index', ['tab' => 'mikrotik'])
            ->with('success', 'Konfigurasi integrasi MikroTik RouterOS berhasil diperbarui');
    }

    public function updateTelegram(Request $request): RedirectResponse
    {
        $request->validate([
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $adminUser = $user->parent_admin_id ? User::find($user->parent_admin_id) : $user;

        $adminUser->telegram_bot_token = $request->telegram_bot_token;
        $adminUser->save();

        return redirect()->route('settings.index', ['tab' => 'telegram'])
            ->with('success', 'Konfigurasi integrasi Telegram Bot berhasil diperbarui');
    }

    public function setupWebhook(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $adminUser = $user->parent_admin_id ? User::find($user->parent_admin_id) : $user;
        $token = $adminUser->telegram_bot_token;

        if (!$token) {
            return redirect()->route('settings.index', ['tab' => 'telegram'])
                ->withErrors(['telegram_bot_token' => 'Harap simpan Telegram Bot Token terlebih dahulu sebelum mengaktifkan webhook.']);
        }

        // Webhook URL harus HTTPS publik. 
        $webhookUrl = url("/api/telegram/webhook/{$token}");

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->post("https://api.telegram.org/bot{$token}/setWebhook", [
                'url' => $webhookUrl
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['ok'] ?? false)) {
                $msg = 'Webhook Telegram berhasil diaktifkan! ';
                
                // Tambahkan peringatan jika di localhost
                if (str_contains($webhookUrl, 'localhost') || str_contains($webhookUrl, '127.0.0.1')) {
                    $msg .= '(Catatan: Aplikasi terdeteksi berjalan di localhost. Agar bot bisa merespons chat dari Telegram, gunakan Ngrok dan daftarkan URL HTTPS Ngrok Anda).';
                }

                return redirect()->route('settings.index', ['tab' => 'telegram'])
                    ->with('success', $msg);
            } else {
                $errorMessage = $data['description'] ?? 'Gagal menghubungi Telegram API';
                return redirect()->route('settings.index', ['tab' => 'telegram'])
                    ->withErrors(['telegram_bot_token' => 'Gagal mengaktifkan Webhook: ' . $errorMessage]);
            }
        } catch (\Exception $e) {
            return redirect()->route('settings.index', ['tab' => 'telegram'])
                ->withErrors(['telegram_bot_token' => 'Gagal menghubungi server Telegram: ' . $e->getMessage()]);
        }
    }

    public function testMikrotikConnection(Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'port' => 'required|integer',
        ]);

        $host = $request->host;
        $port = (int)$request->port;

        // Auto-split jika user memasukkan IP:Port saat test koneksi
        if (str_contains($host, ':')) {
            $parts = explode(':', $host);
            $host = $parts[0];
            $port = (int)$parts[1];
        }

        $api = new \App\Services\RouterosAPI();
        
        $success = $api->connect(
            $host,
            $request->username,
            $request->password ?? '',
            $port
        );

        if ($success) {
            $api->disconnect();
            return response()->json([
                'success' => true,
                'message' => 'Koneksi ke MikroTik berhasil terhubung!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung: ' . $api->error_str . ' (' . $api->error_no . ')'
            ]);
        }
    }
}
