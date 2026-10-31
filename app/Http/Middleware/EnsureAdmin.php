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
        $ok = match ($level) {
            'master' => $user->isMasterAdmin(),       // platform owner only
            'staff'  => $user->isAdmin(),              // master + admin (NOT vendor)
            default  => $user->isMasterAdmin() || ($user->role && count($user->role->permissionStrings()) > 0), // panel: master + anyone with permissions
        };
        if (!$ok) {
            abort(403, 'You do not have access to this area.');
        }
        return $next($request);
    }
}
