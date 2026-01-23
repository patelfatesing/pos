<?php

// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Please login to access this page.');
        }

        // Check if authenticated user has the required permission
        if (!auth()->user()->hasPermission($permission)) {
            abort(403, 'Unauthorized Access - You do not have the required permission.');
        }

        return $next($request);
        
    }
}
