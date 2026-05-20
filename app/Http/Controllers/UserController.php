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

        return view('settings.users.create', compact('roles'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();
        $this->authorizeManagedUser($currentUser, $user, allowMasterSelf: true);

        $roles = $this->availableRolesFor($currentUser, $user);

        return view('settings.users.edit', compact('user', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:5',
            'role' => 'required'
        ]);

        $currentUser = auth()->user();
        $allowedRoles = $currentUser->role == 'master'
            ? ['admin']
            : ['finance', 'teknisi', 'noc'];

        abort_unless(in_array($request->role, $allowedRoles), 403);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'parent_admin_id' => $currentUser->role == 'master' ? null : $currentUser->id,
        ]);

        return redirect()->route('settings.index', ['tab' => 'staff'])
            ->with('success', 'User berhasil dibuat');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();
        $this->authorizeManagedUser($currentUser, $user, allowMasterSelf: true);

        $roles = array_keys($this->availableRolesFor($currentUser, $user));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in($roles)],
            'password' => ['nullable', 'confirmed', 'min:5'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

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
