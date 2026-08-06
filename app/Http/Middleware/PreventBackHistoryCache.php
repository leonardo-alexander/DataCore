<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistoryCache
{
    /**
     * Stop the browser from serving a cached copy of a page it has already been
     * shown. Two things go wrong without this:
     *
     * 1. After logging out, the back button re-displays an authenticated page.
     * 2. Signing in fails with "419 Page expired", because the browser restored
     *    a login form it had rendered under an older session (bfcache, or a tab
     *    left open) and submitted that session's stale CSRF token.
     *
     * Only "no-store" keeps a page out of the back/forward cache, so it has to
     * cover guest pages too — the stale form in (2) is the login page itself.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
