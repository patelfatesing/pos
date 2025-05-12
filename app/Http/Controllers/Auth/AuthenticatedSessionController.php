<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        // Get role name after authentication
        $roleName = \DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', auth()->id())
            ->value('roles.name');

        // Store it in session
        session(['role_name' => $roleName]);
        if(strtolower($roleName)=="cashier"){
            return redirect()->intended(route('items.cart', absolute: false));
        }else if(strtolower($roleName)=="warehouse"){
            return redirect()->intended(route('items.cart', absolute: false));
        }else{
            return redirect()->intended(route('dashboard', absolute: false));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = User::find(Auth::id());
        $user->is_login = 'No';
        $user->save();
        Auth::guard('web')->logout();
        if (session()->has('checkout_images')) {
            session()->forget('checkout_images');
        }

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}