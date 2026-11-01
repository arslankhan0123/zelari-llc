<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    public function show()
    {
        if (Auth::check() && Auth::user()->isVendor()) {
            return redirect()->route('admin.dashboard');
        }
        return view('vendor.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'store_name' => 'required|string|max:160',
            'name'       => 'required|string|max:120',
            'email'      => 'required|email|max:190|unique:users,email',
            'phone'      => 'nullable|string|max:40',
            'password'   => 'required|string|min:8|confirmed',
            'description'=> 'nullable|string|max:1000',
        ]);

        $vendorRoleId = Role::idFor(Role::VENDOR);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => $vendorRoleId,
        ]);

        $vendor = Vendor::create([
            'user_id'     => $user->id,
            'name'        => $data['store_name'],
            'email'       => $data['email'],
            'phone'       => $data['phone'] ?? null,
            'description' => $data['description'] ?? null,
            'status'      => Vendor::PENDING,
        ]);
        $user->forceFill(['vendor_id' => $vendor->id])->save();

        Auth::login($user);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Application submitted! Your store is pending admin approval.');
    }
}
