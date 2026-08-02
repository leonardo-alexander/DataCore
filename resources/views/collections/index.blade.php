@extends('layouts.app')

@section('title', 'Collections')

@section('content')
    <div data-tour="collections-list" x-data="{ deleteOpen: false, deleteUrl: '', deleteLabel: '' }"
        @open-delete.window="deleteOpen = true; deleteUrl = $event.detail.url; deleteLabel = $event.detail.label">

    @php
        $gradients = [
            'from-indigo-500 via-violet-500 to-fuchsia-500',
            'from-sky-500 via-indigo-500 to-violet-500',
            'from-fuchsia-500 via-purple-500 to-indigo-500',
            'from-violet-600 via-indigo-500 to-sky-500',
            'from-rose-500 via-fuchsia-500 to-violet-500',
            'from-emerald-500 via-teal-500 to-sky-500',
        ];

        $tabUrl = fn ($t) => request()->fullUrlWithQuery(['tab' => $t, 'page' => null]);

        $sortOptions = [
            'date'    => ['label' => __('Date'),    'icon' => 'calendar',        'asc' => __('Oldest first'),    'desc' => __('Newest first'),    'default' => 'desc'],
            'price'   => ['label' => __('Price'),   'icon' => 'tag',             'asc' => __('Low to High'),     'desc' => __('High to Low'),     'default' => 'desc'],
            'entries' => ['label' => __('Entries'), 'icon' => 'users',           'asc' => __('Fewest first'),    'desc' => __('Most first'),      'default' => 'desc'],
            'reviews' => ['label' => __('Reviews'), 'icon' => 'message-circle',  'asc' => __('Fewest first'),    'desc' => __('Most reviewed'),   'default' => 'desc'],
        ];

        $sortUrl = fn ($s) => request()->fullUrlWithQuery([
            'sort' => $s,
            'dir'  => $s === $sort ? ($dir === 'desc' ? 'asc' : 'desc') : $sortOptions[$s]['default'],
            'page' => null,
        ]);

        $statusOptions = [
            ''          => ['label' => __('Any status'), 'icon' => 'sliders-horizontal'],
            'draft'     => ['label' => __('Draft'),       'icon' => 'file-text'],
            'ongoing'   => ['label' => __('Collecting'),  'icon' => 'radio'],
            'published' => ['label' => __('For sale'),    'icon' => 'tag'],
        ];
    @endphp

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">{{ __('Collections') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Your datasets.') }}</p>
        </div>
        <a href="{{ route('collections.create') }}"
            class="dc-spectrum inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
            <i data-lucide="plus" class="h-4 w-4"></i> {{ __('New collection') }}
        </a>
    </div>

    {{-- Toolbar --}}
    <div id="collections-toolbar" class="mt-6 flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach ([
                ['key' => 'all',       'label' => __('All'),         'count' => $ownCount + $boughtCount],
                ['key' => 'own',       'label' => __('My datasets'), 'count' => $ownCount],
                ['key' => 'purchased', 'label' => __('Purchased'),   'count' => $boughtCount],
            ] as $t)
                <a href="{{ $tabUrl($t['key']) }}" @class([
                    'inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-medium transition',
                    'dc-spectrum text-white shadow-glow' => $tab === $t['key'],
                    'border border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600' => $tab !== $t['key'],
                ])>
                    {{ $t['label'] }}
                    <span @class([
                        'rounded-full px-1.5 py-0.5 text-[10px] font-semibold',
                        'bg-white/25 text-white' => $tab === $t['key'],
                        'bg-slate-100 text-slate-500' => $tab !== $t['key'],
                    ])>{{ $t['count'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Status filter --}}
        @if ($tab !== 'purchased')
            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button @click="open = !open"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <i data-lucide="{{ $statusOptions[$status]['icon'] }}" class="h-3.5 w-3.5 text-slate-400"></i>
                    {{ $statusOptions[$status]['label'] }}
                    <i data-lucide="chevron-down" class="h-3.5 w-3.5 text-slate-300 transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 top-full z-30 mt-2 w-48 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl">
                    @foreach ($statusOptions as $val => $opt)
                        @php $isActive = $status === $val; @endphp
                        <a href="{{ request()->fullUrlWithQuery(['status' => $val ?: null, 'page' => null]) }}"
                            @class([
                                'flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm transition',
                                'bg-indigo-50 font-semibold text-indigo-700' => $isActive,
                                'text-slate-600 hover:bg-slate-50' => ! $isActive,
                            ])>
                            <i data-lucide="{{ $opt['icon'] }}" class="h-4 w-4 {{ $isActive ? 'text-indigo-500' : 'text-slate-400' }}"></i>
                            {{ $opt['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Search --}}
        <form method="GET" action="{{ route('collections.index') }}" class="flex flex-1 items-center gap-2 min-w-45">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $dir }}">
            @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
            <div class="relative flex-1">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Search collections…') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-8 pr-4 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            </div>
            @if ($search)
                <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}"
                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="h-3.5 w-3.5"></i>
                </a>
            @endif
        </form>

        {{-- Sort dropdown --}}
        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
            <button @click="open = !open"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                <i data-lucide="{{ ($sortOptions[$sort] ?? $sortOptions['date'])['icon'] }}" class="h-3.5 w-3.5 text-slate-400"></i>
                {{ ($sortOptions[$sort] ?? $sortOptions['date'])['label'] }}
                <i data-lucide="{{ $dir === 'desc' ? 'arrow-down' : 'arrow-up' }}" class="h-3 w-3 text-indigo-400"></i>
                <i data-lucide="chevron-down" class="h-3.5 w-3.5 text-slate-300 transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full z-30 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl">
                @foreach ($sortOptions as $key => $opt)
                    @php $isActive = $sort === $key; @endphp
                    <a href="{{ $sortUrl($key) }}" @class([
                        'flex items-center gap-3 rounded-xl px-3 py-2.5 transition',
                        'bg-indigo-50'   => $isActive,
                        'hover:bg-slate-50' => ! $isActive,
                    ])>
                        <span @class([
                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                            'bg-indigo-100 text-indigo-600' => $isActive,
                            'bg-slate-100 text-slate-400'   => ! $isActive,
                        ])>
                            <i data-lucide="{{ $opt['icon'] }}" class="h-4 w-4"></i>
                        </span>
                        <span class="flex-1">
                            <span @class(['block text-sm font-semibold', 'text-indigo-700' => $isActive, 'text-slate-700' => ! $isActive])>
                                {{ $opt['label'] }}
                            </span>
                            <span class="block text-xs text-slate-400">
                                {{ $isActive ? $opt[$dir] : $opt[$opt['default']] }}
                            </span>
                        </span>
                        @if ($isActive)
                            <i data-lucide="{{ $dir === 'desc' ? 'arrow-down' : 'arrow-up' }}" class="h-4 w-4 shrink-0 text-indigo-400"></i>
                        @else
                            <i data-lucide="arrow-up-down" class="h-4 w-4 shrink-0 text-slate-300"></i>
                        @endif
                    </a>
                @endforeach
                <p class="mt-1 px-3 pb-1 text-xs text-slate-400">{{ __('Click the active sort again to reverse order.') }}</p>
            </div>
        </div>
    </div>

    @if ($search)
        <p class="mt-4 text-sm text-slate-500">Results for <span class="font-semibold text-slate-700">"{{ $search }}"</span></p>
    @endif

    {{-- Grid --}}
    @if ($collections->count())
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($collections as $collection)
                @php
                    $isOwn    = $collection->user_id === $userId;
                    $state    = $collection->cleanState();
                    $gradient = $gradients[$collection->id % count($gradients)];
                    $purchase = $isOwn ? null : $collection->purchases->first();
                @endphp

                <div class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card transition hover:-translate-y-0.5 hover:shadow-lg">

                    {{-- Gradient banner --}}
                    <div class="relative h-28 bg-gradient-to-br {{ $gradient }}">
                        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/15 blur-xl"></div>

                        {{-- Top-left: pipeline state --}}
                        <div class="absolute left-4 top-4 flex gap-2">
                            <span class="rounded-full bg-white/20 px-2.5 py-1 text-xs font-medium text-white backdrop-blur">
                                {{ $state === 'clean2' ? __('Full Clean') : ($state === 'clean1' ? __('Half Clean') : __('Raw')) }}
                            </span>
                            <span class="rounded-full bg-white/15 px-2.5 py-1 text-xs font-medium text-white/90 backdrop-blur">
                                {{ $collection->type === 'upload' ? __('CSV') : __('Survey') }}
                            </span>
                        </div>

                        {{-- Top-right: ownership badge --}}
                        @if ($isOwn)
                            <span @class([
                                'absolute right-4 top-4 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold backdrop-blur',
                                'bg-fuchsia-500/30 text-fuchsia-100' => $collection->status === 'published',
                                'bg-violet-500/30 text-violet-100'   => $collection->status === 'ongoing',
                                'bg-slate-500/30 text-slate-100'     => $collection->status === 'draft',
                            ])>
                                <span @class([
                                    'h-1.5 w-1.5 rounded-full',
                                    'bg-fuchsia-300' => $collection->status === 'published',
                                    'bg-violet-300'  => $collection->status === 'ongoing',
                                    'bg-slate-300'   => $collection->status === 'draft',
                                ])></span>
                                {{ ['draft' => __('Draft'), 'ongoing' => __('Collecting'), 'published' => __('For sale')][$collection->status] ?? __(ucfirst($collection->status)) }}
                            </span>
                        @else
                            <span class="absolute right-4 top-4 inline-flex items-center gap-1 rounded-full bg-indigo-500/30 px-2.5 py-1 text-xs font-semibold text-indigo-100 backdrop-blur">
                                <i data-lucide="shopping-bag" class="h-3 w-3"></i> {{ __('Purchased') }}
                            </span>
                        @endif

                        {{-- Bottom-right: price --}}
                        <span class="absolute bottom-4 right-4 rounded-lg bg-black/20 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur">
                            @if ($isOwn)
                                {{ $collection->price > 0 ? \App\Support\Money::format($collection->price) : __('Free') }}
                            @else
                                Paid {{ \App\Support\Money::format($purchase?->amount ?? 0) }}
                            @endif
                        </span>
                    </div>

                    {{-- Body --}}
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start gap-2">
                            @if ($collection->category)
                                <span class="font-mono text-[11px] uppercase tracking-wide text-indigo-500">{{ $collection->category->name }}</span>
                            @endif
                            @if ($isOwn && $collection->reward > 0)
                                <span class="ml-auto inline-flex items-center gap-1 rounded-md bg-violet-50 px-2 py-0.5 text-[10px] font-semibold text-violet-700">
                                    <i data-lucide="gift" class="h-3 w-3"></i>
                                    {{ \App\Support\Money::format($collection->reward) }}/entry
                                </span>
                            @endif
                        </div>

                        <h3 class="mt-2 font-display text-lg font-semibold text-slate-900 group-hover:text-indigo-600">
                            {{ $collection->title }}
                        </h3>

                        <div class="flex-1"></div>

                        {{-- Stats row --}}
                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-400">
                            @if ($isOwn && $collection->respondent_target)
                                @php $pct = min(100, round($collection->entries_count / $collection->respondent_target * 100)); @endphp
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="users" class="h-3.5 w-3.5"></i>
                                    {{ number_format($collection->entries_count) }}/{{ number_format($collection->respondent_target) }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="database" class="h-3.5 w-3.5"></i>
                                    {{ number_format($collection->entries_count) }} {{ __('rows') }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1">
                                <i data-lucide="message-circle" class="h-3.5 w-3.5"></i>
                                {{ $collection->reviews_count }} {{ __('reviews') }}
                            </span>
                        </div>

                        {{-- Target progress bar --}}
                        @if ($isOwn && $collection->respondent_target)
                            @php $pct = min(100, round($collection->entries_count / $collection->respondent_target * 100)); @endphp
                            <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-1 rounded-full bg-indigo-400" style="width:{{ $pct }}%"></div>
                            </div>
                        @endif

                        {{-- Action buttons --}}
                        <div class="mt-3 flex items-center gap-2" x-data="{ open: false }">
                            <a href="{{ route('marketplace.show', $collection) }}"
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-indigo-200 hover:text-indigo-600">
                                <i data-lucide="eye" class="h-3.5 w-3.5"></i> {{ __('View') }}
                            </a>
                            @if ($isOwn)
                                <a href="{{ route('collections.edit', $collection) }}"
                                    class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-indigo-200 hover:text-indigo-600">
                                    <i data-lucide="pencil" class="h-3.5 w-3.5"></i> {{ __('Edit') }}
                                </a>
                                <div class="relative">
                                    <button @click="open = !open"
                                        class="flex h-7.5 w-7.5 items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition hover:border-slate-300 hover:text-slate-600">
                                        <i data-lucide="more-horizontal" class="h-3.5 w-3.5"></i>
                                    </button>
                                    <div x-show="open" x-cloak @click.outside="open = false" x-transition
                                        class="absolute bottom-full right-0 z-10 mb-1 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl">
                                        <a href="{{ route('collections.analytics', $collection) }}"
                                            class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                                            <i data-lucide="bar-chart-2" class="h-4 w-4 text-slate-400"></i> {{ __('Analytics') }}
                                        </a>
                                        <a href="{{ route('collections.export', $collection) }}"
                                            class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                                            <i data-lucide="download" class="h-4 w-4 text-slate-400"></i> {{ __('Export CSV') }}
                                        </a>
                                        @if ($collection->status === 'ongoing' && $collection->survey_ended_at === null)
                                            <form method="POST" action="{{ route('collections.end-survey', $collection) }}"
                                                @submit.prevent="if(confirm('End this survey? Unused budget will be refunded.')) $el.submit()">
                                                @csrf
                                                <button type="submit"
                                                    class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-amber-600 hover:bg-amber-50">
                                                    <i data-lucide="square-x" class="h-4 w-4"></i> {{ __('End survey') }}
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button"
                                            @click="window.dispatchEvent(new CustomEvent('open-delete', { detail: { url: '{{ route('collections.destroy', $collection) }}', label: '{{ addslashes($collection->title) }}' } }))"
                                            class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i> {{ __('Delete') }}
                                        </button>
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('collections.export', $collection) }}"
                                    class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-indigo-200 hover:text-indigo-600">
                                    <i data-lucide="download" class="h-3.5 w-3.5"></i> Export
                                </a>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        @if ($collections->hasPages())
            <div class="mt-8">{{ $collections->links() }}</div>
        @endif
    @else
        <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center">
            <i data-lucide="{{ $tab === 'purchased' ? 'shopping-bag' : 'layers' }}" class="mx-auto h-8 w-8 text-slate-300"></i>
            <p class="mt-3 font-display font-medium text-slate-600">
                @if ($search) {{ __('No results for ":search"', ['search' => $search]) }}
                @elseif ($tab === 'purchased') {{ __('No purchased datasets yet') }}
                @else {{ __('No collections yet') }}
                @endif
            </p>
            <p class="text-sm text-slate-400">
                @if ($search) {{ __('Try a different search term.') }}
                @elseif ($tab === 'purchased') {!! __('Browse the :link to find datasets.', ['link' => '<a href="' . route('marketplace.index') . '" class="text-indigo-600 hover:underline">' . __('marketplace') . '</a>']) !!}
                @else {{ __('Create your first collection to start gathering data.') }}
                @endif
            </p>
            @if (!$search && $tab !== 'purchased')
                <a href="{{ route('collections.create') }}"
                    class="dc-spectrum mt-5 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                    <i data-lucide="plus" class="h-4 w-4"></i> {{ __('New collection') }}
                </a>
            @endif
        </div>
    @endif

    @include('partials.confirm_delete')
    </div>
@endsection
