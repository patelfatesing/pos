<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class CheckAccessIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = DB::table('access_ip_table')->pluck('ip_address')->toArray();
        $clientIp = $request->ip(); // Get client's IP

        if (!in_array($clientIp, $allowedIps)) {
            abort(403, 'Unauthorized access - IP not allowed');
        }

        return $next($request);
    }
}
