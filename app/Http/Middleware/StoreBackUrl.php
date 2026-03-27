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
    public function handle($request, Closure $next)
    {
        if (!$request->ajax()) {
            session(['previous_url' => url()->current()]);
        }

        return $next($request);
    }

    // public function handle($request, Closure $next)
    // {
    //     if (!$request->ajax() && !$request->isMethod('post')) {

    //         $history = session()->get('url_history', []);

    //         // Add current URL
    //         $current = url()->current();

    //         // Avoid duplicate consecutive URLs
    //         if (empty($history) || end($history) !== $current) {
    //             $history[] = $current;
    //         }

    //         // Keep only last 10 URLs (optional limit)
    //         $history = array_slice($history, -10);

    //         session(['url_history' => $history]);
    //     }

    //     return $next($request);
    // }
}
