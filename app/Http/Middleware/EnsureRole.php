<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureRole
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware(EnsureRole::class . ':admin|superadmin')
     */
    public function handle(Request $request, Closure $next, $roles = null)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('auth-login-basic');
        }

        if (!$roles) {
            return $next($request);
        }

        $rolesArr = array_filter(explode('|', $roles));

        // If user has any of the roles, allow
        if ($user->hasAnyRole($rolesArr)) {
            return $next($request);
        }

        abort(403);
    }
}
