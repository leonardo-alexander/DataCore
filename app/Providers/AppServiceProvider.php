<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Rate limiters for sensitive actions
        RateLimiter::for('login', fn ($request) =>
            Limit::perMinute(5)->by($request->ip())->response(fn () =>
                back()->withErrors(['email' => 'Too many login attempts. Please wait a minute and try again.'])->withInput()
            )
        );

        RateLimiter::for('register', fn ($request) =>
            Limit::perMinute(3)->by($request->ip())
        );

        RateLimiter::for('wallet', fn ($request) =>
            Limit::perMinute(10)->by($request->user()?->id ?? $request->ip())
        );

        RateLimiter::for('entry', fn ($request) =>
            Limit::perMinute(20)->by($request->user()?->id ?? $request->ip())
        );

        RateLimiter::for('verification', fn ($request) =>
            Limit::perMinute(3)->by($request->user()?->id ?? $request->ip())
        );

        RateLimiter::for('import', fn ($request) =>
            Limit::perMinute(5)->by($request->user()?->id ?? $request->ip())
        );

        Blade::directive('rupiah', function ($expression) {
            return "<?php echo \App\Support\Money::format($expression); ?>";
        });

        View::composer('partials.topbar', function ($view) {
            $user = Auth::user();

            $view->with([
                'navUnread' => $user ? $user->activities()->where('is_read', false)->count() : 0,
                'navActivities' => $user ? $user->activities()->latest()->take(6)->get() : collect(),
                'navBalance' => $user ? $user->balance() : 0,
            ]);
        });
    }
}
