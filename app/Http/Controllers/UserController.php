<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function addUser(Request $request)
    {
        return view('user.create');
    }
}
