<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Only users with a vendor role may enter the vendor panel. */
class EnsureVendor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }
        if (!$user->isVendor()) {
            abort(403, 'This area is for marketplace vendors.');
        }
        return $next($request);
    }
}
