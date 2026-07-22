@extends('layouts.app')

@section('title', 'Purchases')

@section('content')
    @php
        $gradients = ['from-indigo-500 to-violet-500', 'from-sky-500 to-indigo-500', 'from-fuchsia-500 to-purple-500', 'from-violet-600 to-sky-500', 'from-emerald-500 to-teal-500'];
        $totalSpent = $purchases->sum('amount');
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">Purchases</h1>
            <p class="mt-1 text-sm text-slate-500">Datasets you own. Download the cleanest available version any time.</p>
        </div>
        @if ($purchases->count())
            <div class="rounded-xl border border-slate-200/70 bg-white px-4 py-2.5 text-right shadow-card">
                <p class="text-[11px] uppercase tracking-wide text-slate-400">Total spent</p>
                <p class="font-display text-lg font-semibold text-slate-900">@rupiah($totalSpent)</p>
            </div>
        @endif
    </div>

    @if ($purchases->count())
        <div class="mt-7 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($purchases as $purchase)
                @php $collection = $purchase->collection; @endphp
                <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card">
                    <div class="relative h-24 bg-gradient-to-br {{ $gradients[$collection->id % count($gradients)] }}">
                        <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/15 blur-xl"></div>
                        <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-white/20 px-2.5 py-1 text-xs font-medium text-white backdrop-blur">
                            <i data-lucide="check" class="h-3 w-3"></i> Owned
                        </span>
                        <span class="absolute bottom-3 right-4 font-mono text-xs font-semibold text-white/90">{{ $purchase->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        @if ($collection->category)
                            <span class="font-mono text-[11px] uppercase tracking-wide text-indigo-500">{{ $collection->category->name }}</span>
                        @endif
                        <h3 class="mt-1 font-display text-lg font-semibold text-slate-900">{{ $collection->title }}</h3>
                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                            <span class="dc-spectrum flex h-5 w-5 items-center justify-center rounded-full text-[9px] font-semibold text-white">{{ strtoupper(mb_substr($collection->user->name, 0, 2)) }}</span>
                            <span>{{ $collection->user->name }}</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                            <span class="text-sm font-medium text-slate-700">{{ $purchase->amount > 0 ? \App\Support\Money::format($purchase->amount) : 'Free' }}</span>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('collections.export', $collection) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-indigo-200 hover:text-indigo-600">
                                    <i data-lucide="download" class="h-3.5 w-3.5"></i> CSV
                                </a>
                                <a href="{{ route('marketplace.show', $collection) }}" class="dc-spectrum inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white shadow-glow transition hover:brightness-110">
                                    View <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="mt-7 rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center">
            <i data-lucide="shopping-bag" class="mx-auto h-8 w-8 text-slate-300"></i>
            <p class="mt-3 font-medium text-slate-600">No purchases yet</p>
            <p class="text-sm text-slate-400">Browse the marketplace to find datasets worth trusting.</p>
            <a href="{{ route('marketplace.index') }}" class="dc-spectrum mt-5 inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                <i data-lucide="compass" class="h-4 w-4"></i> Explore marketplace
            </a>
        </div>
    @endif
@endsection
