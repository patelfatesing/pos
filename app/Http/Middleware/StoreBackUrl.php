<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreBackUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('get')) {

            $history = session('url_history', []);
            $current = url()->full();

            if (empty($history) || end($history) != $current) {
                $history[] = $current;
            }

            session(['url_history' => $history]);
        }

        return $next($request);
    }
}
