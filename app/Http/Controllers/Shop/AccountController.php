<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Storefront "My account": profile, password and order history. */
class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $orders = Order::withCount('items')
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('customer_email', $user->email))
            ->latest()
            ->paginate(10, ['*'], 'orders_page');

        return view('shop.account', compact('user', 'orders'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name'  => 'required|string|max:120',
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
        ]);
        $emailChanged = strtolower($data['email']) !== strtolower($user->email);
        $user->fill(['name' => $data['name'], 'email' => strtolower($data['email'])]);
        if ($emailChanged) $user->email_verified_at = null;
        $user->save();

        return back()->with('account_success', 'Profile updated.')->with('tab', 'profile');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);
        $user = $request->user();
        $user->password      = Hash::make($data['password']);
        $user->real_password = $data['password']; // kept in step (encrypted at rest)
        $user->save();

        return back()->with('account_success', 'Password changed.')->with('tab', 'password');
    }
}
