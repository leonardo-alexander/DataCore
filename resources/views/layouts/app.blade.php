<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DataCore') · DataCore</title>
    <link rel="icon" type="image/svg+xml" href="/datacore-logo.svg">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-slate-50 font-sans text-slate-900">
    <div x-data="{ sidebar: false }" class="min-h-full">
        <div x-show="sidebar" x-cloak @click="sidebar = false"
            class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

        <div :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 w-72 transform transition-transform duration-200 lg:translate-x-0">
            @include('partials.sidebar')
        </div>

        <div class="lg:pl-72">
            @include('partials.topbar')

            <main class="dc-grain min-h-[calc(100vh-4rem)]">
                <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
                    @include('partials.flash')
                    @yield('content')
                </div>
            </main>
        </div>

        @include('partials.guide-tour')
    </div>

    @stack('scripts')
</body>

</html>
