<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · DataCore</title>
    <link rel="icon" type="image/svg+xml" href="/datacore-logo.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    // An error page can render after the session or router has already failed, so
    // every lookup below falls back rather than throwing a second time.
    $home = rescue(
        fn() => auth()->check() ? route('dashboard', ['locale' => app()->getLocale()]) : url('/'),
        url('/'),
        false,
    );

    // ThrottleRequests and maintenance mode both attach Retry-After.
    $retryAfter = rescue(
        fn() => isset($exception) && method_exists($exception, 'getHeaders')
            ? $exception->getHeaders()['Retry-After'] ?? null
            : null,
        null,
        false,
    );
@endphp

<body class="h-full bg-[#0b0f1c] font-sans text-slate-900">
    <div class="relative flex min-h-full flex-col overflow-hidden px-6 py-8 sm:px-10">

        {{-- Ambient spectrum wash over the app's own grain texture --}}
        <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-violet-600/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-20 h-80 w-80 rounded-full bg-indigo-600/20 blur-3xl"></div>
        <div class="dc-grain pointer-events-none absolute inset-0 opacity-70"></div>

        <header class="relative">
            <a href="{{ $home }}" class="inline-flex items-center gap-3">
                @include('partials.datacore-logo', ['logoClass' => 'h-9 w-9'])
                <span class="font-display text-base font-semibold text-white">DataCore</span>
            </a>
        </header>

        <main class="relative flex flex-1 items-center justify-center py-10">
            <div class="w-full max-w-xl text-center">

                <span
                    class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 font-mono text-[11px] uppercase tracking-[0.2em] text-violet-300 backdrop-blur-sm">
                    <i data-lucide="@yield('icon', 'triangle-alert')" class="h-3.5 w-3.5"></i>
                    @yield('label')
                </span>

                <p class="dc-text-spectrum mt-6 font-display text-[5.5rem] font-semibold leading-[0.85] sm:text-[7.5rem]">
                    @yield('code')
                </p>

                <h1 class="mt-4 font-display text-2xl font-semibold leading-tight text-white sm:text-3xl">
                    @yield('title')
                </h1>

                <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-slate-400">
                    @yield('message')
                </p>

                @if (filled($retryAfter) && is_numeric($retryAfter))
                    <p
                        class="mt-5 inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 font-mono text-xs text-slate-300">
                        <i data-lucide="timer" class="h-3.5 w-3.5 text-violet-300"></i>
                        {{ __('Try again in :seconds s', ['seconds' => (int) $retryAfter]) }}
                    </p>
                @endif

                <div class="mt-9 flex flex-wrap items-center justify-center gap-2.5">
                    <a href="{{ $home }}"
                        class="dc-spectrum inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110 focus:outline-none focus:ring-4 focus:ring-violet-500/30">
                        <i data-lucide="home" class="h-4 w-4"></i>
                        {{ __('Back to home') }}
                    </a>

                    <button type="button"
                        onclick="history.length > 1 ? history.back() : window.location.assign(@js($home))"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/15 px-5 py-2.5 text-sm font-medium text-slate-300 transition hover:border-white/30 hover:bg-white/5 hover:text-white focus:outline-none focus:ring-4 focus:ring-white/10">
                        <i data-lucide="arrow-left" class="h-4 w-4"></i>
                        {{ __('Go back') }}
                    </button>

                    @yield('action')
                </div>
            </div>
        </main>

        <footer
            class="relative flex flex-wrap items-center justify-between gap-2 font-mono text-[11px] uppercase tracking-[0.15em] text-slate-600">
            <span>{{ __('Raw data, refined') }}</span>
            {{-- Keep a non-word char before @yield; Blade skips directives glued to a word. --}}
            <span>ERR · @yield('code') · {{ now()->utc()->format('d M Y H:i') }} UTC</span>
        </footer>
    </div>
</body>

</html>
