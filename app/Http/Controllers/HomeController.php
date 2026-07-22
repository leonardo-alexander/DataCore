<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __invoke()
    {
        return redirect()->route(Auth::check() ? 'dashboard' : 'login');
    }

    public function root()
    {
        $locale = session('locale', config('app.locale'));

        return redirect("/{$locale}");
    }
}
