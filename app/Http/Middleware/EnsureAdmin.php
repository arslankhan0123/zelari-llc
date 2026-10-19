<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Only SuperAdmin / Admin roles may enter the admin panel. */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next, string $level = 'admin'): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }
        $ok = $level === 'super' ? $user->isSuperAdmin() : $user->isAdmin();
        if (!$ok) {
            abort(403, 'You do not have access to the admin panel.');
        }
        return $next($request);
    }
}
