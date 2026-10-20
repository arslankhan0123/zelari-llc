<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
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

        $email = mb_strtolower($user->email);

        // Reviews this customer has written (matched by account or by the e-mail they used)
        $myReviews = Review::with('product')
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhereRaw('LOWER(author_email) = ?', [$email]))
            ->latest()
            ->get();

        // Products they bought but have not reviewed yet
        $orderIds = Order::where('status', '!=', 'cancelled')
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhereRaw('LOWER(customer_email) = ?', [$email]))
            ->pluck('id');

        $boughtIds = OrderItem::whereIn('order_id', $orderIds)
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id');

        $toReview = Product::where('status', 'active')
            ->whereIn('id', $boughtIds)
            ->whereNotIn('id', $myReviews->pluck('product_id')->filter()->all() ?: [0])
            ->latest()
            ->take(12)
            ->get();

        return view('shop.account', compact('user', 'orders', 'myReviews', 'toReview'));
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
