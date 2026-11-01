<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\HrmRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HrmRoleController extends Controller
{
    public function index()
    {
        $roles = HrmRole::withCount('users')->orderByDesc('is_system')->orderBy('name')->get();
        return view('admin.hrm.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.hrm.roles.create', ['modules' => config('hrm_permissions')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:60|unique:hrm_roles,name',
            'description'   => 'nullable|string|max:255',
            'permissions'   => 'array',
            'permissions.*' => 'string',
        ]);
        $base = Str::slug($data['name'], '_') ?: 'role'; $slug = $base; $i = 1;
        while (HrmRole::where('slug', $slug)->exists()) { $slug = $base . '_' . (++$i); }

        $role = HrmRole::create([
            'name' => $data['name'], 'slug' => $slug,
            'description' => $data['description'] ?? null, 'is_system' => false,
        ]);
        $role->syncPermissions($this->valid($request->input('permissions', [])));
        return redirect()->route('admin.hrm.roles.index')->with('success', "Role \"{$role->name}\" created.");
    }

    public function edit(HrmRole $role)
    {
        return view('admin.hrm.roles.edit', ['role' => $role, 'modules' => config('hrm_permissions'), 'granted' => $role->permissionStrings()]);
    }

    public function update(Request $request, HrmRole $role)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:60|unique:hrm_roles,name,' . $role->id,
            'description'   => 'nullable|string|max:255',
            'permissions'   => 'array',
            'permissions.*' => 'string',
        ]);
        $role->update(['name' => $data['name'], 'description' => $data['description'] ?? null]);
        $role->syncPermissions($this->valid($request->input('permissions', [])));
        return redirect()->route('admin.hrm.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(HrmRole $role)
    {
        if ($role->is_system) return back()->with('error', 'System roles cannot be deleted.');
        $role->delete();
        return back()->with('success', 'Role deleted.');
    }

    private function valid(array $perms): array
    {
        $allowed = [];
        foreach (array_keys(config('hrm_permissions', [])) as $m) {
            foreach (config("hrm_permissions.$m.actions", []) as $a) { $allowed[] = "hrm.$m.$a"; }
        }
        return array_values(array_intersect($perms, $allowed));
    }
}
