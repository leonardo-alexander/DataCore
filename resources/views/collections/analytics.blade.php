@extends('layouts.app')

@section('title', "Analytics · {$collection->title}")

@section('content')
    @php
        $dailyValues = array_values($dailyData);
        $dailyLabels = array_keys($dailyData);
        $maxDaily    = max([...$dailyValues, 1]);
        $totalInWindow = array_sum($dailyValues);
        $pipelineTotal = max($totalEntries, 1);
        $statusLabel = ['draft' => __('Draft'), 'ongoing' => __('Collecting'), 'published' => __('For sale')][$collection->status] ?? ucfirst($collection->status);
    @endphp

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('collections.index') }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-900">
                <i data-lucide="arrow-left" class="h-4 w-4"></i> My collections
            </a>
            <h1 class="mt-3 font-display text-3xl font-semibold tracking-tight text-slate-900">{{ $collection->title }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span @class([
                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold',
                    'bg-violet-50 text-violet-700'  => $collection->status === 'ongoing',
                    'bg-fuchsia-50 text-fuchsia-700' => $collection->status === 'published',
                    'bg-slate-100 text-slate-600'    => $collection->status === 'draft',
                ])>
                    <span @class([
                        'h-1.5 w-1.5 rounded-full',
                        'bg-violet-500'  => $collection->status === 'ongoing',
                        'bg-fuchsia-500' => $collection->status === 'published',
                        'bg-slate-400'   => $collection->status === 'draft',
                    ])></span>
                    {{ $statusLabel }}
                </span>
                @if ($collection->category)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $collection->category->name }}</span>
                @endif
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                    {{ $collection->type === 'upload' ? 'CSV upload' : 'Survey' }}
                </span>
            </div>
        </div>
        <a href="{{ route('collections.edit', $collection) }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600">
            <i data-lucide="pencil" class="h-4 w-4"></i> Edit collection
        </a>
    </div>

    {{-- Stat cards --}}
    <div class="mt-7 grid grid-cols-2 gap-4 lg:grid-cols-4">
        {{-- Total entries --}}
        <div class="relative overflow-hidden rounded-2xl bg-[#0b0f1c] p-5 text-white shadow-card">
            <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-indigo-600/30 blur-2xl"></div>
            <div class="relative">
                <p class="flex items-center gap-2 text-xs font-medium text-slate-400">
                    <i data-lucide="database" class="h-3.5 w-3.5"></i> Total entries
                </p>
                <p class="mt-2 font-display text-3xl font-semibold tracking-tight">{{ number_format($totalEntries) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $totalInWindow > 0 ? '+' . number_format($totalInWindow) . ' in last 30 days' : 'No new entries recently' }}</p>
            </div>
        </div>

        {{-- Respondent target --}}
        <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
            <p class="flex items-center gap-2 text-xs font-medium text-slate-500">
                <i data-lucide="users" class="h-3.5 w-3.5 text-indigo-400"></i> Target respondents
            </p>
            <p class="mt-2 font-display text-3xl font-semibold tracking-tight text-slate-900">
                {{ $target > 0 ? number_format($target) : '-' }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                @if ($target > 0)
                    Reward: @rupiah($collection->reward) per entry
                @else
                    No target set
                @endif
            </p>
        </div>

        {{-- Completion rate --}}
        <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
            <p class="flex items-center gap-2 text-xs font-medium text-slate-500">
                <i data-lucide="percent" class="h-3.5 w-3.5 text-emerald-500"></i> Completion rate
            </p>
            @if ($completionRate !== null)
                <p class="mt-2 font-display text-3xl font-semibold tracking-tight text-slate-900">{{ $completionRate }}%</p>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-1.5 rounded-full bg-emerald-400 transition-all"
                        style="width: {{ $completionRate }}%"></div>
                </div>
            @else
                <p class="mt-2 font-display text-3xl font-semibold tracking-tight text-slate-400">-</p>
                <p class="mt-1 text-xs text-slate-400">Set a respondent target to track</p>
            @endif
        </div>

        {{-- Quality score --}}
        <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
            <p class="flex items-center gap-2 text-xs font-medium text-slate-500">
                <i data-lucide="gauge" class="h-3.5 w-3.5 text-violet-500"></i> Quality score
            </p>
            <p class="mt-2 font-display text-3xl font-semibold tracking-tight text-slate-900">
                {{ $qualityScore !== null ? number_format($qualityScore, 2) : '-' }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                {{ $qualityScore !== null ? 'Run Half Clean to refresh' : 'Run Half Clean to compute' }}
            </p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- 30-day activity chart (2/3 width) --}}
        <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card lg:col-span-2"
            x-data="{
                values: {{ json_encode($dailyValues) }},
                labels: {{ json_encode($dailyLabels) }},
                max: {{ $maxDaily }},
                hover: null,
                barHeight(v) {
                    return this.max > 0 ? Math.max((v / this.max) * 100, v > 0 ? 2 : 0) : 0;
                },
                shortLabel(d) {
                    const dt = new Date(d + 'T00:00:00');
                    return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }
            }">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-base font-semibold text-slate-900">Entry activity</h2>
                    <p class="mt-0.5 text-xs text-slate-400">Submissions over the last 30 days</p>
                </div>
                <span class="rounded-lg bg-slate-50 px-3 py-1.5 font-mono text-sm font-semibold text-slate-700">
                    +{{ number_format($totalInWindow) }} entries
                </span>
            </div>

            {{-- Tooltip --}}
            <div class="mt-5 relative">
                <div x-show="hover !== null" x-cloak
                    class="pointer-events-none absolute -top-8 left-1/2 z-10 -translate-x-1/2 rounded-lg bg-slate-900 px-2.5 py-1 text-xs text-white shadow-lg whitespace-nowrap"
                    x-text="hover !== null ? shortLabel(labels[hover]) + ': ' + values[hover] + ' entries' : ''">
                </div>

                {{-- Chart bars --}}
                <div class="flex items-end gap-0.5 h-36">
                    <template x-for="(val, i) in values" :key="i">
                        <div class="group flex flex-1 cursor-default flex-col items-center"
                            @mouseenter="hover = i" @mouseleave="hover = null">
                            <div class="w-full rounded-t transition-colors"
                                :class="hover === i ? 'bg-indigo-500' : (val > 0 ? 'bg-indigo-200' : 'bg-slate-100')"
                                :style="`height: ${barHeight(val)}%; min-height: ${val > 0 ? '3px' : '0'}`">
                            </div>
                            <div class="mt-0 h-full flex-1 bg-transparent"></div>
                        </div>
                    </template>
                </div>

                {{-- X-axis labels: show every ~7 days --}}
                <div class="mt-1 flex">
                    <template x-for="(label, i) in labels" :key="i">
                        <div class="flex-1 text-center"
                            :class="(i % 7 === 0 || i === labels.length - 1) ? 'opacity-100' : 'opacity-0'"
                            x-text="(i % 7 === 0 || i === labels.length - 1) ? shortLabel(label) : ''"
                            class="font-mono text-[9px] text-slate-400">
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-4 border-t border-slate-100 pt-4 text-xs text-slate-400">
                <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-indigo-200"></span> Entries</span>
                <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-slate-100"></span> No activity</span>
            </div>
        </div>

        {{-- Pipeline breakdown (1/3 width) --}}
        <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
            <h2 class="font-display text-base font-semibold text-slate-900">Pipeline breakdown</h2>
            <p class="mt-0.5 text-xs text-slate-400">How entries are distributed across cleaning stages</p>

            <div class="mt-5 space-y-4">
                {{-- Raw --}}
                <div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 font-medium text-slate-700">
                            <i data-lucide="database" class="h-3.5 w-3.5 text-slate-400"></i> Raw
                        </span>
                        <span class="font-mono font-semibold text-slate-900">{{ number_format($withRaw) }}</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-slate-400"
                            style="width: {{ $pipelineTotal > 0 ? round($withRaw / $pipelineTotal * 100) : 0 }}%"></div>
                    </div>
                    <p class="mt-1 text-right font-mono text-[10px] text-slate-400">
                        {{ $pipelineTotal > 0 ? round($withRaw / $pipelineTotal * 100) : 0 }}%
                    </p>
                </div>

                {{-- Half Clean --}}
                <div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 font-medium text-slate-700">
                            <i data-lucide="shield-check" class="h-3.5 w-3.5 text-indigo-500"></i> Half Clean
                        </span>
                        <span class="font-mono font-semibold text-slate-900">{{ number_format($withClean1) }}</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-indigo-400"
                            style="width: {{ $pipelineTotal > 0 ? round($withClean1 / $pipelineTotal * 100) : 0 }}%"></div>
                    </div>
                    <p class="mt-1 text-right font-mono text-[10px] text-slate-400">
                        {{ $pipelineTotal > 0 ? round($withClean1 / $pipelineTotal * 100) : 0 }}%
                    </p>
                </div>

                {{-- Full Clean --}}
                <div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 font-medium text-slate-700">
                            <i data-lucide="sparkles" class="h-3.5 w-3.5 text-violet-500"></i> Full Clean
                        </span>
                        <span class="font-mono font-semibold text-slate-900">{{ number_format($withClean2) }}</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-violet-400"
                            style="width: {{ $pipelineTotal > 0 ? round($withClean2 / $pipelineTotal * 100) : 0 }}%"></div>
                    </div>
                    <p class="mt-1 text-right font-mono text-[10px] text-slate-400">
                        {{ $pipelineTotal > 0 ? round($withClean2 / $pipelineTotal * 100) : 0 }}%
                    </p>
                </div>
            </div>

            @if ($totalEntries === 0)
                <p class="mt-4 rounded-xl bg-slate-50 py-4 text-center text-xs text-slate-400">
                    No entries collected yet.
                </p>
            @endif

            <div class="mt-5 border-t border-slate-100 pt-4">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Total entries</span>
                    <span class="font-mono font-semibold text-slate-900">{{ number_format($totalEntries) }}</span>
                </div>
                @if ($collection->respondent_target)
                    <div class="mt-1.5 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Target</span>
                        <span class="font-mono text-slate-600">{{ number_format($collection->respondent_target) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Collection details --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card lg:col-span-2">
            <h2 class="font-display text-base font-semibold text-slate-900">Collection details</h2>
            <dl class="mt-4 grid grid-cols-2 gap-x-8 gap-y-4 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-xs text-slate-400">Type</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $collection->type === 'upload' ? 'CSV upload' : 'Survey' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Status</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $statusLabel }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Category</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $collection->category?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Price</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        @if ($collection->price > 0) @rupiah($collection->price) @else Free @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Reward per entry</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        @if ($collection->reward > 0) @rupiah($collection->reward) @else - @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Pipeline state</dt>
                    <dd class="mt-1">
                        @php $state = $collection->cleanState(); @endphp
                        <span @class([
                            'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-violet-50 text-violet-700' => $state === 'clean2',
                            'bg-indigo-50 text-indigo-700' => $state === 'clean1',
                            'bg-slate-100 text-slate-600'  => $state === 'raw',
                        ])>
                            {{ $state === 'clean2' ? 'Full Clean' : ($state === 'clean1' ? 'Half Clean' : 'Raw') }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Created</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $collection->created_at->format('d M Y') }}</dd>
                </div>
                @if ($collection->survey_ended_at)
                    <div>
                        <dt class="text-xs text-slate-400">Survey ended</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $collection->survey_ended_at->format('d M Y') }}</dd>
                    </div>
                @endif
                @if ($collection->quality_score !== null)
                    <div>
                        <dt class="text-xs text-slate-400">Quality score</dt>
                        <dd class="mt-1 font-mono font-semibold text-slate-900">{{ number_format($collection->quality_score, 2) }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Quick actions --}}
        <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
            <h2 class="font-display text-base font-semibold text-slate-900">Quick actions</h2>
            <div class="mt-4 space-y-2">
                <a href="{{ route('collections.edit', $collection) }}"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700">
                    <i data-lucide="pencil" class="h-4 w-4 text-slate-400"></i> Edit collection
                </a>
                <a href="{{ route('collections.export', $collection) }}"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700">
                    <i data-lucide="download" class="h-4 w-4 text-slate-400"></i> Export CSV
                </a>
                <a href="{{ route('marketplace.show', $collection) }}"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700">
                    <i data-lucide="eye" class="h-4 w-4 text-slate-400"></i> View in marketplace
                </a>
                @if ($collection->status === 'ongoing' && $collection->type === 'survey')
                    <a href="{{ route('entries.create', $collection) }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700">
                        <i data-lucide="clipboard-list" class="h-4 w-4 text-slate-400"></i> View survey form
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
