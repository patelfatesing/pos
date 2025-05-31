<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $roleName = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', Auth::id())
            ->value('roles.name');

        // Store it in session
        session(['role_name' => $roleName]);
        
        // Always redirect cashier and warehouse users to items.cart
        $roleName = strtolower($roleName);
        if ($roleName === "cashier" || $roleName === "warehouse") {
            return redirect(route('items.cart'));
        }
        
        // All other roles go to dashboard
        // return redirect(route('dashboard'));
        return redirect()->intended('/dashboard');
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
        
        session()->forget(auth()->id().'_warehouse_product_photo_path', []);
        session()->forget(auth()->id().'_warehouse_customer_photo_path', []);
        session()->forget(auth()->id().'_cashier_product_photo_path', []);
        session()->forget(auth()->id().'_cashier_customer_photo_path', []);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}