<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return redirect()->route('settings.index', ['tab' => 'staff']);
    }

    public function create()
    {
        $currentUser = auth()->user();
        $roles = $currentUser->role == 'master'
            ? ['admin' => 'Admin']
            : [
                'finance' => 'Finance',
                'teknisi' => 'Teknisi',
                'noc' => 'NOC',
            ];

        $adminId = $currentUser->parent_admin_id ?: $currentUser->id;
        
        $existingUsers = User::where(function ($query) use ($adminId) {
                $query->where('parent_admin_id', $adminId)->orWhere('id', $adminId);
            })
            ->whereNotNull('face_photo')
            ->get(['id', 'name', 'face_photo']);

        return view('settings.users.create', compact('roles', 'existingUsers'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();
        $this->authorizeManagedUser($currentUser, $user, allowMasterSelf: true);

        $roles = $this->availableRolesFor($currentUser, $user);

        $adminId = $currentUser->parent_admin_id ?: $currentUser->id;
        
        // Ambil semua karyawan yang sudah memiliki foto wajah kecuali user yang sedang diedit
        $existingUsers = User::where(function ($query) use ($adminId) {
                $query->where('parent_admin_id', $adminId)->orWhere('id', $adminId);
            })
            ->where('id', '!=', $user->id)
            ->whereNotNull('face_photo')
            ->get(['id', 'name', 'face_photo']);

        return view('settings.users.edit', compact('user', 'roles', 'existingUsers'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'phone' => 'required|string|max:20',
            'telegram_chat_id' => 'nullable|string|max:50',
            'password' => 'required|min:5',
            'role' => 'required',
        ];

        if ($request->role !== 'admin') {
            $rules['face_photo'] = 'nullable|string';
        } else {
            $rules['face_photo'] = 'nullable|string';
            $rules['customer_limit'] = 'required|integer|in:200,500,1000,2000,3000,4000,5000';
        }

        $request->validate($rules);

        $currentUser = auth()->user();
        $allowedRoles = $currentUser->role == 'master'
            ? ['admin']
            : ['finance', 'teknisi', 'noc'];

        abort_unless(in_array($request->role, $allowedRoles), 403);

        $facePath = null;
        if ($request->face_photo) {
            $img = $request->face_photo;
            $img = str_replace('data:image/png;base64,', '', $img);
            $img = str_replace(' ', '+', $img);
            $data = base64_decode($img);
            
            $fileName = 'face_' . uniqid() . '.png';
            $dir = public_path('storage/face_registered');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($dir . '/' . $fileName, $data);
            $facePath = 'face_registered/' . $fileName;
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'telegram_chat_id' => $request->telegram_chat_id,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'parent_admin_id' => $currentUser->role == 'master' ? null : $currentUser->id,
            'face_photo' => $facePath,
            'customer_limit' => $request->role == 'admin' ? $request->customer_limit : 200,
        ]);

        if ($currentUser->role == 'master') {
            return redirect()->route('dashboard')
                ->with('success', 'User berhasil dibuat');
        }

        return redirect()->route('settings.index', ['tab' => 'staff'])
            ->with('success', 'User berhasil dibuat');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();
        $this->authorizeManagedUser($currentUser, $user, allowMasterSelf: true);

        $roles = array_keys($this->availableRolesFor($currentUser, $user));

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
            'telegram_chat_id' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in($roles)],
            'password' => ['nullable', 'confirmed', 'min:5'],
        ];

        if ($request->role === 'admin') {
            $rules['customer_limit'] = ['required', 'integer', 'in:200,500,1000,2000,3000,4000,5000'];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->telegram_chat_id = $validated['telegram_chat_id'] ?? null;
        $user->role = $validated['role'];

        if ($validated['role'] === 'admin') {
            $user->customer_limit = $validated['customer_limit'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->face_photo) {
            $img = $request->face_photo;
            $img = str_replace('data:image/png;base64,', '', $img);
            $img = str_replace(' ', '+', $img);
            $data = base64_decode($img);
            
            $fileName = 'face_' . uniqid() . '.png';
            $dir = public_path('storage/face_registered');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($dir . '/' . $fileName, $data);
            
            // Hapus foto wajah lama jika ada
            if ($user->face_photo && file_exists(public_path('storage/' . $user->face_photo))) {
                @unlink(public_path('storage/' . $user->face_photo));
            }
            
            $user->face_photo = 'face_registered/' . $fileName;
        }

        $user->save();

        if ($currentUser->role == 'master') {
            return redirect()->route('dashboard')
                ->with('success', 'User berhasil diperbarui');
        }

        return redirect()->route('settings.index', ['tab' => 'staff'])
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $currentUser = auth()->user();
        $user = User::findOrFail($id);

        if ($currentUser->role == 'master') {
            abort_unless($user->role == 'admin', 403);
            $user->subUsers()->delete();
        } else {
            abort_unless($user->parent_admin_id == $currentUser->id, 403);
        }

        $user->delete();

        return back()->with('success', 'User dihapus');
    }

    private function authorizeManagedUser(User $currentUser, User $user, bool $allowMasterSelf = false): void
    {
        if ($currentUser->role == 'master') {
            abort_unless($user->role == 'admin' || ($allowMasterSelf && $user->id == $currentUser->id), 403);
            return;
        }

        abort_unless($user->parent_admin_id == $currentUser->id, 403);
    }

    private function availableRolesFor(User $currentUser, User $user): array
    {
        if ($currentUser->role == 'master' && $user->id == $currentUser->id) {
            return ['master' => 'Master'];
        }

        if ($currentUser->role == 'master') {
            return ['admin' => 'Admin'];
        }

        return [
            'finance' => 'Finance',
            'teknisi' => 'Teknisi',
            'noc' => 'NOC',
        ];
    }
}
