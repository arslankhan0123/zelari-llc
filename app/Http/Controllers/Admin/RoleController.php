<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderByDesc('is_system')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.create', ['modules' => config('permissions')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:60|unique:roles,name',
            'description'   => 'nullable|string|max:255',
            'permissions'   => 'array',
            'permissions.*' => 'string',
        ]);

        $base = Str::slug($data['name'], '_') ?: 'role'; $slug = $base; $i = 1;
        while (Role::where('slug', $slug)->exists()) { $slug = $base . '_' . (++$i); }

        $role = Role::create([
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'is_system'   => false,
        ]);
        $role->syncPermissions($this->validPermissions($request->input('permissions', [])));

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$role->name}\" created.");
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', [
            'role'    => $role,
            'modules' => config('permissions'),
            'granted' => $role->permissionStrings(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:60|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
            'permissions' => 'array',
        ]);

        // System role names/slugs stay fixed; custom roles can be renamed
        if (!$role->is_system) {
            $role->name = $data['name'];
        }
        $role->description = $data['description'] ?? $role->description;
        $role->save();

        // Master Admin always has every permission implicitly — don't store a matrix for it
        if ($role->slug !== Role::MASTER_ADMIN) {
            $role->syncPermissions($this->validPermissions($request->input('permissions', [])));
        }

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$role->name}\" updated.");
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'Built-in roles cannot be deleted.');
        }
        if (User::where('role_id', $role->id)->exists()) {
            return back()->with('error', 'Reassign users off this role before deleting it.');
        }
        $role->delete();
        return back()->with('success', 'Role deleted.');
    }

    /** Keep only valid "module.action" strings from config. */
    protected function validPermissions(array $perms): array
    {
        $valid = [];
        foreach (config('permissions') as $mod => $def) {
            foreach ($def['actions'] as $act) $valid[] = "$mod.$act";
        }
        return array_values(array_intersect($perms, $valid));
    }
}
