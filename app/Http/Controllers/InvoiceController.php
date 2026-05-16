<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    // =========================
    // INDEX (YANG TAMPIL DI HALAMAN INVOICE)
    // =========================
    public function index(Request $request)
    {
        $search = $request->search;

        $invoices = Invoice::with('customer')
            ->where('status', '!=', 'paid') // 🔥 hanya yang tampil di halaman
            ->when($search, function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
            })
            ->latest()
            ->get();

        return view('invoices.index', compact('invoices'));
    }

    // =========================
    // GENERATE
    // =========================
    public function generate(Request $request)
    {
        $request->validate([
            'due_date' => 'required|date'
        ]);

        $customers = Customer::with('package')->get();

        foreach ($customers as $customer) {
            Invoice::create([
                'customer_id' => $customer->id,
                'amount' => $customer->package->price,
                'due_date' => $request->due_date,
                'status' => 'unpaid',
                'paid_amount' => 0
            ]);
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice berhasil dibuat');
    }

    public function generateForm()
    {
        $customers = Customer::all();
        return view('invoices.generate', compact('customers'));
    }

    public function generateMultiple(Request $request)
    {
        $request->validate([
            'due_date' => 'required|date'
        ]);

        $customers = $request->generate_all
            ? Customer::with('package')->get()
            : Customer::with('package')->whereIn('id', $request->customer_ids)->get();

        foreach ($customers as $customer) {
            Invoice::create([
                'customer_id' => $customer->id,
                'amount' => $customer->package->price,
                'due_date' => $request->due_date,
                'status' => 'unpaid',
                'paid_amount' => 0
            ]);
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice berhasil dibuat');
    }

    // =========================
    // BAYAR (CICILAN)
    // =========================
    public function pay(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'amount' => $request->amount,
            'payment_date' => now()
        ]);

        $invoice->paid_amount += $request->amount;

        // ✅ hanya auto lunas kalau benar-benar full
        if ($invoice->paid_amount >= $invoice->amount) {
            $invoice->status = 'paid';
        }

        $invoice->save();

        return back()->with('success', 'Pembayaran berhasil');
    }

    // =========================
    // SELESAI (MASUK HISTORY)
    // =========================
    public function selesai($id)
    {
        $invoice = Invoice::findOrFail($id);

        // 🔥 tandai selesai (masuk history)
        $invoice->status = 'paid';

        // ❗ tidak ubah jumlah bayar
        $invoice->save();

        return back()->with('success', 'Masuk ke riwayat pelanggan');
    }

    // =========================
    // PRINT SELECTED
    // =========================
    public function printSelected(Request $request)
    {
        $invoices = Invoice::with('customer')
            ->whereIn('id', $request->invoice_ids)
            ->where('status', '!=', 'paid') // 🔥 hanya yg tampil
            ->get();

        return view('invoices.print', compact('invoices'));
    }

    // =========================
    // PRINT ALL (FIX 🔥)
    // =========================
    public function printAll()
    {
        $invoices = Invoice::with('customer')
            ->where('status', '!=', 'paid') // 🔥 SAMA seperti halaman index
            ->latest()
            ->get();

        return view('invoices.print', compact('invoices'));
    }

    // =========================
    // PRINT SINGLE
    // =========================
    public function print($id)
    {
        $invoices = Invoice::with('customer')
            ->where('id', $id)
            ->get();

        return view('invoices.print', compact('invoices'));
    }

    // =========================
    // HAPUS INVOICE
    // =========================
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return back()->with('success', 'Invoice berhasil dihapus');
    }
}
