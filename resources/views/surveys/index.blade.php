@extends('layouts.app')

@section('title', 'Surveys')

@section('content')
    @php
        $gradients = [
            'from-indigo-500 to-violet-500',
            'from-sky-500 to-indigo-500',
            'from-fuchsia-500 to-purple-500',
            'from-violet-600 to-sky-500',
            'from-emerald-400 to-teal-500',
            'from-amber-400 to-orange-500',
        ];
        $sortOptions = [
            'date'      => ['label' => __('Date'),      'icon' => 'calendar',   'default' => 'desc', 'labels' => ['desc' => __('Newest first'),   'asc' => __('Oldest first')]],
            'reward'    => ['label' => __('Reward'),    'icon' => 'gift',       'default' => 'desc', 'labels' => ['desc' => __('Highest reward'), 'asc' => __('Lowest reward')]],
            'responses' => ['label' => __('Responses'), 'icon' => 'users',      'default' => 'desc', 'labels' => ['desc' => __('Most responses'), 'asc' => __('Fewest responses')]],
        ];
        $activeOption = $sortOptions[(string) $sort] ?? ['labels' => []];
        $activeLabel  = $activeOption['labels'][(string) $dir] ?? __('Sort');
    @endphp

    {{-- Header --}}
    <div data-tour="surveys-header" class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">{{ __('Open Surveys') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Fill surveys to contribute data and earn rewards for every entry you submit.') }}</p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
            <i data-lucide="gift" class="h-4 w-4"></i>
            {{ __('Earn rewards per entry') }}
        </span>
    </div>

    {{-- Verification notice --}}
    @php
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();
        $authUser->loadMissing('verification');
        $isVerified = $authUser->verification?->status === 'verified';
    @endphp

    @if (!$isVerified)
        <div class="mt-6 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
            <i data-lucide="shield-alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600"></i>
            <div>
                <p class="text-sm font-semibold text-amber-800">{{ __('Identity verification required') }}</p>
                <p class="mt-0.5 text-sm text-amber-700">{{ __('You need to verify your identity before filling any survey.') }}</p>
                <a href="{{ route('verification.index') }}" class="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold text-amber-800 underline underline-offset-2 hover:text-amber-900">
                    {{ __('Verify now') }} <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                </a>
            </div>
        </div>
    @endif

    {{-- Search bar --}}
    <form method="GET" action="{{ route('surveys.index') }}" id="survey-search-form" class="mt-7 relative">
        <input type="hidden" name="category" value="{{ $slug }}">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="dir" value="{{ $dir }}">
        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
            <i data-lucide="search" class="h-4 w-4"></i>
        </span>
        <input type="text" name="q" value="{{ $search }}"
            placeholder="{{ __('Search surveys…') }}"
            class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
    </form>

    {{-- Filters + Sort --}}
    <div class="mt-4 flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('surveys.index', array_filter(['sort' => $sort, 'dir' => $dir, 'q' => $search])) }}"
                @class([
                    'rounded-full px-4 py-2 text-sm font-medium transition',
                    'dc-spectrum text-white shadow-glow' => !$slug,
                    'border border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600' => $slug,
                ])>
                {{ __('All') }}
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('surveys.index', array_filter(['category' => $category->slug, 'sort' => $sort, 'dir' => $dir, 'q' => $search])) }}"
                    @class([
                        'rounded-full px-4 py-2 text-sm font-medium transition',
                        'dc-spectrum text-white shadow-glow' => $slug === $category->slug,
                        'border border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600' => $slug !== $category->slug,
                    ])>
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <div id="sort-dropdown" x-data="{ open: false }" @click.outside="open = false" class="relative ml-auto">
            <button @click="open = !open"
                class="inline-flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                <i data-lucide="arrow-up-down" class="h-3.5 w-3.5 text-slate-400"></i>
                <span>{{ $activeLabel }}</span>
                <i data-lucide="chevron-down" class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"></i>
            </button>

            <div x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full z-30 mt-2 w-52 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                style="display: none;">
                <div class="space-y-0.5 p-1.5">
                    @foreach ($sortOptions as $key => $option)
                        @php
                            $isActive = $sort === $key;
                            $nextDir  = $isActive ? ($dir === 'desc' ? 'asc' : 'desc') : $option['default'];
                            $params   = array_filter(['category' => $slug, 'q' => $search, 'sort' => $key, 'dir' => $nextDir]);
                        @endphp
                        <a href="{{ route('surveys.index', $params) }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ $isActive ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i data-lucide="{{ $option['icon'] }}" class="h-4 w-4 {{ $isActive ? 'text-indigo-500' : 'text-slate-400' }}"></i>
                            <div class="flex-1">
                                <div>{{ $option['label'] }}</div>
                                <div class="text-[11px] font-normal {{ $isActive ? 'text-indigo-400' : 'text-slate-400' }}">
                                    {{ $isActive ? $option['labels'][$dir] : $option['labels'][$option['default']] }}
                                </div>
                            </div>
                            @if ($isActive)
                                <i data-lucide="{{ $dir === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="h-3.5 w-3.5 text-indigo-500"></i>
                            @else
                                <i data-lucide="chevrons-up-down" class="h-3.5 w-3.5 text-slate-300"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-4 py-2.5">
                    <p class="text-[11px] text-slate-400">{{ __('Click the active sort again to reverse order.') }}</p>
                </div>
            </div>
        </div>
    </div>

    @if ($search)
        <p class="mt-4 text-sm text-slate-500">
            {{ __('Showing results for') }} <span class="font-semibold text-slate-700">"{{ $search }}"</span>
        </p>
    @endif

    {{-- Survey grid --}}
    @if ($surveys->count())
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($surveys as $survey)
                <div class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card transition hover:border-indigo-200 hover:shadow-lg">
                    <div class="relative overflow-hidden bg-[#0b0f1c] px-5 py-4">
                        <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-violet-600/20 blur-2xl"></div>
                        <div class="relative flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $gradients[$survey->id % count($gradients)] }} text-white shadow-glow">
                                <i data-lucide="clipboard-list" class="h-5 w-5"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-display text-sm font-semibold text-white">{{ $survey->title }}</p>
                                @if ($survey->category)
                                    <p class="font-mono text-[10px] uppercase tracking-wider text-violet-300">{{ $survey->category->name }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        @if ($survey->description)
                            <p class="line-clamp-2 text-sm text-slate-500">{{ $survey->description }}</p>
                        @endif

                        <div class="mt-4 flex items-center gap-4 text-xs text-slate-500">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="list" class="h-3.5 w-3.5 text-slate-400"></i>
                                {{ $survey->questions_count }} {{ $survey->questions_count == 1 ? __('question') : __('questions') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="users" class="h-3.5 w-3.5 text-slate-400"></i>
                                {{ number_format($survey->entries_count) }} {{ $survey->entries_count == 1 ? __('response') : __('responses') }}
                            </span>
                        </div>

                        <div class="mt-auto pt-5">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                @if ($survey->reward > 0)
                                    <span class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        <i data-lucide="gift" class="h-3.5 w-3.5"></i>
                                        {{ __('Earn') }} {{ \App\Support\Money::format($survey->reward) }}
                                    </span>
                                @else
                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-400">
                                        {{ __('No reward') }}
                                    </span>
                                @endif
                                <p class="min-w-0 truncate text-[11px] text-slate-400">{{ __('by') }} {{ $survey->user->name }}</p>
                            </div>
                            <a href="{{ route('entries.create', $survey) }}"
                                @class([
                                    'mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                    'dc-spectrum text-white shadow-glow hover:brightness-110' => $isVerified,
                                    'cursor-not-allowed border border-slate-200 bg-slate-50 text-slate-400' => !$isVerified,
                                ])
                                @if (!$isVerified) onclick="return false" @endif>
                                <i data-lucide="{{ $isVerified ? 'pencil-line' : 'lock' }}" class="h-4 w-4"></i>
                                {{ $isVerified ? __('Fill Survey') : __('Verify to Contribute') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($surveys->hasPages())
            <div class="mt-8">
                {{ $surveys->links() }}
            </div>
        @endif
    @else
        <div class="mt-10 rounded-2xl border border-slate-200/70 bg-white py-16 text-center shadow-card">
            <i data-lucide="clipboard-list" class="mx-auto h-8 w-8 text-slate-300"></i>
            <p class="mt-3 font-display font-medium text-slate-600">{{ __('No open surveys right now') }}</p>
            <p class="mt-1 text-sm text-slate-400">
                @if ($search || $slug)
                    {{ __('Try clearing your filters or search term.') }}
                @else
                    {{ __('Check back soon. New surveys are added regularly.') }}
                @endif
            </p>
            @if ($search || $slug)
                <a href="{{ route('surveys.index') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                    <i data-lucide="x" class="h-3.5 w-3.5"></i> {{ __('Clear filters') }}
                </a>
            @endif
        </div>
    @endif
@endsection
