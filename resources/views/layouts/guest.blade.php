<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') · DataCore</title>
    <link rel="icon" type="image/svg+xml" href="/datacore-logo.svg">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-white font-sans text-slate-900">
    <div class="flex min-h-full">
        <div class="relative hidden w-1/2 overflow-hidden bg-[#0b0f1c] lg:block">
            <div class="absolute -left-20 top-10 h-96 w-96 rounded-full bg-violet-600/25 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-indigo-600/20 blur-3xl"></div>
            <div class="relative flex h-full flex-col justify-between p-12">
                <div class="flex items-center gap-3">
                    @include('partials.datacore-logo', ['logoClass' => 'h-10 w-10'])
                    <span class="font-display text-lg font-semibold text-white">DataCore</span>
                </div>

                <div class="max-w-md">
                    <p class="font-mono text-xs uppercase tracking-[0.2em] text-violet-300">{{ __('Raw data, refined') }}</p>
                    <h1 class="mt-4 font-display text-4xl font-semibold leading-tight text-white">{{ __('The marketplace for data worth trusting.') }}</h1>
                    <p class="mt-4 text-slate-400">{{ __('Collect entries, refine them through our cleaning pipeline, and sell market-ready datasets. Half Clean is free. Full Clean is premium.') }}</p>
                </div>
            </div>
        </div>

        <div class="flex w-full items-center justify-center px-6 py-12 lg:w-1/2">
            <div class="w-full max-w-md">
                <div class="mb-6 flex justify-end" x-data="{ open: false }" @click.outside="open = false">
                    <div class="relative">
                        <button @click="open = !open"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                            <i data-lucide="globe" class="h-3.5 w-3.5 text-slate-400"></i>
                            {{ app()->getLocale() === 'id' ? __('Bahasa Indonesia') : __('English') }}
                            <i data-lucide="chevron-down" class="h-3 w-3 text-slate-300 transition-transform" :class="open && 'rotate-180'"></i>
                        </button>
                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-full z-30 mt-1.5 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white p-1 shadow-lg">
                            @foreach (['en' => __('English'), 'id' => __('Bahasa Indonesia')] as $val => $label)
                                <button type="button" @click="open = false; dcChangeLanguage('{{ $val }}')"
                                    @class([
                                        'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition',
                                        'bg-indigo-50 font-semibold text-indigo-700' => app()->getLocale() === $val,
                                        'text-slate-600 hover:bg-slate-50' => app()->getLocale() !== $val,
                                    ])>{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                @yield('content')
            </div>
        </div>
    </div>

    <script>
        async function dcChangeLanguage(locale) {
            try {
                const res = await fetch(@js(route('language.update')), {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ locale }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.redirect) {
                    window.location.href = data.redirect;
                }
            } catch (e) {}
        }
    </script>
</body>

</html>
