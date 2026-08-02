<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · DataCore</title>
    <link rel="icon" type="image/svg+xml" href="/datacore-logo.svg">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-white font-sans text-slate-900">
    <div class="relative flex min-h-full items-center justify-center overflow-hidden bg-[#0b0f1c] px-6 py-16">
        <div class="pointer-events-none absolute -left-24 top-0 h-96 w-96 rounded-full bg-violet-600/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-16 bottom-0 h-80 w-80 rounded-full bg-indigo-600/20 blur-3xl"></div>

        <div class="relative w-full max-w-lg text-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                @include('partials.datacore-logo', ['logoClass' => 'h-10 w-10'])
                <span class="font-display text-lg font-semibold text-white">DataCore</span>
            </a>

            <p class="mt-12 font-mono text-sm uppercase tracking-[0.3em] text-violet-300">@yield('code')</p>

            <h1 class="mt-4 font-display text-3xl font-semibold leading-tight text-white sm:text-4xl">
                @yield('title')
            </h1>

            <p class="mx-auto mt-4 max-w-md text-slate-400">
                @yield('message')
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ url('/') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:bg-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/30">
                    <i data-lucide="home" class="h-4 w-4"></i>
                    {{ __('Back to home') }}
                </a>

                <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/15 px-5 py-2.5 text-sm font-medium text-slate-300 transition hover:border-white/30 hover:text-white focus:outline-none focus:ring-4 focus:ring-white/10">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    {{ __('Go back') }}
                </button>
            </div>
        </div>
    </div>
</body>

</html>
