<?php
namespace App\Http\Controllers\Admin\Hrm;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\HrmRole;
use Illuminate\Http\Request;

class HrmUserController extends Controller
{
    public function index()
    {
        $users = User::whereNotNull('hrm_role_id')->with('hrmRole')->orderBy('name')->get();
        $roles = HrmRole::orderBy('name')->get();
        return view('admin.hrm.users.index', compact('users', 'roles'));
    }

    /** Grant HRM access to an existing login account by email. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'       => 'required|email',
            'hrm_role_id' => 'required|exists:hrm_roles,id',
        ]);
        $user = User::where('email', $data['email'])->first();
        if (!$user) return back()->with('error', 'No account found with that email. The person must have a login account first.');
        $user->update(['hrm_role_id' => $data['hrm_role_id']]);
        return back()->with('success', "HRM access granted to {$user->name}.");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate(['hrm_role_id' => 'required|exists:hrm_roles,id']);
        $user->update(['hrm_role_id' => $data['hrm_role_id']]);
        return back()->with('success', 'HRM role updated.');
    }

    /** Revoke HRM access. */
    public function destroy(User $user)
    {
        $user->update(['hrm_role_id' => null]);
        return back()->with('success', 'HRM access revoked.');
    }
}
