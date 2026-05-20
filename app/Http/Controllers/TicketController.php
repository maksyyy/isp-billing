<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // =========================
    // LIST TICKET
    // =========================
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->search;

        $query = Ticket::whereNull('archived_at');

        if ($user->role == 'teknisi') {
            $query->where('assigned_to', $user->id);
        }

        $tickets = $query->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cust) use ($search) {
                        $cust->where('name', 'like', "%{$search}%");
                    });
            });
        })->get();

        return view('tickets.index', compact('tickets'));
    }

    // =========================
    // FORM CREATE
    // =========================
    public function create()
    {
        $customers = Customer::all();
        $teknisi = User::where('role', 'teknisi')->get();

        return view('tickets.create', compact('customers', 'teknisi'));
    }

    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'required',
            'assigned_to' => 'required',
            'problem' => 'required|string|max:500' // 🔥 Pastikan problem tidak kosong dan wajar
        ]);

        Ticket::create([
            'title' => 'Gangguan',
            'tanggal' => $request->tanggal,
            'customer_id' => $request->customer_id,
            'description' => $request->problem,
            'assigned_to' => $request->assigned_to,
            'status' => 'open'
        ]);

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket berhasil dibuat');
    }

    // =========================
    // EDIT
    // =========================
    public function edit($id)
    {
        $ticket = Ticket::findOrFail($id);
        $customers = Customer::all();
        $teknisi = User::where('role', 'teknisi')->get();

        return view('tickets.edit', compact('ticket', 'customers', 'teknisi'));
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'required',
            'assigned_to' => 'required',
            'problem' => 'required|string|max:500'
        ]);

        $ticket->update([
            'tanggal' => $request->tanggal,
            'customer_id' => $request->customer_id,
            'description' => $request->problem,
            'assigned_to' => $request->assigned_to,
        ]);

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
            'bukti' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:2048' // 🔥 Keamanan: wajib gambar, maks 2MB
        ]);

        // upload bukti
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti')->store('bukti', 'public');
            $ticket->bukti = $file;
        }

        // update status + waktu selesai
        $ticket->status = 'done';
        $ticket->tanggal_selesai = now(); // 🔥 simpan jam selesai

        $ticket->save();

        return back()->with('success', 'Ticket selesai');
    }
}