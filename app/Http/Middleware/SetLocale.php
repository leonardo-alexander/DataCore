<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale')
            ?? session('locale', config('app.locale'));

        $locale = in_array($locale, ['en', 'id']) ? $locale : 'en';

        App::setLocale($locale);

        session(['locale' => $locale]);

        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
