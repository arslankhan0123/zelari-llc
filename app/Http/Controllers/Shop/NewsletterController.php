<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $status = Newsletter::subscribe((string) $request->input('email', ''));
        $msg = match ($status) {
            'ok'      => 'You are subscribed — watch your inbox for deals! 🎉',
            'exists'  => 'You are already subscribed. Thanks!',
            'invalid' => 'Please enter a valid email address.',
            default   => 'Something went wrong, please try again.',
        };
        if ($request->expectsJson()) return response()->json(['status' => $status, 'message' => $msg]);
        return back()->with('ztoast', $msg);
    }

    public function unsubscribe(string $token)
    {
        $ok = Newsletter::unsubscribe($token);
        return view('shop.unsubscribe', ['ok' => $ok]);
    }
}
