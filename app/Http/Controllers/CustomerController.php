<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Package;

class CustomerController extends Controller
{
    // =========================
    // INDEX + SEARCH 🔥
    // =========================
    public function index(Request $request)
    {
        $search = $request->search;

        $customers = Customer::with('package')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                      ->orWhere('customer_code', 'like', "%$search%")
                      ->orWhere('phone', 'like', "%$search%");
            })
            ->latest()
            ->get();

        return view('customers.index', compact('customers'));
    }

    // =========================
    // CREATE
    // =========================
    public function create()
    {
        $packages = Package::all();
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
            'package_id' => 'required'
        ]);

        Customer::create([
            'customer_code' => $request->customer_code,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'package_id' => $request->package_id
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil ditambahkan!');
    }

    // =========================
    // EDIT
    // =========================
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $packages = Package::all();

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
            'package_id' => 'required'
        ]);

        $customer->update([
            'customer_code' => $request->customer_code,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'package_id' => $request->package_id
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil diupdate!');
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        Customer::findOrFail($id)->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil dihapus!');
    }

    // =========================
    // 🔥 HISTORY (PEMBAYARAN + PERBAIKAN)
    // =========================
    public function history($id)
    {
        $customer = Customer::findOrFail($id);

        // ✅ RIWAYAT PEMBAYARAN
        $invoices = \App\Models\Invoice::where('customer_id', $id)
            ->where('status', 'paid')
            ->latest()
            ->get();

        // 🔥 RIWAYAT PERBAIKAN (TICKET)
        $tickets = \App\Models\Ticket::where('customer_id', $id)
            ->whereNotNull('archived_at') // hanya yang sudah lewat jam 00:00
            ->latest()
            ->get();

        return view('customers.history', compact('customer', 'invoices', 'tickets'));
    }
}