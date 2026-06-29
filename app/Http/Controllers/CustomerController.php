<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Http\Controllers\Concerns\AdminScoped;

class CustomerController extends Controller
{
    use AdminScoped;
    // =========================
    // INDEX + SEARCH
    // =========================
    public function index(Request $request)
    {
        $search  = $request->search;
        $adminId = $this->resolveAdminId();

        $customers = Customer::with('package')
            ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                      ->orWhere('customer_code', 'like', "%$search%")
                      ->orWhere('phone', 'like', "%$search%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    // =========================
    // CREATE
    // =========================
    public function create()
    {
        $adminId  = $this->resolveAdminId();
        $packages = Package::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->get();
        return view('customers.create', compact('packages'));
    }

    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'customer_code' => 'required|unique:customers,customer_code',
            'name' => 'required',
            'ip' => 'nullable|string|max:255',
            'package_id' => 'required'
        ]);

        $user      = auth()->user();
        $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;
        $adminId   = $this->resolveAdminId();

        if ($adminUser && $adminUser->role === 'admin') {
            $customerCount = Customer::where('admin_id', $adminId)->count();
            if ($customerCount >= $adminUser->customer_limit) {
                return redirect()->route('customers.index')
                    ->with('error', "Gagal menambahkan pelanggan: Batas jumlah pelanggan ({$adminUser->customer_limit}) telah tercapai!");
            }
        }

        $customer = Customer::create([
            'admin_id'      => $adminId,
            'customer_code' => $request->customer_code,
            'name'          => $request->name,
            'phone'         => $request->phone,
            'ip'            => $request->ip,
            'address'       => $request->address,
            'package_id'    => $request->package_id,
        ]);

        // 🔥 Tambahkan pelanggan ke PRTG & MikroTik secara otomatis jika IP/Host diisi
        if ($request->ip) {
            $user = auth()->user();
            $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;

            // 1. Sync MikroTik
            try {
                $mikrotikService = new \App\Services\MikrotikService();
                $mikrotikService->ensureCustomerInAddressList($adminUser, $customer);
                \Illuminate\Support\Facades\Log::info("MikroTik: Berhasil mendaftarkan/memverifikasi IP '{$request->ip}' untuk pelanggan '{$request->name}' di firewall address-list");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("MikroTik Sync Store Error: Gagal mendaftarkan device ke MikroTik. Detail: " . $e->getMessage());
            }

            // 2. Sync PRTG
            $prtgUrl = $adminUser->prtg_url ?: env('PRTG_URL');
            $prtgUser = $adminUser->prtg_username ?: env('PRTG_USER');
            $prtgPass = $adminUser->prtg_password ?: env('PRTG_PASSHASH');
            $prtgGroupId = env('PRTG_GROUP_ID', 40); // default group ID 40

            if ($prtgUrl && $prtgUser && $prtgPass) {
                try {
                    // Endpoint PRTG adddevice2.htm
                    \Illuminate\Support\Facades\Http::get($prtgUrl . '/api/adddevice2.htm', [
                        'name_' => $request->name,
                        'host_' => $request->ip,
                        'id' => $prtgGroupId,
                        'ipversion' => 0, // IPv4
                        'discoverytype' => 0, // Auto-discovery disabled
                        'username' => $prtgUser,
                        'passhash' => $prtgPass,
                    ]);
                    \Illuminate\Support\Facades\Log::info("PRTG: Berhasil mendaftarkan device '{$request->name}' ({$request->ip})");
                } catch (\Exception $e) {
                    // Log error tanpa menghentikan kelancaran pendaftaran customer utama
                    \Illuminate\Support\Facades\Log::error("PRTG API Error: Gagal mendaftarkan device ke PRTG. Detail: " . $e->getMessage());
                }
            }
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil ditambahkan!');
    }

    // =========================
    // SINKRONISASI / IMPOR DARI PRTG
    // =========================
    public function importFromPrtg()
    {
        $user      = auth()->user();
        $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;
        $adminId   = $this->resolveAdminId();

        $prtgUrl = $adminUser->prtg_url ?: env('PRTG_URL');
        $prtgUser = $adminUser->prtg_username ?: env('PRTG_USER');
        $prtgPass = $adminUser->prtg_password ?: env('PRTG_PASSHASH');

        if (!$prtgUrl || !$prtgUser || !$prtgPass) {
            return redirect()->route('customers.index')
                ->with('error', 'Kredensial atau URL PRTG belum dikonfigurasi secara lengkap di Pengaturan sistem.');
        }

        try {
            // Tarik daftar devices dari PRTG API
            $response = \Illuminate\Support\Facades\Http::get($prtgUrl . '/api/table.json', [
                'content' => 'devices',
                'output' => 'json',
                'columns' => 'objid,device,host',
                'username' => $prtgUser,
                'passhash' => $prtgPass,
            ]);

            if (!$response->successful()) {
                return redirect()->route('customers.index')
                    ->with('error', 'Gagal terhubung dengan server PRTG. Harap periksa URL, kredensial, atau status server PRTG Anda.');
            }

            $data = $response->json();
            $devices = $data['devices'] ?? [];

            if (empty($devices)) {
                return redirect()->route('customers.index')
                    ->with('error', 'Tidak ditemukan perangkat (devices) termonitor yang aktif di server PRTG.');
            }

            // Ambil paket default sebagai paket dasar pelanggan yang diimpor
            $defaultPackage = Package::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->first()
                            ?? Package::first();
            if (!$defaultPackage) {
                return redirect()->route('customers.index')
                    ->with('error', 'Harap daftarkan minimal satu Layanan Paket terlebih dahulu di panel Layanan Paket sebelum mengimpor.');
            }

            $importedCount = 0;
            $skippedCount = 0;

            foreach ($devices as $dev) {
                $deviceName = $dev['device'] ?? null;
                $deviceIp = $dev['host'] ?? null;
                $objId = $dev['objid'] ?? null;

                if (!$deviceName || !$deviceIp) {
                    continue;
                }

                // Ambil ID pelanggan dari 4 digit pertama pada nama pelanggan prtg
                // Jika tidak ada 4 angka di depan, maka anggap bukan pelanggan (abaikan OLT, Core router, dll)
                if (preg_match('/^(\d{4})[-_\s\.]*(.*)$/', $deviceName, $matches)) {
                    $customerCode = $matches[1];
                    $customerName = trim($matches[2]) ?: $deviceName;
                } else {
                    // Bukan format pelanggan, abaikan!
                    continue;
                }

                // Periksa duplikasi customer_code atau IP di database (dalam scope admin yang sama)
                $exists = Customer::where(function ($q) use ($customerCode, $deviceIp) {
                        $q->where('customer_code', $customerCode)->orWhere('ip', $deviceIp);
                    })
                    ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                if ($adminUser && $adminUser->role === 'admin') {
                    $customerCount = Customer::where('admin_id', $adminId)->count();
                    if ($customerCount >= $adminUser->customer_limit) {
                        return redirect()->route('customers.index')
                            ->with('success', "Sinkronisasi berhasil sebagian! Sukses mengimpor {$importedCount} pelanggan baru dari PRTG. Sisa diabaikan karena batas jumlah pelanggan ({$adminUser->customer_limit}) telah tercapai.");
                    }
                }

                // Daftarkan pelanggan baru dengan data yang diekstrak secara bersih
                Customer::create([
                    'admin_id'      => $adminId,
                    'customer_code' => $customerCode,
                    'name'          => $customerName,
                    'phone'         => '-',
                    'ip'            => $deviceIp,
                    'address'       => null,
                    'package_id'    => $defaultPackage->id,
                ]);

                $importedCount++;
            }

            return redirect()->route('customers.index')
                ->with('success', "Sinkronisasi berhasil! Sukses mengimpor {$importedCount} pelanggan baru dari PRTG (Diabaikan: {$skippedCount} karena nama/IP sudah terdaftar).");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PRTG Import Error: " . $e->getMessage());
            return redirect()->route('customers.index')
                ->with('error', 'Gagal memproses sinkronisasi PRTG: ' . $e->getMessage());
        }
    }

    // =========================
    // EDIT
    // =========================
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $adminId  = $this->resolveAdminId();
        $packages = Package::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->get();

        return view('customers.edit', compact('customer', 'packages'));
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'customer_code' => 'required|unique:customers,customer_code,' . $id,
            'name' => 'required',
            'ip' => 'nullable|string|max:255',
            'package_id' => 'required'
        ]);

        $customer->update([
            'customer_code' => $request->customer_code,
            'name' => $request->name,
            'phone' => $request->phone,
            'ip' => $request->ip,
            'address' => $request->address,
            'package_id' => $request->package_id
        ]);

        if ($customer->ip) {
            $user = auth()->user();
            $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;

            try {
                $mikrotikService = new \App\Services\MikrotikService();
                $mikrotikService->ensureCustomerInAddressList($adminUser, $customer);
                \Illuminate\Support\Facades\Log::info("MikroTik: Berhasil sinkronisasi IP '{$customer->ip}' untuk pelanggan '{$customer->name}' di firewall address-list pasca update");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("MikroTik Sync Update Error: Gagal memperbarui device ke MikroTik. Detail: " . $e->getMessage());
            }
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil diupdate!');
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        // Hapus dari MikroTik address-list
        if ($customer->ip) {
            $user = auth()->user();
            $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;

            try {
                $mikrotikService = new \App\Services\MikrotikService();
                $mikrotikService->removeCustomerFromAddressList($adminUser, $customer->customer_code, $customer->ip);
                \Illuminate\Support\Facades\Log::info("MikroTik: Berhasil menghapus pelanggan '{$customer->name}' ({$customer->ip}) dari address-list");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("MikroTik Delete Error: Gagal menghapus dari address-list. Detail: " . $e->getMessage());
            }
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil dihapus!');
    }

    // =========================
    // 🔥 HISTORY (FIX FINAL)
    // =========================
    public function history($id)
    {
        $customer = Customer::findOrFail($id);

        // ✅ RIWAYAT INVOICE (SUDAH SELESAI)
        $invoices = Invoice::where('customer_id', $id)
            ->where('status', 'paid') // tetap pakai ini untuk filter riwayat
            ->latest()
            ->get()
            ->map(function ($inv) {
                // 🔥 LOGIC UTAMA
                $inv->is_lunas = $inv->paid_amount >= $inv->amount;
                $inv->sisa_tagihan = $inv->amount - $inv->paid_amount;
                return $inv;
            });

        // ✅ RIWAYAT TICKET
        $tickets = Ticket::where('customer_id', $id)
            ->whereNotNull('archived_at')
            ->latest()
            ->get();

        return view('customers.history', compact('customer', 'invoices', 'tickets'));
    }

    /**
     * Pause or resume customer device in PRTG
     *
     * @param \App\Models\User $adminUser
     * @param string $customerCode
     * @param string|null $ipAddress
     * @param bool $active (true = resume, false = pause)
     * @return bool
     */
    protected function setPrtgDeviceStatus($adminUser, $customerCode, $ipAddress, $active): bool
    {
        $prtgUrl = $adminUser->prtg_url ?: env('PRTG_URL');
        $prtgUser = $adminUser->prtg_username ?: env('PRTG_USER');
        $prtgPass = $adminUser->prtg_password ?: env('PRTG_PASSHASH');

        if (!$prtgUrl || !$prtgUser || !$prtgPass) {
            return false;
        }

        try {
            // Langkah 1: Cari objid device berdasarkan nama device (mengandung kode pelanggan) atau host IP
            $response = \Illuminate\Support\Facades\Http::get($prtgUrl . '/api/table.json', [
                'content' => 'devices',
                'output' => 'json',
                'columns' => 'objid,device,host',
                'username' => $prtgUser,
                'passhash' => $prtgPass,
            ]);

            if (!$response->successful()) {
                return false;
            }

            $devices = $response->json()['devices'] ?? [];
            $targetObjid = null;

            foreach ($devices as $dev) {
                $deviceName = $dev['device'] ?? '';
                $host = $dev['host'] ?? '';
                $objid = $dev['objid'] ?? null;

                // Cocokkan berdasarkan IP pelanggan, atau nama device yang diawali dengan kode pelanggan
                if ((!empty($ipAddress) && $host === $ipAddress) || 
                    preg_match('/^' . preg_quote($customerCode, '/') . '([-_\s\.]|$)/', $deviceName)) {
                    $targetObjid = $objid;
                    break;
                }
            }

            // Fallback: Jika tidak ditemukan di devices, cari di sensors
            if (!$targetObjid) {
                $responseSensors = \Illuminate\Support\Facades\Http::get($prtgUrl . '/api/table.json', [
                    'content' => 'sensors',
                    'output' => 'json',
                    'columns' => 'objid,device,sensor',
                    'username' => $prtgUser,
                    'passhash' => $prtgPass,
                ]);

                if ($responseSensors->successful()) {
                    $sensors = $responseSensors->json()['sensors'] ?? [];
                    foreach ($sensors as $sens) {
                        $deviceName = $sens['device'] ?? '';
                        $objid = $sens['objid'] ?? null;
                        if (preg_match('/^' . preg_quote($customerCode, '/') . '([-_\s\.]|$)/', $deviceName)) {
                            $targetObjid = $objid;
                            break;
                        }
                    }
                }
            }

            if (!$targetObjid) {
                return false;
            }

            // Langkah 2: Lakukan Pause atau Resume
            if ($active) {
                // Resume (Unpause)
                $actResponse = \Illuminate\Support\Facades\Http::get($prtgUrl . '/api/pause.htm', [
                    'id' => $targetObjid,
                    'action' => 'resume',
                    'username' => $prtgUser,
                    'passhash' => $prtgPass,
                ]);
            } else {
                // Pause dengan pesan "di matikan admin"
                $actResponse = \Illuminate\Support\Facades\Http::get($prtgUrl . '/api/pause.htm', [
                    'id' => $targetObjid,
                    'action' => 'pause',
                    'pausemsg' => 'di matikan admin',
                    'username' => $prtgUser,
                    'passhash' => $prtgPass,
                ]);
            }

            return $actResponse->successful();

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PRTG Pause/Resume error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle customer internet status and sync with MikroTik & PRTG
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        
        $newStatus = !$customer->is_active;
        $customer->is_active = $newStatus;
        $customer->save();

        $user = auth()->user();
        $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;

        $mikrotikSynced = false;
        $prtgSynced = false;

        // 1. Sync MikroTik
        try {
            $mikrotikService = new \App\Services\MikrotikService();
            $mikrotikSynced = $mikrotikService->setCustomerNetworkStatus(
                $adminUser, 
                $customer->customer_code, 
                $customer->ip, 
                $newStatus
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("MikroTik Sync Toggle Error: " . $e->getMessage());
        }

        // 2. Sync PRTG
        try {
            $prtgSynced = $this->setPrtgDeviceStatus(
                $adminUser, 
                $customer->customer_code, 
                $customer->ip, 
                $newStatus
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PRTG Sync Toggle Error: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'is_active' => $newStatus,
            'mikrotik_synced' => $mikrotikSynced,
            'prtg_synced' => $prtgSynced,
            'message' => $newStatus ? 'Koneksi internet diaktifkan!' : 'Koneksi internet dinonaktifkan!'
        ]);
    }

    /**
     * Sinkronisasi MikroTik → Database:
     * Ambil semua entri address-list dari MikroTik yang tidak di-disable,
     * lalu simpan/update ke database pelanggan.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncAllToMikrotik()
    {
        $user      = auth()->user();
        $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;
        $adminId   = $this->resolveAdminId();

        try {
            $mikrotikService = new \App\Services\MikrotikService();
            if (!$mikrotikService->connect($adminUser)) {
                return redirect()->route('customers.index')
                    ->with('error', "Gagal menyinkronkan: Tidak dapat terhubung ke MikroTik. Harap periksa kredensial MikroTik Anda.");
            }

            // Ambil SEMUA entri address-list dari MikroTik
            $addressLists = $mikrotikService->getAllAddressListEntries();
            $mikrotikService->disconnect();

            if (empty($addressLists)) {
                return redirect()->route('customers.index')
                    ->with('success', 'Sinkronisasi Selesai: Tidak ada data di address-list MikroTik.');
            }

            // Ambil paket default untuk pelanggan baru (dalam scope admin)
            $defaultPackage = Package::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->first()
                            ?? Package::first();
            if (!$defaultPackage) {
                return redirect()->route('customers.index')
                    ->with('error', 'Harap daftarkan minimal satu Layanan Paket terlebih dahulu sebelum sinkronisasi.');
            }

            // Ambil semua paket untuk mencocokkan list name dengan paket di database
            $packages = Package::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->get();

            $importedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($addressLists as $entry) {
                $ipAddress = $entry['address'] ?? '';
                $comment = $entry['comment'] ?? '';
                $listName = $entry['list'] ?? '';
                $disabled = ($entry['disabled'] ?? 'false') === 'true';

                if (empty($ipAddress)) {
                    continue;
                }

                // Ekstrak kode pelanggan dan nama dari comment (format: "6666 - rokim")
                $customerCode = null;
                $customerName = null;

                if (preg_match('/^(\d{4})[-_\s\.]+(.+)$/', $comment, $matches)) {
                    $customerCode = $matches[1];
                    $customerName = trim($matches[2]);
                } elseif (preg_match('/^(\d{4})/', $comment, $matches)) {
                    $customerCode = $matches[1];
                    $customerName = trim(preg_replace('/^\d{4}[-_\s\.]*/', '', $comment)) ?: 'Pelanggan ' . $matches[1];
                } elseif (preg_match('/^(\d{4})/', $listName, $matches)) {
                    $customerCode = $matches[1];
                    $customerName = trim(preg_replace('/^\d{4}[-_\s\.]*/', '', $listName)) ?: 'Pelanggan ' . $matches[1];
                }

                // Jika tidak ada kode pelanggan yang valid, lewati (bukan data pelanggan)
                if (!$customerCode) {
                    $skippedCount++;
                    continue;
                }

                // Cek apakah pelanggan sudah ada di database (dalam scope admin)
                $existingCustomer = Customer::where(function ($q) use ($customerCode, $ipAddress) {
                        $q->where('customer_code', $customerCode)->orWhere('ip', $ipAddress);
                    })
                    ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                    ->first();

                if ($existingCustomer) {
                    // Update status aktif, nama, IP, dan paket jika berubah
                    $changed = false;

                    if ($customerName && $existingCustomer->name !== $customerName) {
                        $existingCustomer->name = $customerName;
                        $changed = true;
                    }

                    if ($existingCustomer->ip !== $ipAddress) {
                        $existingCustomer->ip = $ipAddress;
                        $changed = true;
                    }

                    $newIsActive = !$disabled;
                    if ((bool)$existingCustomer->is_active !== $newIsActive) {
                        $existingCustomer->is_active = $newIsActive;
                        $changed = true;
                    }

                    // Cocokkan list name dengan paket di database
                    $matchedPackage = $packages->first(function ($pkg) use ($listName) {
                        return strtolower($pkg->name) === strtolower($listName);
                    });

                    if ($matchedPackage && $existingCustomer->package_id !== $matchedPackage->id) {
                        $existingCustomer->package_id = $matchedPackage->id;
                        $changed = true;
                    }

                    if ($changed) {
                        $existingCustomer->save();
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    if ($adminUser && $adminUser->role === 'admin') {
                        $customerCount = Customer::where('admin_id', $adminId)->count();
                        if ($customerCount >= $adminUser->customer_limit) {
                            return redirect()->route('customers.index')
                                ->with('success', "Sinkronisasi MikroTik → Database selesai sebagian! Ditambahkan: {$importedCount} pelanggan baru. Sisa diabaikan karena batas jumlah pelanggan ({$adminUser->customer_limit}) telah tercapai.");
                        }
                    }

                    // Cari paket yang cocok dengan list name Mikrotik
                    $matchedPackage = $packages->first(function ($pkg) use ($listName) {
                        return strtolower($pkg->name) === strtolower($listName);
                    });

                    // Buat pelanggan baru di database
                    Customer::create([
                        'admin_id'      => $adminId,
                        'customer_code' => $customerCode,
                        'name'          => $customerName,
                        'phone'         => '-',
                        'ip'            => $ipAddress,
                        'address'       => null,
                        'package_id'    => $matchedPackage ? $matchedPackage->id : $defaultPackage->id,
                        'is_active'     => !$disabled,
                    ]);
                    $importedCount++;
                }
            }

            return redirect()->route('customers.index')
                ->with('success', "Sinkronisasi MikroTik → Database Berhasil! " .
                    "Ditambahkan: {$importedCount} pelanggan baru. " .
                    "Diperbarui: {$updatedCount} pelanggan. " .
                    "Dilewati: {$skippedCount} (sudah sama/bukan pelanggan).");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("MikroTik Sync Error: " . $e->getMessage());
            return redirect()->route('customers.index')
                ->with('error', 'Gagal memproses sinkronisasi MikroTik → Database: ' . $e->getMessage());
        }
    }

    /**
     * Sinkronisasi Database → PRTG:
     * Ambil semua pelanggan dari database yang memiliki IP,
     * lalu daftarkan ke PRTG jika belum terdaftar.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncAllToPrtg()
    {
        $user      = auth()->user();
        $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;
        $adminId   = $this->resolveAdminId();

        $prtgUrl = $adminUser->prtg_url ?: env('PRTG_URL');
        $prtgUser = $adminUser->prtg_username ?: env('PRTG_USER');
        $prtgPass = $adminUser->prtg_password ?: env('PRTG_PASSHASH');

        if (!$prtgUrl || !$prtgUser || !$prtgPass) {
            return redirect()->route('customers.index')
                ->with('error', 'Kredensial atau URL PRTG belum dikonfigurasi. Harap periksa pengaturan PRTG Anda.');
        }

        try {
            // Ambil semua pelanggan dari database yang memiliki IP dan aktif (dalam scope admin)
            $customers = Customer::whereNotNull('ip')->where('ip', '!=', '')->where('is_active', true)
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->get();

            if ($customers->isEmpty()) {
                return redirect()->route('customers.index')
                    ->with('success', 'Sinkronisasi Selesai: Tidak ada pelanggan aktif dengan IP yang perlu didaftarkan ke PRTG.');
            }

            $registeredCount = 0;
            $resumedCount = 0;
            $skippedCount = 0;

            foreach ($customers as $customer) {
                // Cek apakah sudah ada di PRTG
                $prtgObjid = $this->getPrtgDeviceObjid($adminUser, $customer->customer_code, $customer->ip);

                if (!$prtgObjid) {
                    // Belum ada di PRTG → daftarkan
                    $deviceName = $customer->customer_code . ' - ' . $customer->name;
                    $registered = $this->registerDeviceToPrtg($adminUser, $deviceName, $customer->ip);
                    if ($registered) {
                        $registeredCount++;
                    }
                } else {
                    // Sudah ada di PRTG → pastikan di-resume (karena filter is_active = true)
                    $resumed = $this->resumePrtgDevice($adminUser, $prtgObjid);
                    if ($resumed) {
                        $resumedCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }

            return redirect()->route('customers.index')
                ->with('success', "Sinkronisasi Database → PRTG Berhasil! " .
                    "Didaftarkan: {$registeredCount} pelanggan baru ke PRTG. " .
                    "Diaktifkan kembali: {$resumedCount} perangkat. " .
                    "Dilewati: {$skippedCount} (sudah aktif di PRTG).");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PRTG Sync Error: " . $e->getMessage());
            return redirect()->route('customers.index')
                ->with('error', 'Gagal memproses sinkronisasi Database → PRTG: ' . $e->getMessage());
        }
    }

    /**
     * Get PRTG device objid by customer code or IP address
     */
    protected function getPrtgDeviceObjid($adminUser, $customerCode, $ipAddress): ?int
    {
        $prtgUrl = $adminUser->prtg_url ?: env('PRTG_URL');
        $prtgUser = $adminUser->prtg_username ?: env('PRTG_USER');
        $prtgPass = $adminUser->prtg_password ?: env('PRTG_PASSHASH');

        if (!$prtgUrl || !$prtgUser || !$prtgPass) {
            return null;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::get($prtgUrl . '/api/table.json', [
                'content' => 'devices',
                'output' => 'json',
                'columns' => 'objid,device,host',
                'username' => $prtgUser,
                'passhash' => $prtgPass,
            ]);

            if ($response->successful()) {
                $devices = $response->json()['devices'] ?? [];
                foreach ($devices as $dev) {
                    $deviceName = $dev['device'] ?? '';
                    $host = $dev['host'] ?? '';
                    $objid = $dev['objid'] ?? null;

                    if ((!empty($ipAddress) && $host === $ipAddress) || 
                        preg_match('/^' . preg_quote($customerCode, '/') . '([-_\s\.]|$)/', $deviceName)) {
                        return (int)$objid;
                    }
                }
            }

            // Fallback to check sensors
            $responseSensors = \Illuminate\Support\Facades\Http::get($prtgUrl . '/api/table.json', [
                'content' => 'sensors',
                'output' => 'json',
                'columns' => 'objid,device,sensor',
                'username' => $prtgUser,
                'passhash' => $prtgPass,
            ]);

            if ($responseSensors->successful()) {
                $sensors = $responseSensors->json()['sensors'] ?? [];
                foreach ($sensors as $sens) {
                    $deviceName = $sens['device'] ?? '';
                    $objid = $sens['objid'] ?? null;
                    if (preg_match('/^' . preg_quote($customerCode, '/') . '([-_\s\.]|$)/', $deviceName)) {
                        return (int)$objid;
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PRTG Check error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Register a new device to PRTG
     */
    protected function registerDeviceToPrtg($adminUser, $name, $ipAddress): bool
    {
        $prtgUrl = $adminUser->prtg_url ?: env('PRTG_URL');
        $prtgUser = $adminUser->prtg_username ?: env('PRTG_USER');
        $prtgPass = $adminUser->prtg_password ?: env('PRTG_PASSHASH');
        $prtgGroupId = env('PRTG_GROUP_ID', 40);

        if (!$prtgUrl || !$prtgUser || !$prtgPass) {
            return false;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::get($prtgUrl . '/api/adddevice2.htm', [
                'name_' => $name,
                'host_' => $ipAddress,
                'id' => $prtgGroupId,
                'ipversion' => 0, // IPv4
                'discoverytype' => 0, // Auto-discovery disabled
                'username' => $prtgUser,
                'passhash' => $prtgPass,
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PRTG Register error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resume a paused device in PRTG
     */
    protected function resumePrtgDevice($adminUser, $objid): bool
    {
        $prtgUrl = $adminUser->prtg_url ?: env('PRTG_URL');
        $prtgUser = $adminUser->prtg_username ?: env('PRTG_USER');
        $prtgPass = $adminUser->prtg_password ?: env('PRTG_PASSHASH');

        if (!$prtgUrl || !$prtgUser || !$prtgPass) {
            return false;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::get($prtgUrl . '/api/pause.htm', [
                'id' => $objid,
                'action' => 'resume',
                'username' => $prtgUser,
                'passhash' => $prtgPass,
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PRTG Resume error: " . $e->getMessage());
            return false;
        }
    }
}