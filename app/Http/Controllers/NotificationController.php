<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // app/Http/Controllers/PopupController.php

    public function loadForm($type)
    {
        if ($type === 'low_stock') {
            return view('notification.product-form'); // resources/views/popups/user-form.blade.php
        }

        if ($type === 'product') {
            return view('notification.product-form');
        }

        return response()->json(['error' => 'Form not found'], 404);
    }

}
