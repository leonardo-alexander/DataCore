@extends('layouts.app')

@section('title', 'Fill Survey')

@section('content')
    @php
        $reward    = (int) $collection->reward;
        $requested = $collection->metadata_fields ?? [];
    @endphp

    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-900">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Back to dashboard
    </a>

    <div class="mx-auto mt-5 max-w-2xl">
        <div class="relative overflow-hidden rounded-2xl bg-[#0b0f1c] p-6 text-white shadow-card">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-violet-600/20 blur-3xl"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-violet-300">Survey</p>
                    <h1 class="mt-1 font-display text-2xl font-semibold">{{ $collection->title }}</h1>
                </div>
                @if ($reward > 0)
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold text-emerald-300">
                        <i data-lucide="gift" class="h-4 w-4"></i> Earn {{ \App\Support\Money::format($reward) }}
                    </span>
                @endif
            </div>
        </div>

        @if ($requested)
            @if (! auth()->user()->isVerified())
                <div class="mt-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <i data-lucide="shield-alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-900">Verify your ID to answer</p>
                        <p class="text-sm text-amber-700">This survey attaches your verified {{ collect($requested)->map(fn ($k) => \App\Models\Collection::METADATA[$k] ?? $k)->join(', ', ' and ') }}.</p>
                        <a href="{{ route('verification.index') }}" class="mt-1 inline-block text-sm font-semibold text-amber-800 underline">Verify now</a>
                    </div>
                </div>
            @else
                <div class="mt-6 rounded-xl border border-indigo-200 bg-indigo-50/50 p-4">
                    <p class="text-sm font-medium text-slate-900">This survey will attach your verified profile:</p>
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($requested as $metaKey)
                            <div class="rounded-lg bg-white px-3 py-2 shadow-sm">
                                <p class="text-[11px] uppercase tracking-wide text-slate-400">{{ \App\Models\Collection::METADATA[$metaKey] ?? $metaKey }}</p>
                                <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->profile?->metadata()[$metaKey] ?? '-' }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Filled from your verified ID; cannot be edited here.</p>
                </div>
            @endif
        @endif

        <form method="POST" action="{{ route('entries.store', $collection) }}" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf

            @foreach ($collection->questions as $index => $question)
                <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                    <label class="flex items-start gap-2 text-sm font-medium text-slate-900">
                        <span class="font-mono text-xs text-slate-400">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <span>
                            {{ $question->title }}
                            @if ($question->required)
                                <span class="ml-1 text-rose-500">*</span>
                            @endif
                        </span>
                    </label>

                    <div class="mt-3">
                        @if ($question->type === 'choice' && is_array($question->options))
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach ($question->options as $option)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 transition has-checked:border-indigo-400 has-checked:bg-indigo-50/60 has-checked:text-indigo-700">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}" class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-200">
                                        {{ $option }}
                                    </label>
                                @endforeach
                                @if ($question->has_other)
                                    <label x-data class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 transition has-checked:border-indigo-400 has-checked:bg-indigo-50/60 has-checked:text-indigo-700 sm:col-span-2">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="__other__" x-ref="other" class="h-4 w-4 shrink-0 border-slate-300 text-indigo-600 focus:ring-indigo-200">
                                        <span class="shrink-0">Other:</span>
                                        <input type="text" name="other_texts[{{ $question->id }}]" placeholder="Your answer"
                                            @focus="$refs.other.checked = true"
                                            class="w-full border-0 border-b border-slate-200 bg-transparent px-1 py-0.5 text-sm placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-0">
                                    </label>
                                @endif
                            </div>

                        @elseif ($question->type === 'checkbox' && is_array($question->options))
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach ($question->options as $option)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 transition has-checked:border-indigo-400 has-checked:bg-indigo-50/60 has-checked:text-indigo-700">
                                        <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option }}" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-200">
                                        {{ $option }}
                                    </label>
                                @endforeach
                                @if ($question->has_other)
                                    <label x-data class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 transition has-checked:border-indigo-400 has-checked:bg-indigo-50/60 has-checked:text-indigo-700 sm:col-span-2">
                                        <input type="checkbox" name="answers[{{ $question->id }}][]" value="__other__" x-ref="other" class="h-4 w-4 shrink-0 rounded border-slate-300 text-indigo-600 focus:ring-indigo-200">
                                        <span class="shrink-0">Other:</span>
                                        <input type="text" name="other_texts[{{ $question->id }}]" placeholder="Your answer"
                                            @focus="$refs.other.checked = true"
                                            class="w-full border-0 border-b border-slate-200 bg-transparent px-1 py-0.5 text-sm placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-0">
                                    </label>
                                @endif
                            </div>

                        @elseif ($question->type === 'rating')
                            @php $stars = $question->max_stars ?? 5; @endphp
                            <div x-data="{ rating: 0, hovered: 0 }" class="flex items-center gap-1">
                                @for ($s = 1; $s <= $stars; $s++)
                                    <button type="button"
                                        @click="rating = {{ $s }}"
                                        @mouseenter="hovered = {{ $s }}"
                                        @mouseleave="hovered = 0"
                                        :class="(hovered || rating) >= {{ $s }} ? 'text-amber-400' : 'text-slate-200'"
                                        class="transition hover:scale-110">
                                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </button>
                                @endfor
                                <input type="hidden" name="answers[{{ $question->id }}]" :value="rating">
                                <span x-show="rating" x-cloak class="ml-3 font-mono text-sm text-slate-500" x-text="rating + ' / {{ $stars }}'"></span>
                            </div>

                        @elseif ($question->type === 'datetime')
                            <input type="{{ $question->date_mode ?? 'date' }}"
                                name="answers[{{ $question->id }}]"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">

                        @elseif ($question->type === 'file')
                            <div x-data="{ name: '' }">
                                <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 px-4 py-6 text-center transition hover:border-indigo-300 hover:bg-slate-50">
                                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                        <i data-lucide="cloud-upload" class="h-5 w-5"></i>
                                    </span>
                                    <p class="mt-3 text-sm font-medium text-slate-700" x-show="!name">
                                        Drop a file or <span class="text-indigo-600">browse</span>
                                    </p>
                                    <p class="mt-3 flex items-center gap-1.5 text-sm font-medium text-emerald-600" x-show="name" x-cloak>
                                        <i data-lucide="circle-check-big" class="h-4 w-4"></i>
                                        <span x-text="name"></span>
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">Images, PDF, Office docs, CSV, up to 10 MB.</p>
                                    <input type="file" name="answers[{{ $question->id }}]"
                                        accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt"
                                        {{ $question->required ? 'required' : '' }} class="hidden"
                                        @change="name = $event.target.files[0]?.name ?? ''">
                                </label>
                            </div>

                        @elseif ($question->type === 'paragraph')
                            <textarea name="answers[{{ $question->id }}]" rows="3" placeholder="Your answer"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100"></textarea>

                        @elseif ($question->type === 'number')
                            <input type="number" name="answers[{{ $question->id }}]" placeholder="0"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">

                        @else
                            <input type="text" name="answers[{{ $question->id }}]" placeholder="Your answer"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 text-xs text-slate-500">
                <i data-lucide="shield" class="h-4 w-4 shrink-0"></i>
                Your responses are stored securely and anonymized during cleaning before this dataset is published.
            </div>

            <button type="submit" class="dc-spectrum inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                <i data-lucide="send" class="h-4 w-4"></i> Submit entry
            </button>
        </form>
    </div>
@endsection
