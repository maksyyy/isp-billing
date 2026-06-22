<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\AdminScoped;

class InvoiceController extends Controller
{
    use AdminScoped;

    // =========================
    // INDEX
    // =========================
    public function index(Request $request)
    {
        $search  = $request->search;
        $adminId = $this->resolveAdminId();

        $invoices = Invoice::with('customer')
            ->where('status', '!=', 'paid')
            ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
            ->when($search, function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    // =========================
    // GENERATE (SEMUA PELANGGAN)
    // =========================
    public function generate(Request $request)
    {
        $request->validate([
            'due_date' => 'required|date'
        ]);

        $adminId   = $this->resolveAdminId();
        $customers = Customer::with('package')
            ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
            ->get();

        foreach ($customers as $customer) {
            Invoice::create([
                'admin_id'    => $adminId,
                'customer_id' => $customer->id,
                'amount'      => $customer->package->price,
                'due_date'    => $request->due_date,
                'status'      => 'unpaid',
                'paid_amount' => 0
            ]);
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice berhasil dibuat');
    }

    public function generateForm()
    {
        $adminId   = $this->resolveAdminId();
        $customers = Customer::when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->get();
        return view('invoices.generate', compact('customers'));
    }

    public function generateMultiple(Request $request)
    {
        $request->validate([
            'due_date' => 'required|date'
        ]);

        $adminId   = $this->resolveAdminId();

        $customers = $request->generate_all
            ? Customer::with('package')->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))->get()
            : Customer::with('package')
                ->whereIn('id', $request->customer_ids)
                ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
                ->get();

        foreach ($customers as $customer) {
            Invoice::create([
                'admin_id'    => $adminId,
                'customer_id' => $customer->id,
                'amount'      => $customer->package->price,
                'due_date'    => $request->due_date,
                'status'      => 'unpaid',
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

        DB::transaction(function () use ($request, $invoice) {
            Payment::create([
                'invoice_id'   => $invoice->id,
                'customer_id'  => $invoice->customer_id,
                'amount'       => $request->amount,
                'payment_date' => now()
            ]);

            $invoice->paid_amount += $request->amount;

            if ($invoice->paid_amount >= $invoice->amount) {
                $invoice->status = 'paid';
            }

            $invoice->save();
        });

        return back()->with('success', 'Pembayaran berhasil');
    }

    // =========================
    // SELESAI (MASUK HISTORY)
    // =========================
    public function selesai($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->status = 'paid';
        $invoice->save();

        return back()->with('success', 'Masuk ke riwayat pelanggan');
    }

    // =========================
    // PRINT SELECTED
    // =========================
    public function printSelected(Request $request)
    {
        $adminId  = $this->resolveAdminId();
        $invoices = Invoice::with('customer')
            ->whereIn('id', $request->invoice_ids)
            ->where('status', '!=', 'paid')
            ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
            ->get();

        return view('invoices.print', compact('invoices'));
    }

    // =========================
    // PRINT ALL
    // =========================
    public function printAll()
    {
        $adminId  = $this->resolveAdminId();
        $invoices = Invoice::with('customer')
            ->where('status', '!=', 'paid')
            ->when($adminId !== null, fn ($q) => $q->where('admin_id', $adminId))
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

    // =========================
    // HAPUS SELECTED (BULK DELETE)
    // =========================
    public function destroySelected(Request $request)
    {
        $request->validate([
            'invoice_ids'   => 'required|array',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        Invoice::whereIn('id', $request->invoice_ids)->delete();

        return back()->with('success', 'Invoice terpilih berhasil dihapus');
    }
}
