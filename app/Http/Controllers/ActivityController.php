<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function read()
    {
        Auth::user()->activities()->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'Notifications marked as read.');
    }
}
