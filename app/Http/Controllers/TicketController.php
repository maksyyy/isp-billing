<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\AdminScoped;

class TicketController extends Controller
{
    use AdminScoped;

    // =========================
    // LIST TICKET
    // =========================
    public function index(Request $request)
    {
        $user    = auth()->user();
        $search  = $request->search;
        $adminId = $this->resolveAdminId();

        $query = Ticket::with(['customer', 'teknisi'])->whereNull('archived_at');

        // Teknisi hanya melihat tiket yang ditugaskan kepadanya
        if ($user->role == 'teknisi') {
            $query->where('assigned_to', $user->id);
        }

        // Scope by admin_id
        if ($adminId !== null) {
            $query->where('admin_id', $adminId);
        }

        $tickets = $query->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cust) use ($search) {
                        $cust->where('name', 'like', "%{$search}%");
                    });
            });
        })->latest()->paginate(15)->withQueryString();

        return view('tickets.index', compact('tickets'));
    }

    // =========================
    // FORM CREATE
    // =========================
    public function create()
    {
        $adminId   = $this->resolveAdminId();
        $customers = Customer::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->get();
        $teknisi   = User::where('role', 'teknisi')
            ->when($adminId !== null, fn ($q) => $q->where('parent_admin_id', $adminId))
            ->get();

        return view('tickets.create', compact('customers', 'teknisi'));
    }

    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'tanggal'      => 'required|date',
            'customer_id'  => 'required',
            'assigned_to'  => 'required',
            'problem'      => 'required|string|max:500',
            'foto_masalah' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $adminId = $this->resolveAdminId();

        $fotoMasalahPath = null;
        if ($request->hasFile('foto_masalah')) {
            $fotoMasalahPath = $request->file('foto_masalah')->store('foto_masalah', 'public');
        }

        $ticket = Ticket::create([
            'admin_id'     => $adminId,
            'title'        => $request->title,
            'tanggal'      => $request->tanggal,
            'customer_id'  => $request->customer_id,
            'description'  => $request->problem,
            'foto_masalah' => $fotoMasalahPath,
            'assigned_to'  => $request->assigned_to,
            'status'       => 'open'
        ]);

        // Kirim Notifikasi Telegram ke Teknisi jika memiliki telegram_chat_id
        try {
            $teknisiUser = User::find($request->assigned_to);
            $adminUser = $adminId ? User::find($adminId) : null;
            
            // Ambil token bot dari admin tenant, jika kosong gunakan default config
            $botToken = ($adminUser && $adminUser->telegram_bot_token) ? $adminUser->telegram_bot_token : config('services.telegram.bot_token');

            if ($teknisiUser && $teknisiUser->telegram_chat_id && $botToken) {
                $customer = Customer::find($request->customer_id);
                $customerName = $customer ? $customer->name : '-';
                $customerAddress = $customer ? ($customer->address ?: '-') : '-';
                $ticketUrl = url('/tickets');

                $messageText = "🔔 *TIKET ADUAN BARU*\n\n"
                    . "🆔 *ID Tiket:* #{$ticket->id}\n"
                    . "📌 *Judul:* {$request->title}\n"
                    . "👤 *Pelanggan:* {$customerName}\n"
                    . "📍 *Alamat:* {$customerAddress}\n"
                    . "📝 *Masalah:* {$request->problem}\n"
                    . "📅 *Tanggal:* " . \Carbon\Carbon::parse($request->tanggal)->format('d-m-Y') . "\n\n"
                    . "🔗 *Direct Link:* [Buka Halaman Tiket]({$ticketUrl})\n\n"
                    . "Silakan segera ditindaklanjuti. Terima kasih!";

                \Illuminate\Support\Facades\Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $teknisiUser->telegram_chat_id,
                    'text' => $messageText,
                    'parse_mode' => 'Markdown',
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim notifikasi Telegram: " . $e->getMessage());
        }

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket berhasil dibuat');
    }

    // =========================
    // EDIT
    // =========================
    public function edit($id)
    {
        $ticket    = Ticket::findOrFail($id);
        $adminId   = $this->resolveAdminId();
        $customers = Customer::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->get();
        $teknisi   = User::where('role', 'teknisi')
            ->when($adminId !== null, fn ($q) => $q->where('parent_admin_id', $adminId))
            ->get();

        return view('tickets.edit', compact('ticket', 'customers', 'teknisi'));
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'title'        => 'required|string|max:255',
            'tanggal'      => 'required|date',
            'customer_id'  => 'required',
            'assigned_to'  => 'required',
            'problem'      => 'required|string|max:500',
            'foto_masalah' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $updateData = [
            'title'       => $request->title,
            'tanggal'     => $request->tanggal,
            'customer_id' => $request->customer_id,
            'description' => $request->problem,
            'assigned_to' => $request->assigned_to,
        ];

        if ($request->hasFile('foto_masalah')) {
            // Hapus foto lama jika ada
            if ($ticket->foto_masalah && \Illuminate\Support\Facades\Storage::disk('public')->exists($ticket->foto_masalah)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($ticket->foto_masalah);
            }
            $updateData['foto_masalah'] = $request->file('foto_masalah')->store('foto_masalah', 'public');
        }

        $ticket->update($updateData);

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket berhasil diupdate');
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        Ticket::findOrFail($id)->delete();

        return back()->with('success', 'Ticket berhasil dihapus');
    }

    // =========================
    // SELESAI (UPLOAD + STATUS)
    // =========================
    public function selesai(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'bukti' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        // upload bukti
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti')->store('bukti', 'public');
            $ticket->bukti = $file;
        }

        // update status + waktu selesai
        $ticket->status         = 'done';
        $ticket->tanggal_selesai = now()->format('Y-m-d H:i:s');

        $ticket->save();

        return back()->with('success', 'Ticket selesai');
    }
}