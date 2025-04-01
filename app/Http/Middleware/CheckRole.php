<?php

// app/Http/Middleware/CheckRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
    // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Please login to access this page.');
        }

        // Check if the authenticated user has the required role
        if (!auth()->user()->hasRole($role)) {
            abort(403, 'Unauthorized Access');
        }

        return $next($request);
}
}
