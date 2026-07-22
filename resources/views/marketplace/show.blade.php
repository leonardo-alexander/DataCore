@extends('layouts.app')
@section('title', $collection->title)
@section('content')
    @php
        $state = $collection->cleanState();
        $report = $collection->cleaning_report['quality'] ?? null;
        $avgRating = $collection->averageRating();
        $fee = (int) config('datacore.clean2_fee');
    @endphp

    <a href="{{ route('marketplace.index') }}"
        class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-900">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Back to marketplace
    </a>

    <div class="mt-5 grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($collection->category)
                        <span
                            class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">{{ $collection->category->name }}</span>
                    @endif
                    <span @class([
                        'rounded-full px-3 py-1 text-xs font-semibold',
                        'bg-violet-50 text-violet-700' => $state === 'clean2',
                        'bg-indigo-50 text-indigo-700' => $state === 'clean1',
                        'bg-slate-100 text-slate-600' => $state === 'raw',
                    ])>
                        {{ $state === 'clean2' ? 'Full Clean · Premium' : ($state === 'clean1' ? 'Half Clean · Free tier' : 'Raw data') }}
                    </span>
                </div>
                <h1 class="mt-3 font-display text-3xl font-semibold tracking-tight text-slate-900">{{ $collection->title }}
                </h1>
                <p class="mt-2 max-w-2xl text-slate-600">
                    {{ $collection->description ?: 'A refined dataset from the DataCore community.' }}</p>

                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        <span
                            class="dc-spectrum flex h-7 w-7 items-center justify-center rounded-full text-[11px] font-semibold text-white">{{ strtoupper(mb_substr($collection->user->name, 0, 2)) }}</span>
                        <span class="font-medium text-slate-700">{{ $collection->user->name }}</span>
                        @if ($collection->user->isVerified())
                            <span class="inline-flex items-center gap-1 text-emerald-600"><i data-lucide="badge-check"
                                    class="h-4 w-4"></i></span>
                        @endif
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-amber-500">
                        <i data-lucide="star" class="h-4 w-4 fill-current"></i>
                        <span class="font-semibold text-slate-700">{{ $avgRating ?: '-' }}</span>
                        <span class="text-slate-400">({{ $collection->reviews_count }})</span>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                @php
                    $metrics = [
                        ['label' => 'Rows', 'value' => number_format($collection->entries_count), 'icon' => 'database'],
                        [
                            'label' => 'Quality',
                            'value' =>
                                $collection->quality_score !== null
                                    ? number_format($collection->quality_score, 2)
                                    : '-',
                            'icon' => 'gauge',
                        ],
                        ['label' => 'Fields', 'value' => $collection->questions->count(), 'icon' => 'columns-3'],
                    ];
                @endphp
                @foreach ($metrics as $m)
                    <div class="rounded-2xl border border-slate-200/70 bg-white p-4 shadow-card">
                        <i data-lucide="{{ $m['icon'] }}" class="h-4 w-4 text-indigo-500"></i>
                        <p class="mt-2 font-display text-xl font-semibold text-slate-900">{{ $m['value'] }}</p>
                        <p class="text-xs text-slate-500">{{ $m['label'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card"
                x-data="{ tab: '{{ $state === 'clean2' ? 'clean2' : 'clean1' }}' }">
                <div class="flex items-center gap-1 border-b border-slate-100 px-4 pt-3">
                    @php
                        $tabs = [];
                        if ($owns) {
                            $tabs[] = ['key' => 'raw', 'label' => 'Raw', 'badge' => null, 'badgeClass' => ''];
                        }
                        $tabs[] = [
                            'key' => 'clean1',
                            'label' => 'Half Clean',
                            'badge' => 'Free',
                            'badgeClass' => 'bg-emerald-100 text-emerald-700',
                        ];
                        $tabs[] = [
                            'key' => 'clean2',
                            'label' => 'Full Clean',
                            'badge' => 'Pro',
                            'badgeClass' => 'bg-violet-100 text-violet-700',
                        ];
                    @endphp
                    @foreach ($tabs as $t)
                        <button @click="tab = '{{ $t['key'] }}'"
                            :class="tab === '{{ $t['key'] }}' ? 'border-indigo-500 text-slate-900' :
                                'border-transparent text-slate-400 hover:text-slate-600'"
                            class="flex items-center gap-2 border-b-2 px-3 pb-3 text-sm font-medium transition">
                            {{ $t['label'] }}
                            @if ($t['badge'])
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $t['badgeClass'] }}">{{ $t['badge'] }}</span>
                            @endif
                        </button>
                    @endforeach
                    <div class="ml-auto pb-2">
                        <span
                            class="hidden items-center gap-1.5 font-mono text-[10px] uppercase tracking-wide text-slate-400 sm:inline-flex">
                            <i data-lucide="arrow-right" class="h-3 w-3"></i> Refinement preview
                        </span>
                    </div>
                </div>

                <div class="bg-[#0b0f1c] p-5">
                    @if ($owns)
                        <div x-show="tab === 'raw'" x-cloak>
                            @forelse ($preview['raw'] as $row)
                                <pre class="mb-2 overflow-x-auto rounded-lg bg-white/5 p-3 font-mono text-xs leading-relaxed text-slate-300">{{ json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            @empty
                                <p class="py-6 text-center text-sm text-slate-500">No entries yet.</p>
                            @endforelse
                        </div>
                    @endif
                    <div x-show="tab === 'clean1'" @if ($owns) x-cloak @endif>
                        @forelse ($preview['clean1'] as $row)
                            <pre class="mb-2 overflow-x-auto rounded-lg bg-white/5 p-3 font-mono text-xs leading-relaxed text-emerald-200">{{ json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        @empty
                            <div class="py-8 text-center">
                                <i data-lucide="sparkles" class="mx-auto h-6 w-6 text-slate-600"></i>
                                <p class="mt-2 text-sm text-slate-400">Half Clean has not been run yet.</p>
                            </div>
                        @endforelse
                    </div>
                    <div x-show="tab === 'clean2'" x-cloak>
                        @if (!$preview['clean2']->count())
                            <div class="py-8 text-center">
                                <i data-lucide="lock" class="mx-auto h-6 w-6 text-slate-600"></i>
                                <p class="mt-2 text-sm text-slate-400">Full Clean has not been run yet.</p>
                            </div>
                        @elseif ($owns || $purchased)
                            @foreach ($preview['clean2'] as $row)
                                <pre class="mb-2 overflow-x-auto rounded-lg bg-white/5 p-3 font-mono text-xs leading-relaxed text-violet-200">{{ json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            @endforeach
                        @else
                            <div class="relative">
                                <pre class="overflow-hidden rounded-lg bg-white/5 p-3 font-mono text-xs leading-relaxed text-violet-200/40 blur-sm">{{ json_encode($preview['clean2']->first(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <i data-lucide="lock" class="h-6 w-6 text-violet-300"></i>
                                    <p class="mt-2 text-sm font-medium text-white">Full Clean data is premium</p>
                                    <p class="text-xs text-slate-400">Purchase this dataset to unlock fully refined rows.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Reviews</h2>
                    <span class="inline-flex items-center gap-1.5 text-sm text-amber-500"><i data-lucide="star"
                            class="h-4 w-4 fill-current"></i> <span
                            class="font-semibold text-slate-700">{{ $avgRating ?: '-' }}</span></span>
                </div>

                <div class="mt-4 divide-y divide-slate-50">
                    @forelse ($collection->reviews as $review)
                        <div class="py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                                        {{ strtoupper(mb_substr($review->user->name, 0, 2)) }}
                                    </span>

                                    <span class="text-sm font-medium text-slate-800">
                                        {{ $review->user->name }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">

                                    {{-- Rating --}}
                                    <div class="flex items-center gap-0.5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i data-lucide="star"
                                                class="h-3.5 w-3.5 {{ $i <= $review->rating ? 'fill-amber-400 text-amber-400' : 'text-slate-200' }}"></i>
                                        @endfor
                                    </div>

                                    @if (auth()->id() == $review->user_id)
                                        <div class="relative" x-data="{ open: false }">

                                            <button @click="open = !open"
                                                class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100">
                                                <i data-lucide="ellipsis-vertical" class="h-4 w-4"></i>
                                            </button>

                                            <div x-show="open" x-cloak @click.outside="open = false" x-transition
                                                class="absolute right-0 z-10 mt-1 w-36 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl">

                                                <button
                                                    @click="
                                                        open = false;
                                                        window.dispatchEvent(new CustomEvent('edit-review', {
                                                            detail: {
                                                                rating: {{ $review->rating }},
                                                                comment: @js($review->comment)
                                                            }
                                                        }))
                                                    "
                                                    class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                                                    <i data-lucide="pencil" class="h-4 w-4"></i>
                                                    Edit
                                                </button>

                                                <button type="button"
                                                    @click="open = false; window.dispatchEvent(new CustomEvent('open-delete', { detail: { url: '{{ route('marketplace.review.delete', $collection) }}', label: 'review' } }))"
                                                    class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 text-left">
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                            @if ($review->comment)
                                <p class="mt-2 text-sm text-slate-600">{{ $review->comment }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="py-6 text-sm text-slate-400">No reviews yet. Be the first to review this dataset.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="space-y-5 lg:sticky lg:top-24">
                {{-- CARD PRICE --}}
                <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                    <p class="text-sm text-slate-500">Price</p>
                    <p class="mt-1 font-display text-3xl font-semibold text-slate-900">
                        {{ $collection->price > 0 ? \App\Support\Money::format($collection->price) : 'Free' }}</p>
                    @if ($owns)
                        <div
                            class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 text-sm font-medium text-indigo-700">
                            <i data-lucide="crown" class="mr-1 inline h-4 w-4"></i> This is your dataset
                        </div>
                        <a href="{{ route('collections.edit', $collection) }}"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i data-lucide="pencil" class="h-4 w-4"></i> Edit collection
                        </a>
                        <a href="{{ route('collections.export', $collection) }}"
                            class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50">
                            <i data-lucide="download" class="h-4 w-4"></i> Export CSV
                        </a>
                    @elseif ($purchased)
                        <div
                            class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm font-medium text-emerald-700">
                            <i data-lucide="check-circle-2" class="mr-1 inline h-4 w-4"></i> You own this dataset
                        </div>
                        <a href="{{ route('collections.export', $collection) }}"
                            class="dc-spectrum mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                            <i data-lucide="download" class="h-4 w-4"></i> Download CSV
                        </a>
                    @else
                        {{-- FORM BUY --}}
                        <form method="POST" action="{{ route('marketplace.purchase', $collection) }}" class="mt-4">
                            @csrf
                            <button
                                class="dc-spectrum inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                                <i data-lucide="{{ $collection->price > 0 ? 'shopping-cart' : 'download' }}"
                                    class="h-4 w-4"></i>
                                {{ $collection->price > 0 ? 'Buy now' : 'Get for free' }}
                            </button>
                        </form>
                        <ul class="mt-4 space-y-2 text-sm text-slate-500">
                            @foreach (['Full dataset access', 'Full Clean refined rows', 'One-click CSV export'] as $feat)
                                <li class="flex items-center gap-2"><i data-lucide="check"
                                        class="h-4 w-4 text-emerald-500"></i> {{ $feat }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if ($purchased && $canReview)
                    <div x-data="{ openReview: false, rating: 0 }" class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                        <h3 class="font-semibold text-slate-800">Rate this dataset</h3>
                        <p class="mt-1 text-xs text-slate-500">Share your experience to help others</p>
                        <div class="mt-5 flex justify-center gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    @click="
                                        rating={{ $i }};
                                        openReview=true;
                                    "
                                    class="transition hover:scale-125">
                                    <svg class="h-8 w-8"
                                        :class="rating >= {{ $i }} ?
                                            'fill-amber-400 text-amber-400' :
                                            'fill-slate-200 text-slate-200'"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                </button>
                            @endfor
                        </div>
                        <p class="mt-4 text-center text-xs text-slate-400">Click a star to rate</p>
                        @include('partials.review_popup', [
                            'collection' => $collection,
                        ])
                    </div>
                @endif

                <div x-data="{
                    openEdit: false,
                    editRating: 0,
                    editComment: '',
                    deleteOpen: false,
                    deleteUrl: '',
                    deleteLabel: ''
                }"
                    @edit-review.window="
                        openEdit = true;
                        editRating = $event.detail.rating;
                        editComment = $event.detail.comment;
                    "
                    @open-delete.window="
                        deleteOpen = true;
                        deleteUrl = $event.detail.url;
                        deleteLabel = $event.detail.label;
                    ">
                    @include('partials.edit_review_popup')
                    @include('partials.confirm_delete')
                </div>

                @if ($owns)
                    <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                        <h3 class="font-display text-base font-semibold text-slate-900">Refine this dataset</h3>
                        <p class="mt-1 text-sm text-slate-500">Run the cleaning pipeline over your collected entries.</p>

                        {{-- Clean 1 --}}
                        @if (in_array($state, ['clean1', 'clean2']))
                            <div
                                class="mt-4 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-400">Half Clean</span>
                                    <span class="block text-xs text-slate-400">PII stripping + quality scoring</span>
                                </span>
                                <span
                                    class="flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <i data-lucide="check" class="h-3.5 w-3.5"></i> Done
                                </span>
                            </div>
                        @else
                            <form method="POST" action="{{ route('collections.clean1', $collection) }}" class="mt-4">
                                @csrf
                                <button
                                    class="flex w-full items-center justify-between rounded-xl border border-indigo-200 bg-indigo-50/60 px-4 py-3 text-left transition hover:bg-indigo-50">
                                    <span>
                                        <span class="block text-sm font-semibold text-indigo-700">Run Half Clean</span>
                                        <span class="block text-xs text-indigo-600/80">PII stripping + quality
                                            scoring</span>
                                    </span>
                                    <span
                                        class="rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Free</span>
                                </button>
                            </form>
                        @endif

                        {{-- Clean 2 --}}
                        @if ($state === 'clean2')
                            <div
                                class="mt-3 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-400">Full Clean</span>
                                    <span class="block text-xs text-slate-400">Dedupe, validate, type-cast,
                                        normalize</span>
                                </span>
                                <span
                                    class="flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <i data-lucide="check" class="h-3.5 w-3.5"></i> Done
                                </span>
                            </div>
                        @else
                            <div x-data="{ clean2Confirm: false }">
                                <button type="button" @click="clean2Confirm = true"
                                    class="mt-3 flex w-full items-center justify-between rounded-xl border border-violet-200 bg-violet-50/60 px-4 py-3 text-left transition hover:bg-violet-50">
                                    <span>
                                        <span class="block text-sm font-semibold text-violet-700">Run Full Clean</span>
                                        <span class="block text-xs text-violet-600/80">Dedupe, validate, type-cast,
                                            normalize</span>
                                    </span>
                                    <span
                                        class="rounded-lg bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">Rp
                                        {{ number_format($fee, 0, ',', '.') }}</span>
                                </button>
                                @include('partials.confirm_clean2', [
                                    'fee' => $fee,
                                    'action' => route('collections.clean2', $collection),
                                ])
                            </div>
                        @endif

                        @if ($report)
                            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                                <p class="font-mono text-[10px] uppercase tracking-wide text-slate-400">Last cleaning
                                    report</p>
                                <div class="mt-2 grid grid-cols-3 gap-2 text-center">
                                    <div>
                                        <p class="font-display text-lg font-semibold text-slate-900">
                                            {{ $report['final_quality_score'] ?? '-' }}</p>
                                        <p class="text-[11px] text-slate-400">Score</p>
                                    </div>
                                    <div>
                                        <p class="font-display text-lg font-semibold text-slate-900">
                                            {{ $report['avg_score'] ?? '-' }}</p>
                                        <p class="text-[11px] text-slate-400">Avg</p>
                                    </div>
                                    <div>
                                        <p class="font-display text-lg font-semibold text-slate-900">
                                            {{ $report['row_count'] ?? '-' }}</p>
                                        <p class="text-[11px] text-slate-400">Rows</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
