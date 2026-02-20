<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    /**
     * Ensure the current user is a platform super admin.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Super admin access required.');
        }

        return $next($request);
    }
}
