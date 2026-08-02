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
        $supported = config('app.supported_locales');

        $locale = $request->route('locale')
            ?? $this->fromPath($request, $supported)
            ?? $this->fromSession($request)
            ?? config('app.locale');

        $locale = in_array($locale, $supported, true) ? $locale : config('app.fallback_locale');

        App::setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        URL::defaults(['locale' => $locale]);

        return $next($request);
    }

    private function fromPath(Request $request, array $supported): ?string
    {
        $segment = $request->segment(1);

        return in_array($segment, $supported, true) ? $segment : null;
    }

    private function fromSession(Request $request): ?string
    {
        return $request->hasSession() ? $request->session()->get('locale') : null;
    }
}
