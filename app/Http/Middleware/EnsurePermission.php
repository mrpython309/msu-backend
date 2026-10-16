<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePermission
{
    /**
     * Handle an incoming request.
     * Usage ->middleware(EnsurePermission::class . ':perm1|perm2')
     */
    public function handle(Request $request, Closure $next, $perms = null)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('auth-login-basic');
        }

        if (!$perms) {
            return $next($request);
        }

        $permsArr = array_filter(explode('|', $perms));

        foreach ($permsArr as $p) {
            if ($user->can($p)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
