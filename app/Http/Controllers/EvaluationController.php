<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Presensi;
use App\Models\BackboneDevice;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\AdminScoped;

class EvaluationController extends Controller
{
    use AdminScoped;

    /**
     * Display the Evaluation and Presentation Dashboard.
     */
    public function index(Request $request)
    {
        $adminId = $this->resolveAdminId();

        // 1. Dynamic Counts
        $counts = [
            'users' => User::count(),
            'packages' => Package::count(),
            'customers' => Customer::count(),
            'invoices' => Invoice::count(),
            'payments' => Payment::count(),
            'tickets' => Ticket::count(),
            'presensis' => Presensi::count(),
            'backbone_devices' => BackboneDevice::count(),
        ];

        // 2. Database Connection Info
        $dbDriver = config('database.default');
        $dbName = config("database.connections.{$dbDriver}.database");
        $dbHost = config("database.connections.{$dbDriver}.host", 'localhost');

        // 3. Cast Audit (Encryption check)
        $userModel = new User();
        $casts = $userModel->getCasts();
        $encryptionAudit = [
            'prtg_password' => isset($casts['prtg_password']) && $casts['prtg_password'] === 'encrypted',
            'mikrotik_password' => isset($casts['mikrotik_password']) && $casts['mikrotik_password'] === 'encrypted',
            'telegram_bot_token' => isset($casts['telegram_bot_token']) && $casts['telegram_bot_token'] === 'encrypted',
            'smtp_password' => isset($casts['smtp_password']) && $casts['smtp_password'] === 'encrypted',
        ];

        // 4. Index Audit (Dynamic checks using Laravel Schema)
        $indexAudit = [];
        $tablesToCheck = ['invoices', 'tickets', 'customers', 'presensis', 'packages', 'backbone_devices'];
        foreach ($tablesToCheck as $table) {
            if (Schema::hasTable($table)) {
                try {
                    // Laravel 11/12 has Schema::getIndexes
                    $indexes = Schema::getIndexes($table);
                    $indexNames = array_map(fn($idx) => $idx['name'] ?? '', $indexes);
                    $indexAudit[$table] = $indexNames;
                } catch (\Exception $e) {
                    $indexAudit[$table] = ['error' => $e->getMessage()];
                }
            }
        }

        // Seed default backbone devices if none exist
        if (BackboneDevice::count() === 0) {
            BackboneDevice::create([
                'admin_id' => $adminId ?? 1,
                'name' => 'Router Core (Gedung Pusat)',
                'ip' => '10.10.10.1',
                'status' => 'up',
            ]);
            BackboneDevice::create([
                'admin_id' => $adminId ?? 1,
                'name' => 'Switch Distribusi (Lt. 2)',
                'ip' => '10.10.20.5',
                'status' => 'up',
            ]);
            // Re-update count in local stats
            $counts['backbone_devices'] = BackboneDevice::count();
        }

        // Seed default package if none exist
        if (Package::count() === 0) {
            Package::create([
                'admin_id' => $adminId ?? 1,
                'name' => 'Home Lite 10M',
                'price' => 150000,
                'speed' => '10 Mbps',
            ]);
            Package::create([
                'admin_id' => $adminId ?? 1,
                'name' => 'Family Pro 50M',
                'price' => 350000,
                'speed' => '50 Mbps',
            ]);
            // Re-update count in local stats
            $counts['packages'] = Package::count();
        }

        // 5. Data Dictionary definition
        $dataDictionary = $this->getDataDictionary();

        // 6. Recent records for CRUD playground
        $packages = Package::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
            ->latest()
            ->limit(10)
            ->get();

        $customers = Customer::with('package')
            ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
            ->latest()
            ->limit(10)
            ->get();

        $backboneDevices = BackboneDevice::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
            ->latest()
            ->limit(5)
            ->get();

        return view('evaluation.index', compact(
            'counts',
            'dbDriver',
            'dbName',
            'dbHost',
            'encryptionAudit',
            'indexAudit',
            'dataDictionary',
            'packages',
            'customers',
            'backboneDevices'
        ));
    }

    /**
     * API: Create a demo package (CRUD: Create)
     */
    public function createTestPackage(Request $request)
    {
        $adminId = $this->resolveAdminId();
        
        $speeds = ['10 Mbps', '20 Mbps', '50 Mbps', '100 Mbps', '150 Mbps'];
        $speed = $speeds[array_rand($speeds)];
        $name = 'Paket Demo ' . rand(100, 999) . ' (' . $speed . ')';
        $price = rand(150, 450) * 1000;

        $package = Package::create([
            'admin_id' => $adminId ?? 1,
            'name' => $name,
            'price' => $price,
            'speed' => $speed,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil ditambahkan secara dinamis!',
            'data' => $package
        ]);
    }

    /**
     * API: Update package price (CRUD: Update)
     */
    public function updateTestPackage(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        $newPrice = rand(150, 450) * 1000;
        
        $package->update([
            'price' => $newPrice
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Harga paket berhasil diperbarui secara dinamis!',
            'data' => $package
        ]);
    }

    /**
     * API: Delete package (CRUD: Delete)
     */
    public function deleteTestPackage($id)
    {
        $package = Package::findOrFail($id);
        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil dihapus secara dinamis!'
        ]);
    }

    /**
     * API: Create a dummy customer
     */
    public function createTestCustomer(Request $request)
    {
        $adminId = $this->resolveAdminId();
        
        // Find a package
        $package = Package::first();
        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan buat paket terlebih dahulu sebelum menambahkan pelanggan.'
            ], 422);
        }

        $names = ['Ahmad Syarif', 'Budi Santoso', 'Citra Dewi', 'Dedy Cahyono', 'Eka Lestari', 'Fahmi Idris', 'Gita Gutawa'];
        $name = $names[array_rand($names)] . ' (Demo ' . rand(10, 99) . ')';
        $code = 'CUST-' . rand(1000, 9999);
        $ip = '192.168.100.' . rand(2, 254);

        $customer = Customer::create([
            'admin_id' => $adminId ?? 1,
            'customer_code' => $code,
            'name' => $name,
            'phone' => '08' . rand(1000000000, 9999999999),
            'ip' => $ip,
            'is_active' => true,
            'address' => 'Jl. Demo Raya No. ' . rand(1, 100),
            'package_id' => $package->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan baru berhasil didaftarkan!',
            'data' => $customer
        ]);
    }

    /**
     * API: Simulate creating an invoice (CRUD)
     */
    public function createTestInvoice(Request $request)
    {
        $adminId = $this->resolveAdminId();

        $customer = Customer::inRandomOrder()->first();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada pelanggan. Silakan buat pelanggan terlebih dahulu.'
            ], 422);
        }

        $amount = $customer->package ? $customer->package->price : 250000;
        
        $invoice = Invoice::create([
            'admin_id' => $adminId ?? $customer->admin_id ?? 1,
            'customer_id' => $customer->id,
            'amount' => $amount,
            'paid_amount' => 0,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'unpaid',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice tagihan berhasil dibuat secara dinamis untuk ' . $customer->name,
            'data' => $invoice
        ]);
    }

    /**
     * API: Simulate pinging a backbone device (Monitoring Check)
     */
    public function simulatePingBackbone($id)
    {
        $device = BackboneDevice::findOrFail($id);
        
        $pingMs = rand(5, 45);
        $status = rand(1, 10) > 1 ? 'up' : 'down'; // 90% chance of up

        $device->update([
            'status' => $status,
            'last_ping_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Koneksi ke {$device->name} berhasil diperiksa!",
            'data' => [
                'name' => $device->name,
                'ip' => $device->ip,
                'status' => $status,
                'ping' => $status === 'up' ? "{$pingMs} ms" : 'RTO (Request Timeout)',
                'time' => now()->format('H:i:s'),
            ]
        ]);
    }

    /**
     * Static helper: Get Database Data Dictionary.
     */
    private function getDataDictionary()
    {
        return [
            'users' => [
                'desc' => 'Menyimpan kredensial otentikasi user, role hierarki, integrasi PRTG, MikroTik, dan verifikasi presensi wajah.',
                'columns' => [
                    'id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Primary Key unik user.'],
                    'name' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Nama lengkap pengguna.'],
                    'email' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Email unik untuk masuk (login).'],
                    'phone' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Nomor HP/Telepon aktif.'],
                    'telegram_chat_id' => ['type' => 'string', 'null' => 'YES', 'desc' => 'ID obrolan Telegram untuk notifikasi bot.'],
                    'password' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Kata sandi yang di-hash (Bcrypt).'],
                    'role' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Hak akses: master, admin, finance, noc, teknisi.'],
                    'customer_limit' => ['type' => 'integer', 'null' => 'YES', 'desc' => 'Maksimum limit pelanggan yang bisa dikelola admin (default: 200).'],
                    'parent_admin_id' => ['type' => 'bigint', 'null' => 'YES', 'desc' => 'ID Admin atasan langsung (untuk sub-karyawan).'],
                    'company_name' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Nama perusahaan / ISP.'],
                    'face_photo' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Path file foto wajah terdaftar untuk verifikasi presensi.'],
                    'timezone' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Zona waktu pengguna.'],
                    'prtg_url' => ['type' => 'string', 'null' => 'YES', 'desc' => 'URL API Endpoint server PRTG.'],
                    'prtg_username' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Username login server PRTG.'],
                    'prtg_password' => ['type' => 'text', 'null' => 'YES', 'desc' => 'Password API PRTG (Terenkripsi AES-256).'],
                    'mikrotik_host' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Alamat IP / Host router MikroTik.'],
                    'mikrotik_username' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Username API router MikroTik.'],
                    'mikrotik_password' => ['type' => 'text', 'null' => 'YES', 'desc' => 'Password API router MikroTik (Terenkripsi AES-256).'],
                    'telegram_bot_token' => ['type' => 'text', 'null' => 'YES', 'desc' => 'Token Telegram Bot pengirim alerts (Terenkripsi AES-256).']
                ]
            ],
            'packages' => [
                'desc' => 'Daftar paket internet (bandwidth) yang ditawarkan kepada pelanggan.',
                'columns' => [
                    'id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Primary Key unik paket.'],
                    'admin_id' => ['type' => 'bigint', 'null' => 'YES', 'desc' => 'Pemilik/Admin pembuat paket (Multi-Tenancy).'],
                    'name' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Nama paket layanan internet.'],
                    'price' => ['type' => 'integer', 'null' => 'NO', 'desc' => 'Harga paket bulanan (Rupiah).'],
                    'speed' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Kecepatan bandwidth (misal: 10 Mbps).']
                ]
            ],
            'customers' => [
                'desc' => 'Profil lengkap pelanggan internet ISP, status isolir router, dan IP address.',
                'columns' => [
                    'id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Primary Key unik pelanggan.'],
                    'admin_id' => ['type' => 'bigint', 'null' => 'YES', 'desc' => 'ID Admin pengelola pelanggan ini (Multi-Tenancy).'],
                    'customer_code' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Kode unik pelanggan (misal: CUST-9021).'],
                    'name' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Nama lengkap pelanggan.'],
                    'phone' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Nomor WhatsApp / HP aktif.'],
                    'ip' => ['type' => 'string', 'null' => 'YES', 'desc' => 'IP statis pelanggan untuk integrasi filter IP MikroTik.'],
                    'is_active' => ['type' => 'boolean', 'null' => 'NO', 'desc' => 'Status koneksi aktif (true) atau terisolir (false).'],
                    'package_id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Foreign Key relasi ke packages.id.']
                ]
            ],
            'invoices' => [
                'desc' => 'Data tagihan bulanan pelanggan yang di-generate otomatis oleh system scheduler.',
                'columns' => [
                    'id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Primary Key unik invoice.'],
                    'admin_id' => ['type' => 'bigint', 'null' => 'YES', 'desc' => 'ID Admin pemilik transaksi tagihan.'],
                    'customer_id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Foreign Key relasi ke customers.id.'],
                    'amount' => ['type' => 'integer', 'null' => 'NO', 'desc' => 'Jumlah total nilai tagihan.'],
                    'paid_amount' => ['type' => 'integer', 'null' => 'NO', 'desc' => 'Jumlah nominal yang telah dibayarkan.'],
                    'due_date' => ['type' => 'date', 'null' => 'NO', 'desc' => 'Batas akhir tanggal jatuh tempo pembayaran.'],
                    'status' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Status: paid (lunas), unpaid (belum bayar), partial (bayar sebagian).']
                ]
            ],
            'payments' => [
                'desc' => 'Histori transaksi pembayaran real-time dari pelanggan untuk pelunasan tagihan invoice.',
                'columns' => [
                    'id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Primary Key transaksi pembayaran.'],
                    'invoice_id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Foreign Key relasi ke invoices.id.'],
                    'customer_id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Foreign Key relasi ke customers.id.'],
                    'amount' => ['type' => 'integer', 'null' => 'NO', 'desc' => 'Jumlah uang tunai yang diserahkan/ditransfer.'],
                    'payment_date' => ['type' => 'date', 'null' => 'NO', 'desc' => 'Tanggal pembayaran diproses.']
                ]
            ],
            'tickets' => [
                'desc' => 'Tiket laporan pengaduan gangguan teknis dari pelanggan beserta penugasan teknisi lapangan.',
                'columns' => [
                    'id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Primary Key laporan tiket.'],
                    'admin_id' => ['type' => 'bigint', 'null' => 'YES', 'desc' => 'ID Admin pemilik tiket.'],
                    'customer_id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Foreign Key relasi ke pelanggan pelapor (customers.id).'],
                    'title' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Subjek kendala teknis (misal: Internet Lambat).'],
                    'description' => ['type' => 'text', 'null' => 'NO', 'desc' => 'Detail kronologi masalah.'],
                    'foto_masalah' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Path foto kendala yang diunggah pelanggan.'],
                    'assigned_to' => ['type' => 'bigint', 'null' => 'YES', 'desc' => 'Foreign Key ke users.id (teknisi yang ditugaskan).'],
                    'status' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Status tiket: open, progress, done.'],
                    'bukti' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Path foto bukti penyelesaian gangguan dari teknisi.'],
                    'tanggal_selesai' => ['type' => 'dateTime', 'null' => 'YES', 'desc' => 'Waktu penyelesaian kendala.']
                ]
            ],
            'presensis' => [
                'desc' => 'Presensi kehadiran karyawan menggunakan pencocokan verifikasi foto wajah.',
                'columns' => [
                    'id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Primary Key absensi.'],
                    'user_id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Foreign Key relasi ke users.id.'],
                    'tanggal' => ['type' => 'date', 'null' => 'NO', 'desc' => 'Tanggal absensi.'],
                    'jam_masuk' => ['type' => 'time', 'null' => 'NO', 'desc' => 'Waktu presensi masuk.'],
                    'jam_keluar' => ['type' => 'time', 'null' => 'YES', 'desc' => 'Waktu presensi keluar.'],
                    'foto_masuk' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Path foto verifikasi wajah saat masuk.'],
                    'foto_keluar' => ['type' => 'string', 'null' => 'YES', 'desc' => 'Path foto verifikasi wajah saat pulang.'],
                    'status' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Status presensi (Hadir, Izin, Sakit, Alpa).'],
                    'lembur' => ['type' => 'integer', 'null' => 'NO', 'desc' => 'Jumlah jam lembur.']
                ]
            ],
            'backbone_devices' => [
                'desc' => 'Monitoring status up/down infrastruktur perangkat backbone jaringan utama.',
                'columns' => [
                    'id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'Primary Key perangkat backbone.'],
                    'admin_id' => ['type' => 'bigint', 'null' => 'NO', 'desc' => 'ID Admin pemilik perangkat.'],
                    'name' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Nama identitas perangkat.'],
                    'ip' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Alamat IP statis perangkat backbone.'],
                    'status' => ['type' => 'string', 'null' => 'NO', 'desc' => 'Status ping terbaru: up, down.'],
                    'last_ping_at' => ['type' => 'timestamp', 'null' => 'YES', 'desc' => 'Waktu pemeriksaan ping terakhir.']
                ]
            ]
        ];
    }
}
