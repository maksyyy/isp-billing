<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'noc']), 403, 'Halaman presensi hanya dapat dibuka oleh Admin dan NOC.');
        
        $adminId = $user->parent_admin_id ?: $user->id;
        $adminUser = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;
        $timezone = $adminUser->timezone ?? 'Asia/Jakarta';

        // Ambil semua karyawan (termasuk admin utama itu sendiri)
        $employees = \App\Models\User::where('parent_admin_id', $adminId)
            ->orWhere('id', $adminId)
            ->orderBy('name')
            ->get();

        // Ambil log kehadiran seluruh karyawan HARI INI (sesuai wilayah waktu) untuk live board
        $todayStr = Carbon::today($timezone)->format('Y-m-d');
        
        $logs = Presensi::with('user')
            ->whereHas('user', function ($q) use ($adminId) {
                $q->where('parent_admin_id', $adminId)->orWhere('id', $adminId);
            })
            ->where('tanggal', $todayStr)
            ->orderBy('jam_masuk', 'desc')
            ->get();

        return view('presensi.index', compact('employees', 'logs'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'noc']), 403, 'Akses ditolak. Fitur presensi hanya untuk Admin dan NOC.');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'photo' => 'nullable|string',
            'is_manual' => 'nullable|boolean'
        ]);

        $targetUser = \App\Models\User::findOrFail($request->user_id);

        // Keaktifan wajah dinonaktifkan atas permintaan pengguna - tidak ada pemeriksaan face_photo

        $adminId = $targetUser->parent_admin_id ?: $targetUser->id;
        $adminUser = $targetUser->parent_admin_id ? \App\Models\User::find($targetUser->parent_admin_id) : $targetUser;
        $timezone = $adminUser->timezone ?? 'Asia/Jakarta';

        $nowTime = Carbon::now($timezone);
        $today = $nowTime->format('Y-m-d');

        // Deteksi secara otomatis jenis aksi (masuk atau keluar) berdasarkan database hari ini
        $existing = Presensi::where('user_id', $targetUser->id)
            ->where('tanggal', $today)
            ->first();

        if (!$existing) {
            $actionType = 'masuk';
        } elseif ($existing && !$existing->jam_keluar) {
            $actionType = 'keluar';
        } else {
            return back()->with('error', $targetUser->name . ' sudah melakukan presensi masuk & keluar untuk hari ini!');
        }
        
        // Dekode Base64 foto (jika ada)
        $savedPath = null;
        if ($request->filled('photo')) {
            $img = $request->photo;
            $img = str_replace('data:image/png;base64,', '', $img);
            $img = str_replace(' ', '+', $img);
            $data = base64_decode($img);
            
            $fileName = $targetUser->id . '_' . $actionType . '_' . time() . '.png';
            $dir = public_path('storage/presensi');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($dir . '/' . $fileName, $data);
            $savedPath = 'presensi/' . $fileName;
        }

        if ($actionType === 'masuk') {
            // Presensi Masuk
            
            // Cek batas terlambat (jam 08:30:00 waktu setempat)
            $limitTime = Carbon::createFromTimeString('08:30:00', $timezone);
            $status = 'Hadir';
            if ($nowTime->format('H:i:s') > $limitTime->format('H:i:s')) {
                $status = 'Terlambat';
            }

            Presensi::create([
                'user_id' => $targetUser->id,
                'tanggal' => $today,
                'jam_masuk' => $nowTime->format('H:i:s'),
                'foto_masuk' => $savedPath,
                'status' => $status
            ]);

            return back()->with('success', 'Presensi MASUK ' . $targetUser->name . ' berhasil! Status: ' . $status . ' (Zona Waktu: ' . ($timezone == 'Asia/Jakarta' ? 'WIB' : ($timezone == 'Asia/Makassar' ? 'WITA' : 'WIT')) . ')');
            
        } else {
            // Presensi Keluar
            $checkoutTime = $nowTime;
            $endTime = Carbon::createFromTimeString('16:00:00', $timezone);
            $lemburMinutes = 0;
            $successMsg = 'Presensi KELUAR ' . $targetUser->name . ' berhasil! Sampai jumpa besok!';

            if ($checkoutTime->greaterThan($endTime)) {
                $lemburMinutes = abs($checkoutTime->diffInMinutes($endTime));
                $hours = floor($lemburMinutes / 60);
                $minutes = $lemburMinutes % 60;
                $durationStr = ($hours > 0 ? $hours . ' jam ' : '') . ($minutes > 0 ? $minutes . ' menit' : '');
                $successMsg .= " (Lembur: {$durationStr} terhitung)";
            }

            $existing->update([
                'jam_keluar' => $checkoutTime->format('H:i:s'),
                'foto_keluar' => $savedPath,
                'lembur' => $lemburMinutes
            ]);

            return back()->with('success', $successMsg . ' (Zona Waktu: ' . ($timezone == 'Asia/Jakarta' ? 'WIB' : ($timezone == 'Asia/Makassar' ? 'WITA' : 'WIT')) . ')');
        }
    }
}
