<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    private function vendorId(): ?int
    {
        return auth()->user()->vendorId();
    }

    public function index()
    {
        $vid   = $this->vendorId();
        $staffRoleId = Role::idFor(Role::VENDOR_STAFF);

        $query = User::with('vendor')->where('role_id', $staffRoleId)->latest();
        if ($vid) {
            $query->where('vendor_id', $vid);              // vendor sees only their own team
        }
        $staff = $query->get();

        // Admins can assign staff to any vendor; vendors are locked to their own.
        $vendors = $vid ? collect() : Vendor::orderBy('name')->get(['id', 'name']);

        return view('admin.staff.index', compact('staff', 'vendors', 'vid'));
    }

    public function store(Request $request)
    {
        $vid = $this->vendorId();

        $rules = [
            'name'     => 'required|string|max:120',
            'email'    => ['required', 'email', 'max:160', Rule::unique('users', 'email')],
            'password' => 'required|string|min:8',
        ];
        if (!$vid) {
            $rules['vendor_id'] = 'required|exists:vendors,id';
        }
        $data = $request->validate($rules);

        $targetVendor = $vid ?: (int) $data['vendor_id'];

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role_id'   => Role::idFor(Role::VENDOR_STAFF),
            'vendor_id' => $targetVendor,
            'real_password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'Staff member added.');
    }

    public function destroy(User $staff)
    {
        $vid = $this->vendorId();
        // A vendor can only remove their own staff; must be a staff account.
        abort_unless($staff->isVendorStaff(), 404);
        if ($vid) {
            abort_unless($staff->vendor_id === $vid, 403);
        }
        $staff->delete();
        return back()->with('success', 'Staff member removed.');
    }
}
