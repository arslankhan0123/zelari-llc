<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

/**
 * Customers post reviews here. Both guests and logged-in customers can review,
 * but only for something they actually bought:
 *   · logged in  → we look for an order on their account or e-mail
 *   · guest      → they enter their order number + the e-mail they ordered with
 * Nothing shows on the website until an admin approves it.
 */
class ReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->where('status', 'active')->firstOrFail();
        $user    = $request->user();

        // ── 1. Does an order entitle them to review? ───────────────────────
        $order = $user ? $product->purchaseOrderFor($user) : null;

        $rules = [
            'rating' => 'required|integer|min:1|max:5',
            'title'  => 'nullable|string|max:120',
            'body'   => 'required|string|min:10|max:2000',
        ];

        // No order on the account → they must prove the purchase with an order number
        if (!$order) {
            $rules['order_number'] = 'required|string|max:40';
            $rules['email']        = 'required|email|max:150';
            $rules['name']         = $user ? 'nullable|string|max:80' : 'required|string|max:80';
        }

        $data = $request->validate($rules, [
            'rating.required'       => 'Please choose a star rating.',
            'body.required'         => 'Please write a few words about the product.',
            'body.min'              => 'Please write at least 10 characters.',
            'order_number.required' => 'Please enter the order number from your confirmation email.',
            'email.required'        => 'Please enter the email you ordered with.',
            'name.required'         => 'Please enter your name.',
        ]);

        if (!$order) {
            $order = $product->purchaseOrderForEmail($data['order_number'], $data['email']);

            if (!$order) {
                return back()->withInput()->with('review_error',
                    'We could not match that order. Check the order number and use the same email address you ordered with — and make sure this product was part of that order.'
                )->withFragment('reviews');
            }
        }

        // ── 2. One review per customer per product ─────────────────────────
        $email    = $user ? $user->email : ($data['email'] ?? $order->customer_email);
        $existing = ($user ? $product->reviewBy($user) : null) ?? $product->reviewByEmail($email);

        if ($existing) {
            return back()->with('review_error', 'You have already reviewed this product — thanks!')->withFragment('reviews');
        }

        // ── 3. Save it as pending ──────────────────────────────────────────
        Review::create([
            'product_id'        => $product->id,
            'user_id'           => $user?->id,
            'order_id'          => $order->id,
            'author_name'       => $user?->name ?: ($data['name'] ?? $order->shipping_name ?: 'Customer'),
            'author_email'      => $email,
            'rating'            => $data['rating'],
            'title'             => $data['title'] ?? null,
            'body'              => $data['body'],
            'verified_purchase' => true,
            'status'            => Review::PENDING,
        ]);

        return back()
            ->with('review_success', 'Thanks for your review! It will appear on this page once our team has approved it.')
            ->withFragment('reviews');
    }
}
