<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $packages = Package::when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('speed', 'like', "%{$search}%");
        })->get();

        return view('packages.index', compact('packages'));
    }

    
    public function create()
    {
        return view('packages.create');
    }

    public function edit($id)
{
    $package = Package::findOrFail($id);
    return view('packages.edit', compact('package'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required',
        'price' => 'required|numeric',
        'speed' => 'required'
    ]);

    $package = Package::findOrFail($id);
    $package->update($request->all());

    return redirect()->route('packages.index')
        ->with('success', 'Paket berhasil diupdate');
}

public function destroy($id)
{
    $package = Package::findOrFail($id);
    $package->delete();

    return redirect()->route('packages.index')
        ->with('success', 'Paket berhasil dihapus');
}

    public function store(Request $request)
    {
        $request->validate([
    'name' => 'required',
    'price' => 'required|numeric',
    'speed' => 'required'
]);

        Package::create($request->all());

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil ditambahkan');
    }
}