@extends('layouts.app')

@section('title', __('Overview'))

@section('content')
    @php
        $stateBadge = [
            'raw' => ['label' => 'Raw', 'class' => 'bg-slate-100 text-slate-600'],
            'clean1' => ['label' => 'Half Clean', 'class' => 'bg-indigo-50 text-indigo-700'],
            'clean2' => ['label' => 'Full Clean', 'class' => 'bg-violet-50 text-violet-700'],
        ];
        $activityIcon = [
            'system' => 'bell',
            'cleaning' => 'sparkles',
            'entry' => 'file-plus-2',
            'collection' => 'layers',
            'review' => 'star',
            'payment' => 'credit-card',
            'topup' => 'wallet',
            'reward' => 'gift',
            'purchase' => 'shopping-bag',
        ];
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">
                {{ __('Welcome back, :name', ['name' => \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->first()]) }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __("Here's what's happening across your data refinery.") }}</p>
        </div>
        <a href="{{ route('collections.create') }}"
            class="dc-spectrum inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
            <i data-lucide="plus" class="h-4 w-4"></i> {{ __('New collection') }}
        </a>
    </div>

    {{-- Get Started checklist (hidden once all done or dismissed) --}}
    @unless ($checklistDone)
        @php $doneCount = collect($checklist)->where('done', true)->count(); @endphp
        <div x-data="{ show: localStorage.getItem('dc_guide_dismissed') !== '{{ session()->getId() }}' }" x-show="show" x-cloak
            class="mt-7 overflow-hidden rounded-2xl border border-indigo-200/70 bg-white shadow-card">
            <div class="flex items-center justify-between gap-4 border-b border-indigo-100 bg-indigo-50/60 px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                        <i data-lucide="map" class="h-4 w-4"></i>
                    </span>
                    <div>
                        <p class="font-display text-sm font-semibold text-slate-900">{{ __('Get started with DataCore') }}</p>
                        <p class="text-xs text-slate-500">{{ __(':done of :total steps complete', ['done' => $doneCount, 'total' => count($checklist)]) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden items-center gap-2 sm:flex">
                        <div class="h-2 w-32 overflow-hidden rounded-full bg-indigo-100">
                            <div class="h-2 rounded-full bg-indigo-500 transition-all"
                                style="width: {{ round($doneCount / count($checklist) * 100) }}%"></div>
                        </div>
                        <span class="font-mono text-xs font-semibold text-indigo-600">{{ round($doneCount / count($checklist) * 100) }}%</span>
                    </div>
                    <button @click="localStorage.setItem('dc_guide_dismissed', '{{ session()->getId() }}'); show = false"
                        class="text-slate-400 transition hover:text-slate-600">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-3 lg:divide-y lg:divide-x-0">
                @foreach ($checklist as $step)
                    <a href="{{ route($step['route']) }}" @class([
                        'group flex items-start gap-4 px-5 py-4 transition',
                        'opacity-50' => $step['done'],
                        'hover:bg-slate-50' => !$step['done'],
                    ])>
                        <span @class([
                            'mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 transition',
                            'border-emerald-400 bg-emerald-400 text-white' => $step['done'],
                            'border-slate-200 bg-white text-slate-400 group-hover:border-indigo-300 group-hover:text-indigo-500' => !$step['done'],
                        ])>
                            @if ($step['done'])
                                <i data-lucide="check" class="h-3.5 w-3.5"></i>
                            @else
                                <i data-lucide="{{ $step['icon'] }}" class="h-3.5 w-3.5"></i>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p @class(['text-sm font-semibold', 'line-through text-slate-400' => $step['done'], 'text-slate-900 group-hover:text-indigo-700' => !$step['done']])>
                                {{ $step['label'] }}
                            </p>
                            <p class="mt-0.5 text-xs leading-relaxed text-slate-400">{{ $step['desc'] }}</p>
                        </div>
                        @if (!$step['done'])
                            <i data-lucide="arrow-right" class="mt-1 h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-indigo-400"></i>
                        @endif
                    </a>
                @endforeach
            </div>
            <div class="border-t border-slate-100 px-6 py-3 text-right">
                <a href="#guide" class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-700">
                    <i data-lucide="book-open" class="h-3.5 w-3.5"></i> {{ __('Read the full guide') }}
                </a>
            </div>
        </div>
    @endunless

    <div class="mt-7 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                [
                    'label' => __('Wallet balance'),
                    'value' => \App\Support\Money::format($stats['balance']),
                    'icon' => 'wallet',
                    'tile' => 'from-indigo-500 to-violet-500',
                ],
                [
                    'label' => __('Entries collected'),
                    'value' => number_format($stats['entries']),
                    'icon' => 'database',
                    'tile' => 'from-violet-500 to-fuchsia-500',
                ],
                [
                    'label' => __('Datasets for sale'),
                    'value' => number_format($stats['published']),
                    'icon' => 'layers',
                    'tile' => 'from-sky-500 to-indigo-500',
                ],
                [
                    'label' => __('Avg quality score'),
                    'value' => $stats['quality'] ? number_format($stats['quality'], 2) : '-',
                    'icon' => 'gauge',
                    'tile' => 'from-fuchsia-500 to-pink-500',
                ],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                <div class="flex items-center justify-between">
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $card['tile'] }} text-white shadow-glow">
                        <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i>
                    </span>
                </div>
                <p class="mt-4 font-display text-2xl font-semibold tracking-tight text-slate-900">{{ $card['value'] }}</p>
                <p class="mt-0.5 text-sm text-slate-500">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- User guide teaser: the full interactive guide lives on the Settings page --}}
    @unless ($checklistDone)
        @php $guideSummary = \App\Support\GuideContent::sections(); @endphp
        <div class="mt-6 overflow-hidden rounded-2xl shadow-card">
            <div class="relative overflow-hidden bg-[#0b0f1c] px-6 py-6 sm:px-8 sm:py-7">
                <div class="pointer-events-none absolute -left-10 -top-10 h-56 w-56 rounded-full bg-violet-600/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-8 right-0 h-48 w-48 rounded-full bg-indigo-600/15 blur-3xl"></div>
                <div class="relative flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 shadow-[0_0_24px_rgba(139,92,246,0.5)]">
                            <i data-lucide="book-open" class="h-5 w-5 text-white"></i>
                        </div>
                        <div>
                            <h2 class="font-display text-lg font-bold tracking-tight text-white">{{ __('User Guide') }}</h2>
                            <p class="mt-0.5 text-sm text-slate-400">{{ __('8 short topics, from setup to selling.') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('settings') }}#guide"
                        class="dc-spectrum inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                        {{ __('Read the full guide') }} <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>
                <div class="relative mt-5 flex flex-wrap gap-2">
                    @foreach ($guideSummary as $section)
                        <span class="flex items-center gap-1.5 rounded-full bg-white/5 px-3 py-1.5 text-xs font-medium text-slate-300 ring-1 ring-white/10">
                            <i data-lucide="{{ $section['icon'] }}" class="h-3 w-3 text-slate-400"></i>
                            {{ $section['title'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endunless

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div data-tour="dashboard-activity" class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Recent activity') }}</h2>
                <a href="{{ route('transactions.index') }}"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('View transactions') }}</a>
            </div>
            <div class="mt-4 divide-y divide-slate-50">
                @forelse ($activities as $activity)
                    <div class="flex items-start gap-3 py-3.5">
                        <span
                            class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <i data-lucide="{{ $activityIcon[$activity->type] ?? 'circle-dot' }}" class="h-4 w-4"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-900">{{ $activity->title }}</p>
                            @if ($activity->description)
                                <p class="text-sm text-slate-500">{{ $activity->description }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="py-6 text-sm text-slate-400">{{ __('Nothing here yet.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
            <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Quick actions') }}</h2>
            <div class="mt-4 space-y-2.5">
                @php
                    $actions = [
                        ['route' => 'marketplace.index', 'icon' => 'compass', 'label' => __('Browse marketplace')],
                        ['route' => 'collections.create', 'icon' => 'plus', 'label' => __('Create a collection')],
                        ['route' => 'verification.index', 'icon' => 'shield-check', 'label' => __('Verify your identity')],
                        ['route' => 'wallet.index', 'icon' => 'wallet', 'label' => __('Top up wallet')],
                    ];
                @endphp
                @foreach ($actions as $action)
                    <a href="{{ route($action['route']) }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/40 hover:text-indigo-700">
                        <i data-lucide="{{ $action['icon'] }}" class="h-4 w-4 text-indigo-500"></i>
                        {{ $action['label'] }}
                        <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
