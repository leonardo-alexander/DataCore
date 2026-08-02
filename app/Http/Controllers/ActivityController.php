<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function read()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->activities()->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', __('Notifications marked as read.'));
    }
}
