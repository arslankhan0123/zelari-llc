<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Per-action RBAC for the HRM module — separate from the ecommerce PanelPermission.
 * Entry requires an HRM role (or master admin). Infers "hrm.<module>.<action>"
 * from the route name (admin.hrm.<module>.<action>) + HTTP method.
 */
class HrmPermission
{
    protected array $actionMap = [
        'index' => 'list', 'show' => 'view',
        'create' => 'create', 'store' => 'create',
        'edit' => 'edit', 'update' => 'edit', 'patch' => 'edit',
        'destroy' => 'delete', 'delete' => 'delete',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Must have HRM access at all (master admin, or an assigned HRM role).
        if (!$user->hasHrmAccess()) {
            abort(403, 'You do not have access to the HRM module.');
        }
        if ($user->isMasterAdmin()) {
            return $next($request);
        }

        $name = $request->route()?->getName();              // admin.hrm.employees.index
        if (!$name || !str_starts_with($name, 'admin.hrm.')) {
            return $next($request);
        }

        $parts  = explode('.', substr($name, 10));          // [employees, index] or [index]
        $module = $parts[0] ?? 'dashboard';
        $action = count($parts) > 1 ? end($parts) : 'list';

        // The HRM landing page (admin.hrm.index) only needs HRM access.
        if ($module === 'index' || $module === '') {
            return $next($request);
        }

        if (!config("hrm_permissions.$module")) {
            return $next($request);
        }

        $permAction = $this->actionMap[$action]
            ?? ($request->isMethodSafe() ? 'view' : 'edit');

        if (!$user->hasHrmPermission("hrm.$module.$permAction")) {
            abort(403, 'You do not have permission for this HRM action.');
        }

        return $next($request);
    }
}
