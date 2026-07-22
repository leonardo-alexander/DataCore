<aside class="relative flex h-full flex-col overflow-hidden bg-[#0b0f1c] text-slate-300">
    <div class="pointer-events-none absolute -left-16 top-0 h-72 w-72 rounded-full bg-violet-600/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-10 bottom-10 h-48 w-48 rounded-full bg-indigo-600/10 blur-3xl"></div>

    <div class="relative flex items-center gap-3 px-6 py-5">
        @include('partials.datacore-logo', ['logoClass' => 'h-9 w-9'])
        <div>
            <p class="font-display text-base font-semibold tracking-tight text-white">DataCore</p>
            <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-slate-500">{{ __('Data Marketplace') }}</p>
        </div>
    </div>

    <nav class="relative flex-1 space-y-1 overflow-y-auto px-4 py-4">
        @php
            $nav = [
                [
                    'route' => 'dashboard',
                    'pattern' => 'dashboard',
                    'icon' => 'layout-dashboard',
                    'label' => __('Overview'),
                ],
                [
                    'route' => 'marketplace.index',
                    'pattern' => 'marketplace.*',
                    'icon' => 'compass',
                    'label' => __('Marketplace'),
                ],
                [
                    'route' => 'surveys.index',
                    'pattern' => 'surveys.*',
                    'icon' => 'clipboard-list',
                    'label' => __('Surveys'),
                ],
                [
                    'route' => 'collections.index',
                    'pattern' => 'collections.*',
                    'icon' => 'layers',
                    'label' => __('My Collections'),
                ],
                [
                    'route' => 'transactions.index',
                    'pattern' => 'transactions.*',
                    'icon' => 'arrow-left-right',
                    'label' => __('Transactions'),
                ],
            ];
        @endphp

        <p class="px-3 pb-2 pt-1 font-mono text-[10px] uppercase tracking-[0.18em] text-slate-600">{{ __('Workspace') }}
        </p>

        @foreach ($nav as $item)
            <a href="{{ route($item['route']) }}" @class([
                'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                'bg-indigo-500/15 text-white shadow-[inset_0_0_0_1px_rgba(129,140,248,.25)]' => request()->routeIs(
                    $item['pattern']),
                'text-slate-400 hover:bg-white/5 hover:text-slate-100' => !request()->routeIs(
                    $item['pattern']),
            ])>
                <span @class([
                    'flex h-8 w-8 items-center justify-center rounded-lg transition',
                    'dc-spectrum text-white shadow-glow' => request()->routeIs(
                        $item['pattern']),
                    'bg-white/5 text-slate-400 group-hover:text-slate-200' => !request()->routeIs(
                        $item['pattern']),
                ])>
                    <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4"></i>
                </span>
                {{ $item['label'] }}
                @if (request()->routeIs($item['pattern']))
                    <span class="ml-auto h-1.5 w-1.5 rounded-full bg-violet-400"></span>
                @endif
            </a>
        @endforeach

        @if (auth()->user()->is_admin)
            <a href="{{ route('admin.dashboard') }}" @class([
                'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                'bg-indigo-500/15 text-white shadow-[inset_0_0_0_1px_rgba(129,140,248,.25)]' => request()->routeIs(
                    'admin.*'),
                'text-slate-400 hover:bg-white/5 hover:text-slate-100' => !request()->routeIs(
                    'admin.*'),
            ])>
                <span @class([
                    'flex h-8 w-8 items-center justify-center rounded-lg transition',
                    'dc-spectrum text-white shadow-glow' => request()->routeIs('admin.*'),
                    'bg-white/5 text-slate-400 group-hover:text-slate-200' => !request()->routeIs(
                        'admin.*'),
                ])>
                    <i data-lucide="shield" class="h-4 w-4"></i>
                </span>
                {{ __('Admin') }}
                @if (request()->routeIs('admin.*'))
                    <span class="ml-auto h-1.5 w-1.5 rounded-full bg-violet-400"></span>
                @endif
            </a>
        @endif
    </nav>

    {{-- <div class="relative px-4 pb-5">
        <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-4">
            <div class="flex items-center gap-2 text-white">
                <i data-lucide="sparkles" class="h-4 w-4 text-violet-300"></i>
                <p class="text-sm font-semibold">{{ __('Refine your data') }}</p>
            </div>
            <p class="mt-1 text-xs leading-relaxed text-slate-400">{{ __('Half Clean is free. Upgrade to Full Clean for deduped, typed, market-ready datasets.') }}</p>
            <a href="{{ route('collections.index') }}"
                class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-violet-300 hover:text-violet-200">
                {{ __('Start refining') }} <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
            </a>
        </div>
    </div> --}}
</aside>
