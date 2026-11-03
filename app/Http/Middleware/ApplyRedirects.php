<?php
namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ApplyRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if ($request->isMethod('GET') && Schema::hasTable('redirects')) {
                $path = '/' . ltrim($request->path(), '/');
                $r = Redirect::where('enabled', true)
                    ->where(fn ($q) => $q->where('from_path', $path)->orWhere('from_path', ltrim($path, '/')))
                    ->first();
                if ($r && trim($r->to_path) !== '' && $r->to_path !== $path) {
                    $r->increment('hits');
                    return redirect($r->to_path, in_array($r->code, [301, 302], true) ? $r->code : 301);
                }
            }
        } catch (\Throwable $e) {
            // never block the request over a redirect lookup
        }
        return $next($request);
    }
}
