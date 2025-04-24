<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;

class DashboardController extends Controller
{
    public function index()
    {
        $branch = Branch::where('is_deleted', 'no')->pluck('name', 'id');
        return view('dashboard', compact('branch')); // This refers to resources/views/dashboard.blade.php
    }
}
