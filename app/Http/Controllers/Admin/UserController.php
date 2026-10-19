<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/** Admin → Users: panel accounts and their roles. SuperAdmin only. */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::with('role')->latest();
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }
        if ($request->filled('role')) $q->whereHas('role', fn ($r) => $r->where('slug', $request->role));
        $users = $q->paginate(15)->withQueryString();
        $roles = Role::orderBy('id')->get();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|max:190|unique:users,email',
            'password' => 'required|string|min:8|max:72',
            'role_id'  => 'required|exists:roles,id',
        ]);
        User::create([
            'name'          => $data['name'],
            'email'         => strtolower($data['email']),
            'password'      => Hash::make($data['password']),
            'real_password' => $data['password'],
            'role_id'       => $data['role_id'],
            'email_verified_at' => now(),
        ]);
        return back()->with('success', 'Account created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id'  => 'required|exists:roles,id',
            'password' => 'nullable|string|min:8|max:72',
        ]);
        // Never let the last SuperAdmin demote themselves
        $superId = Role::idFor(Role::SUPER_ADMIN);
        if ($user->role_id === $superId && (int) $data['role_id'] !== $superId && User::where('role_id', $superId)->count() <= 1) {
            return back()->with('error', 'There must be at least one SuperAdmin.');
        }
        $user->name    = $data['name'];
        $user->email   = strtolower($data['email']);
        $user->role_id = $data['role_id'];
        if (!empty($data['password'])) {
            $user->password      = Hash::make($data['password']);
            $user->real_password = $data['password'];
        }
        $user->save();
        return back()->with('success', 'Account updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', "You can't delete your own account.");
        }
        if ($user->isSuperAdmin() && User::where('role_id', $user->role_id)->count() <= 1) {
            return back()->with('error', 'There must be at least one SuperAdmin.');
        }
        $user->delete();
        return back()->with('success', 'Account deleted.');
    }
}
