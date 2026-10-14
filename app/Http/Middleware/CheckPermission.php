<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Supports Spatie-style OR lists:
     * middleware('permission:rfq.view|rfqs:view')
     *
     * Access follows assigned permissions only.
     * Super Admin bypasses so platform owners always retain access.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Only Super Admin has unrestricted route access.
        try {
            if ($user->hasAnyRole(['superadmin', 'Super Admin'])) {
                return $next($request);
            }
        } catch (\Throwable) {
            // Missing role definitions should not block permission checks.
        }

        $required = [];
        foreach ($permissions as $permission) {
            foreach (explode('|', $permission) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $required[] = $part;
                }
            }
        }

        if ($required === [] || $user->canAny($required)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
