@extends('layouts.app')

@section('title', 'Marketplace')

@section('content')
    @php
        $gradients = [
            'from-indigo-500 via-violet-500 to-fuchsia-500',
            'from-sky-500 via-indigo-500 to-violet-500',
            'from-fuchsia-500 via-purple-500 to-indigo-500',
            'from-violet-600 via-indigo-500 to-sky-500',
            'from-rose-500 via-fuchsia-500 to-violet-500',
            'from-emerald-500 via-teal-500 to-sky-500',
        ];
        $qualityColor = fn($s) => $s === null
            ? 'bg-slate-100 text-slate-500'
            : ($s >= 0.8
                ? 'bg-emerald-50 text-emerald-700'
                : ($s >= 0.6
                    ? 'bg-amber-50 text-amber-700'
                    : 'bg-rose-50 text-rose-700'));
    @endphp

    <div data-tour="marketplace-header">
        <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">{{ __('Marketplace') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Discover refined, ready-to-use datasets from the DataCore community.') }}</p>
    </div>

    @if ($featured->count())
        <div class="mt-7" x-data="{
            active: 0,
            count: {{ $featured->count() }},
            timer: null,
            init() { if (this.count > 1) this.timer = setInterval(() => this.next(), 6000); },
            next() { this.active = (this.active + 1) % this.count; },
            prev() { this.active = (this.active - 1 + this.count) % this.count; },
            go(i) { this.active = i; }
        }">
            <div class="relative overflow-hidden rounded-3xl">
                <div class="flex transition-transform duration-500 ease-out"
                    :style="`transform: translateX(-${active * 100}%)`">
                    @foreach ($featured as $i => $item)
                        <div class="w-full shrink-0">
                            <div
                                class="relative overflow-hidden bg-gradient-to-br {{ $gradients[$i % count($gradients)] }} p-8 sm:p-12">
                                <div class="absolute -right-12 -top-12 h-56 w-56 rounded-full bg-white/15 blur-2xl"></div>
                                <div class="absolute bottom-0 left-1/3 h-40 w-40 rounded-full bg-black/10 blur-3xl"></div>
                                <div class="relative max-w-2xl px-5">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                                            <i data-lucide="flame" class="h-3.5 w-3.5"></i> {{ __('Featured') }}
                                        </span>
                                        @if ($item->category)
                                            <span
                                                class="rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur">{{ $item->category->name }}</span>
                                        @endif
                                    </div>
                                    <h2
                                        class="mt-4 font-display text-2xl font-semibold leading-tight text-white sm:text-3xl">
                                        {{ $item->title }}</h2>
                                    <p class="mt-2 line-clamp-2 max-w-xl text-sm text-white/80">
                                        {{ $item->description ?: __('A refined dataset ready for analysis.') }}</p>
                                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-white/90">
                                        <span
                                            class="inline-flex ite
                                        ms-center gap-1.5"><i
                                                data-lucide="database" class="h-4 w-4"></i>
                                            {{ number_format($item->entries_count) }} {{ __('rows') }}</span>
                                        @if ($item->quality_score !== null)
                                            <span class="inline-flex items-center gap-1.5"><i data-lucide="gauge"
                                                    class="h-4 w-4"></i> {{ number_format($item->quality_score, 2) }}
                                                {{ __('quality') }}</span>
                                        @endif
                                        <span class="inline-flex items-center gap-1.5 font-mono"><i data-lucide="user"
                                                class="h-4 w-4"></i> {{ $item->user->name }}</span>
                                    </div>
                                    <div class="mt-6 flex items-center gap-3">
                                        <a href="{{ route('marketplace.show', $item) }}"
                                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-slate-900 shadow-lg transition hover:bg-slate-100">
                                            {{ __('View dataset') }} <i data-lucide="arrow-right" class="h-4 w-4"></i>
                                        </a>
                                        <span
                                            class="rounded-xl bg-black/15 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur">
                                            {{ $item->price > 0 ? \App\Support\Money::format($item->price) : __('Free') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($featured->count() > 1)
                    <button @click="prev()"
                        class="absolute left-4 top-1/2 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur transition hover:bg-white/30 sm:flex">
                        <i data-lucide="chevron-left" class="h-5 w-5"></i>
                    </button>
                    <button @click="next()"
                        class="absolute right-4 top-1/2 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur transition hover:bg-white/30 sm:flex">
                        <i data-lucide="chevron-right" class="h-5 w-5"></i>
                    </button>
                    <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
                        @foreach ($featured as $i => $item)
                            <button @click="go({{ $i }})"
                                :class="active === {{ $i }} ? 'w-6 bg-white' : 'w-2 bg-white/50'"
                                class="h-2 rounded-full transition-all"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div id="results" class="mt-8 scroll-mt-24 flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('marketplace.index', ['sort' => $sort]) }}#results" @class([
                'rounded-full px-4 py-2 text-sm font-medium transition',
                'dc-spectrum text-white shadow-glow' => !$slug,
                'border border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600' => $slug,
            ])>
                {{ __('All') }}
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('marketplace.index', ['category' => $category->slug, 'sort' => $sort]) }}#results"
                    @class([
                        'rounded-full px-4 py-2 text-sm font-medium transition',
                        'dc-spectrum text-white shadow-glow' => $slug === $category->slug,
                        'border border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600' =>
                            $slug !== $category->slug,
                    ])>
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        @php
            $sortOptions = [
                'date'    => ['label' => __('Date'),    'icon' => 'calendar',        'default' => 'desc', 'labels' => ['desc' => __('Newest first'),   'asc' => __('Oldest first')]],
                'price'   => ['label' => __('Price'),   'icon' => 'tag',             'default' => 'asc',  'labels' => ['asc'  => __('Low to High'),     'desc' => __('High to Low')]],
                'rating'  => ['label' => __('Rating'),  'icon' => 'star',            'default' => 'desc', 'labels' => ['desc' => __('Highest rated'),   'asc' => __('Lowest rated')]],
                'reviews' => ['label' => __('Reviews'), 'icon' => 'message-circle',  'default' => 'desc', 'labels' => ['desc' => __('Most reviewed'),   'asc' => __('Fewest reviews')]],
            ];
            $activeOption = $sortOptions[(string) $sort] ?? ['labels' => []];
            $activeLabel  = $activeOption['labels'][(string) $dir] ?? __('Sort');
        @endphp

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
                <div class="p-1.5 space-y-0.5">
                    @foreach ($sortOptions as $key => $option)
                        @php
                            $isActive = $sort === $key;
                            $nextDir  = $isActive ? ($dir === 'desc' ? 'asc' : 'desc') : $option['default'];
                            $params   = array_filter(['category' => $slug, 'q' => $search, 'sort' => $key, 'dir' => $nextDir]);
                        @endphp
                        <a href="{{ route('marketplace.index', $params) }}#results" data-sort-link
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
        <p class="mt-4 text-sm text-slate-500">{{ __('Showing results for') }} <span
                class="font-semibold text-slate-700">"{{ $search }}"</span></p>
    @endif

    <div id="marketplace-grid" class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($collections as $collection)
            <a href="{{ route('marketplace.show', $collection) }}"
                class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="relative h-28 bg-gradient-to-br {{ $gradients[$collection->id % count($gradients)] }}">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/15 blur-xl"></div>
                    <div class="absolute left-4 top-4 flex gap-2">
                        <span
                            class="rounded-full bg-white/20 px-2.5 py-1 text-xs font-medium text-white backdrop-blur">{{ $collection->cleanState() === 'clean2' ? __('Full Clean') : ($collection->cleanState() === 'clean1' ? __('Half Clean') : __('Raw')) }}</span>
                    </div>
                    <span
                        class="absolute bottom-4 right-4 rounded-lg bg-black/20 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur">{{ $collection->price > 0 ? \App\Support\Money::format($collection->price) : __('Free') }}</span>
                </div>
                <div class="flex flex-1 flex-col p-5">
                    <div class="flex items-center gap-2">
                        @if ($collection->category)
                            <span
                                class="font-mono text-[11px] uppercase tracking-wide text-indigo-500">{{ $collection->category->name }}</span>
                        @endif
                        <span
                            class="ml-auto inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold {{ $qualityColor($collection->quality_score) }}">
                            <i data-lucide="gauge" class="h-3 w-3"></i>
                            {{ $collection->quality_score !== null ? number_format($collection->quality_score, 2) : 'N/A' }}
                        </span>
                    </div>
                    <h3 class="mt-2 font-display text-lg font-semibold text-slate-900 group-hover:text-indigo-600">
                        {{ $collection->title }}</h3>
                    <p class="mt-1 line-clamp-2 text-sm text-slate-500">
                        {{ $collection->description ?: __('A refined dataset from the DataCore community.') }}</p>
                    <div class="flex-1"></div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="dc-spectrum flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-semibold text-white">{{ strtoupper(mb_substr($collection->user->name, 0, 2)) }}</span>
                            <span class="text-xs text-slate-500">{{ $collection->user->name }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-400">
                            <span class="inline-flex items-center gap-1"><i data-lucide="database" class="h-3.5 w-3.5"></i>
                                {{ number_format($collection->entries_count) }}</span>
                            {{-- <span class="inline-flex items-center gap-1"><i data-lucide="star" class="h-3.5 w-3.5"></i> {{ $collection->reviews_count }}</span> --}}

                            @php
                                $rating = $collection->reviews_avg_rating ?? 0;
                            @endphp

                            <div class="flex items-center gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $i <= round($rating) ? 'fill-amber-400 text-amber-400' : 'fill-slate-200 text-slate-200' }}"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                @endfor

                                <span class="ml-1 text-xs text-slate-500">
                                    {{ number_format($rating, 1) }}
                                    ({{ $collection->reviews_count }})
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center">
                <i data-lucide="package-search" class="mx-auto h-8 w-8 text-slate-300"></i>
                <p class="mt-3 font-medium text-slate-600">{{ __('No datasets found') }}</p>
                <p class="text-sm text-slate-400">{{ __('Try a different category or search term.') }}</p>
            </div>
        @endforelse
    </div>

    @if ($collections->hasPages())
        <div id="marketplace-pagination" class="mt-8">{{ $collections->links() }}</div>
    @endif

    @push('scripts')
    <script>
    (function () {
        async function loadSort(url) {
            const grid = document.getElementById('marketplace-grid');
            const sortEl = document.getElementById('sort-dropdown');

            grid.style.transition = 'opacity 0.15s';
            grid.style.opacity = '0.4';
            grid.style.pointerEvents = 'none';

            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error();
                const doc = new DOMParser().parseFromString(await res.text(), 'text/html');

                const newGrid = doc.getElementById('marketplace-grid');
                if (newGrid) grid.innerHTML = newGrid.innerHTML;

                const newSort = doc.getElementById('sort-dropdown');
                if (newSort && sortEl) {
                    sortEl.replaceWith(newSort);
                    if (window.Alpine) window.Alpine.initTree(newSort);
                    bindSortLinks();
                }

                const pag = document.getElementById('marketplace-pagination');
                const newPag = doc.getElementById('marketplace-pagination');
                if (pag && newPag) pag.innerHTML = newPag.innerHTML;

                history.pushState({}, '', url);
                if (window.lucide) window.lucide.createIcons();
            } catch {
                window.location.href = url;
            } finally {
                const g = document.getElementById('marketplace-grid');
                if (g) { g.style.opacity = '1'; g.style.pointerEvents = ''; }
            }
        }

        function bindSortLinks() {
            document.querySelectorAll('[data-sort-link]').forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();
                    loadSort(link.href);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', bindSortLinks);
    })();
    </script>
    @endpush
@endsection
