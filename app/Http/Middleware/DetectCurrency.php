<?php

namespace App\Http\Middleware;

use App\Support\Currency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * On the first request of a session, work out the visitor's country from
 * their IP and pick the matching currency. Skipped for the admin area.
 */
class DetectCurrency
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->is('admin*') && !$request->is('up') && !$request->is('webhooks/*')) {
            try {
                Currency::detect((string) $request->ip());
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $next($request);
    }
}
