@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    @php
        $filters = [
            'all' => __('All'), 'topup' => __('Top-up'), 'payment' => __('Payment'),
            'reward' => __('Reward'), 'refund' => __('Refund'), 'withdraw' => __('Withdraw'),
        ];
        $typeMeta = [
            'topup' => ['icon' => 'plus-circle', 'tile' => 'bg-indigo-50 text-indigo-600'],
            'payment' => ['icon' => 'shopping-bag', 'tile' => 'bg-fuchsia-50 text-fuchsia-600'],
            'reward' => ['icon' => 'gift', 'tile' => 'bg-emerald-50 text-emerald-600'],
            'refund' => ['icon' => 'rotate-ccw', 'tile' => 'bg-amber-50 text-amber-600'],
            'withdraw' => ['icon' => 'banknote', 'tile' => 'bg-sky-50 text-sky-600'],
        ];
        $statusBadge = [
            'success'    => 'bg-emerald-50 text-emerald-700',
            'processing' => 'bg-sky-50 text-sky-700',
            'pending'    => 'bg-amber-50 text-amber-700',
            'failed'     => 'bg-rose-50 text-rose-700',
            'cancelled'  => 'bg-slate-100 text-slate-500',
        ];
    @endphp

    <div data-tour="transactions-header">
        <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">{{ __('Transactions') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Every top-up, purchase, sale, and reward on your account.') }}</p>
    </div>

    <div class="mt-7 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
            <div class="flex items-center gap-2 text-emerald-600"><i data-lucide="arrow-down-left" class="h-4 w-4"></i><p class="text-xs font-medium text-slate-500">{{ __('Total in') }}</p></div>
            <p class="mt-2 font-display text-2xl font-semibold text-slate-900">@rupiah($totalIn)</p>
        </div>
        <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
            <div class="flex items-center gap-2 text-rose-600"><i data-lucide="arrow-up-right" class="h-4 w-4"></i><p class="text-xs font-medium text-slate-500">{{ __('Total out') }}</p></div>
            <p class="mt-2 font-display text-2xl font-semibold text-slate-900">@rupiah($totalOut)</p>
        </div>
        <div class="dc-spectrum rounded-2xl p-5 text-white shadow-glow">
            <div class="flex items-center gap-2 text-white/80"><i data-lucide="trending-up" class="h-4 w-4"></i><p class="text-xs font-medium">{{ __('Net flow') }}</p></div>
            <p class="mt-2 font-display text-2xl font-semibold">@rupiah($totalIn - $totalOut)</p>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        @foreach ($filters as $key => $label)
            <a href="{{ route('transactions.index', $key === 'all' ? [] : ['type' => $key]) }}"
               @class(['rounded-full px-4 py-2 text-sm font-medium transition', 'dc-spectrum text-white shadow-glow' => $filter === $key, 'border border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600' => $filter !== $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card">
        @if ($transactions->count())
            <div class="divide-y divide-slate-50">
                @foreach ($transactions as $tx)
                    @php $meta = $typeMeta[$tx->type] ?? ['icon' => 'circle-dot', 'tile' => 'bg-slate-100 text-slate-500']; @endphp
                    <div class="flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50/50 sm:px-6">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $meta['tile'] }}">
                            <i data-lucide="{{ $meta['icon'] }}" class="h-5 w-5"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $tx->description ?? ucfirst($tx->type) }}</p>
                                <span class="hidden rounded-full px-2 py-0.5 text-[11px] font-medium {{ $statusBadge[$tx->status] ?? 'bg-slate-100 text-slate-500' }} sm:inline-block">{{ ucfirst($tx->status) }}</span>
                            </div>
                            <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $tx->reference }} · {{ $tx->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p @class(['font-mono text-sm font-semibold', 'text-emerald-600' => $tx->direction === 'credit', 'text-slate-800' => $tx->direction === 'debit'])>
                                {{ $tx->direction === 'credit' ? '+' : '−' }}@rupiah($tx->amount)
                            </p>
                            <p class="font-mono text-[11px] uppercase tracking-wide text-slate-400">{{ $tx->type }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-16 text-center">
                <i data-lucide="receipt" class="mx-auto h-8 w-8 text-slate-300"></i>
                <p class="mt-3 font-medium text-slate-600">{{ $filter !== 'all' ? __('No transactions in this category') : __('No transactions yet') }}</p>
                <p class="text-sm text-slate-400">{{ __('Your activity will show up here.') }}</p>
            </div>
        @endif
    </div>

    @if ($transactions->hasPages())
        <div class="mt-6">{{ $transactions->links() }}</div>
    @endif
@endsection
