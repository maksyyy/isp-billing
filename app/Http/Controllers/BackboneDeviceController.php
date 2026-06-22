<?php

namespace App\Http\Controllers;

use App\Models\BackboneDevice;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\AdminScoped;

class BackboneDeviceController extends Controller
{
    use AdminScoped;

    /**
     * Show the main backbone alerts page.
     */
    public function index()
    {
        return view('dashboard.backbone');
    }

    /**
     * Get list of backbone devices in JSON format.
     */
    public function apiIndex()
    {
        $adminId = $this->resolveAdminId();
        
        if ($adminId === null) {
            // For safety and compatibility with master role
            if (auth()->user()->role === 'master') {
                $devices = BackboneDevice::latest()->get();
            } else {
                return response()->json([]);
            }
        } else {
            $devices = BackboneDevice::where('admin_id', $adminId)->latest()->get();
        }

        return response()->json($devices);
    }

    /**
     * Store a new backbone device.
     */
    public function apiStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip' => 'required|ip',
        ]);

        $adminId = $this->resolveAdminId();
        if ($adminId === null) {
            return response()->json(['error' => 'Unauthorized or missing tenant context.'], 403);
        }

        $device = BackboneDevice::create([
            'admin_id' => $adminId,
            'name' => $request->name,
            'ip' => $request->ip,
            'status' => 'up',
        ]);

        return response()->json($device, 201);
    }

    /**
     * Update an existing backbone device.
     */
    public function apiUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip' => 'required|ip',
        ]);

        $adminId = $this->resolveAdminId();
        
        if ($adminId === null) {
            $device = BackboneDevice::findOrFail($id);
        } else {
            $device = BackboneDevice::where('admin_id', $adminId)->findOrFail($id);
        }

        $device->update([
            'name' => $request->name,
            'ip' => $request->ip,
        ]);

        return response()->json($device);
    }

    /**
     * Delete a backbone device.
     */
    public function apiDestroy($id)
    {
        $adminId = $this->resolveAdminId();
        
        if ($adminId === null) {
            $device = BackboneDevice::findOrFail($id);
        } else {
            $device = BackboneDevice::where('admin_id', $adminId)->findOrFail($id);
        }
        
        $device->delete();

        return response()->json(['success' => true]);
    }
}
