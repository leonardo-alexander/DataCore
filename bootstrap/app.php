<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->prepend(\App\Http\Middleware\SetLocale::class);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            // Guest pages need this as much as authenticated ones: a login form
            // restored from the back/forward cache carries the CSRF token of a
            // session that has since been rotated, and signing in returns 419.
            \App\Http\Middleware\PreventBackHistoryCache::class,
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
        $middleware->redirectGuestsTo(fn ($request) => route('login', [
            'locale' => $request->route('locale') ?? session('locale', config('app.locale')),
        ]));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*'),
        );

        /*
         * A stale CSRF token is almost always a session that rotated underneath
         * an open tab — signing in, signing out, or simply idling past the
         * session lifetime — not an attack. Hand the visitor back a form with a
         * fresh token instead of the dead-end "Page expired" screen; the 419
         * page still covers requests we cannot safely bounce.
         *
         * Typed against HttpException, not TokenMismatchException: the handler
         * converts the latter into a 419 HttpException before render callbacks
         * are consulted, so the original only survives as the previous.
         */
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419 || ! $e->getPrevious() instanceof TokenMismatchException) {
                return null;
            }

            if ($request->expectsJson()) {
                return null;
            }

            $locale = $request->route('locale') ?? session('locale', config('app.locale'));

            $target = match (true) {
                $request->is('*/register') => route('register', ['locale' => $locale]),
                $request->is('*/login'), $request->user() === null => route('login', ['locale' => $locale]),
                default => url()->previous(route('dashboard', ['locale' => $locale])),
            };

            return redirect()->to($target)
                ->withInput($request->except(['password', 'password_confirmation', '_token']))
                ->with('error', __('Your session timed out for security. Please try again.'));
        });
    })->create();
