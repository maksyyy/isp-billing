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
    public function index()
    {
        $user = auth()->user();

        if ($user->role == 'teknisi') {
            $tickets = Ticket::where('assigned_to', $user->id)
                ->whereNull('archived_at') // 🔥 hanya ticket aktif
                ->get();
        } else {
            $tickets = Ticket::whereNull('archived_at') // 🔥 sembunyikan yg sudah diarsip
                ->get();
        }

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
            'assigned_to' => 'required'
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
            'assigned_to' => 'required'
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