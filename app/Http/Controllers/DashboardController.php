<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get role name from session
        $roleName = strtolower(session('role_name'));
        
        // Redirect non-admin users to items.cart
        if ($roleName !== 'admin') {
            return redirect()->route('items.cart');
        }

        // Only admin users will reach this point
        $branch = Branch::where('is_deleted', 'no')->pluck('name', 'id');
        return view('dashboard', compact('branch')); // This refers to resources/views/dashboard.blade.php
    }
}
