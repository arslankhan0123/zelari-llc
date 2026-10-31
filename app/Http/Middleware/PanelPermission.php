<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Per-action RBAC enforcement for the admin/ecommerce panel.
 * Infers "module.action" from the route name + HTTP method and checks it.
 */
class PanelPermission
{
    /** Route-name module segment → permission module. */
    protected array $moduleMap = [
        'pdftools' => 'tools', 'calcs' => 'tools', 'imgtools' => 'tools', 'texttools' => 'tools',
        'sellertools' => 'tools', 'randomtools' => 'tools', 'colortools' => 'tools', 'qrtools' => 'tools', 'devtools' => 'tools',
        'roles' => 'users', 'users' => 'users',
    ];

    /** Route-action segment → permission action. */
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
        if ($user->isMasterAdmin()) {
            return $next($request);
        }

        $name = $request->route()?->getName();

        // A vendor whose store isn't approved yet can only see the dashboard (with its pending notice)
        if ($user->isVendor()) {
            $vendor = $user->vendor;
            if (!$vendor || $vendor->status !== \App\Models\Vendor::APPROVED) {
                if ($name !== 'admin.dashboard') {
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'Your store is pending approval — you can manage it once a master admin approves it.');
                }
            }
        }            // e.g. admin.products.index
        if (!$name || !str_starts_with($name, 'admin.')) {
            return $next($request);
        }

        $parts  = explode('.', substr($name, 6));          // [products, index] or [dashboard] or [settings, email]
        $module = $this->moduleMap[$parts[0]] ?? $parts[0];
        $action = count($parts) > 1 ? end($parts) : 'list';

        // Only enforce for modules we know about
        if (!config("permissions.$module")) {
            return $next($request);
        }

        $permAction = $this->actionMap[$action]
            ?? ($request->isMethodSafe() ? 'view' : 'edit');  // custom GET → view, custom write → edit

        if (!$user->hasPermission("$module.$permAction")) {
            abort(403, 'You do not have permission for this action.');
        }

        return $next($request);
    }
}
