@extends('layouts.app')

@section('title', __('Wallet'))

@section('content')
    @php
        $isUsd = \App\Support\Money::currency() === 'USD';
        $jsFormat = $isUsd
            ? "format(v) { return '$' + ((v || 0) / " . \App\Support\Money::rate() . ").toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }"
            : "format(v) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(v || 0); }";
        $presets = $isUsd
            ? array_map(fn ($usd) => $usd * (int) \App\Support\Money::rate(), [5, 10, 50, 100])
            : [50000, 100000, 250000, 500000];
        $channels = [
            ['value' => 'Virtual Account', 'icon' => 'landmark',   'desc' => __('BCA, BNI, Mandiri')],
            ['value' => 'QRIS',            'icon' => 'qr-code',    'desc' => __('Scan to pay')],
            ['value' => 'E-wallet',        'icon' => 'smartphone', 'desc' => __('GoPay, OVO, Dana')],
        ];
    @endphp

    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">{{ __('Wallet') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Top up to buy datasets and unlock Full Clean refinement. Earnings from sales and contributions land here.') }}</p>
    </div>

    {{-- Balance banner --}}
    <div class="relative mt-7 overflow-hidden rounded-2xl bg-[#0b0f1c] p-6 text-white shadow-card sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-violet-600/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-16 left-12 h-48 w-48 rounded-full bg-indigo-600/20 blur-3xl"></div>
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-slate-400">{{ __('Available balance') }}</p>
                <p class="mt-2 font-display text-4xl font-semibold tracking-tight">@rupiah($balance)</p>
                <a href="{{ route('transactions.index') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-violet-300 hover:text-violet-200">
                    {{ __('View all transactions') }} <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                </a>
            </div>
            <div class="flex gap-3">
                <div class="rounded-xl border border-white/10 bg-white/5 px-5 py-3">
                    <p class="flex items-center gap-1.5 text-[11px] text-emerald-300">
                        <i data-lucide="arrow-down-left" class="h-3.5 w-3.5"></i> {{ __('Earned') }}
                    </p>
                    <p class="mt-1 font-mono text-sm font-semibold">@rupiah($earned)</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 px-5 py-3">
                    <p class="flex items-center gap-1.5 text-[11px] text-rose-300">
                        <i data-lucide="arrow-up-right" class="h-3.5 w-3.5"></i> {{ __('Spent') }}
                    </p>
                    <p class="mt-1 font-mono text-sm font-semibold">@rupiah($spent)</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending topup instruction (full-width, above the forms) --}}
    @if (session('topup_instruction'))
        <div class="mt-6">
            @include('partials.topup_confirmation')
        </div>
    @endif

    {{-- Top-up + Withdrawal side by side --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Top-up form --}}
        <form method="POST" action="{{ route('wallet.topup') }}"
              x-data="{
                  amount: {{ $presets[1] }},
                  method: 'Virtual Account',
                  isUsd: {{ $isUsd ? 'true' : 'false' }},
                  rate: {{ (int) \App\Support\Money::rate() }},
                  {{ $jsFormat }}
              }"
              data-tour="wallet-topup"
              class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
            @csrf

            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <i data-lucide="plus-circle" class="h-4 w-4"></i>
                </span>
                <div>
                    <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Top up balance') }}</h2>
                    <p class="text-xs text-slate-400">{{ __('Choose an amount and a payment channel.') }}</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-2.5 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4">
                @foreach ($presets as $preset)
                    <button type="button" @click="amount = {{ $preset }}"
                            :class="amount === {{ $preset }} ? 'border-indigo-400 bg-indigo-50/70 text-indigo-700 ring-4 ring-indigo-100' : 'border-slate-200 text-slate-600 hover:border-indigo-200'"
                            class="rounded-xl border py-3 text-sm font-semibold transition">
                        @rupiah($preset)
                    </button>
                @endforeach
            </div>

            <div class="mt-4">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Custom amount') }} <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-400" x-text="isUsd ? '$' : 'Rp'"></span>
                    <input type="number"
                           :value="isUsd ? Math.round((amount / rate) * 100) / 100 : amount"
                           @input="amount = isUsd ? Math.round((parseFloat($event.target.value) || 0) * rate) : parseInt($event.target.value || 0, 10)"
                           :step="isUsd ? 0.01 : 1"
                           :min="isUsd ? Math.round((({{ config('datacore.min_topup') }}) / rate) * 100) / 100 : {{ config('datacore.min_topup') }}"
                           :max="isUsd ? Math.round((({{ config('datacore.max_topup') }}) / rate) * 100) / 100 : {{ config('datacore.max_topup') }}"
                           class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 font-mono text-sm text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <input type="hidden" name="amount" :value="amount">
                </div>
                <p class="mt-1.5 text-xs text-slate-400">{{ __('Min') }} @rupiah(config('datacore.min_topup')) · {{ __('Max') }} @rupiah(config('datacore.max_topup')).</p>
            </div>

            <div class="mt-5">
                <label class="mb-2 block text-sm font-medium text-slate-700">{{ __('Payment channel') }} <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 gap-2.5">
                    @foreach ($channels as $channel)
                        <label :class="method === '{{ $channel['value'] }}' ? 'border-indigo-400 bg-indigo-50/60 ring-4 ring-indigo-100' : 'border-slate-200 hover:border-indigo-200'"
                               class="flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition">
                            <input type="radio" name="method" value="{{ $channel['value'] }}" x-model="method" class="sr-only">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-indigo-600 shadow-sm">
                                <i data-lucide="{{ $channel['icon'] }}" class="h-4 w-4"></i>
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">{{ $channel['value'] }}</span>
                                <span class="block text-xs text-slate-400">{{ $channel['desc'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5">
                <div>
                    <p class="text-xs text-slate-400">{{ __('You will be charged') }}</p>
                    <p class="font-display text-2xl font-semibold text-slate-900" x-text="format(amount)"></p>
                </div>
                <button type="submit" class="dc-spectrum inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                    <i data-lucide="plus-circle" class="h-4 w-4"></i> {{ __('Top up now') }}
                </button>
            </div>
        </form>

        {{-- Withdrawal form --}}
        <form method="POST" action="{{ route('wallet.withdraw') }}"
            x-data="{
                amount: {{ config('datacore.min_withdrawal') }},
                isUsd: {{ $isUsd ? 'true' : 'false' }},
                rate: {{ (int) \App\Support\Money::rate() }},
                {{ $jsFormat }}
            }"
            data-tour="wallet-withdraw"
            class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
            @csrf

            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-500">
                    <i data-lucide="send" class="h-4 w-4"></i>
                </span>
                <div>
                    <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Withdraw funds') }}</h2>
                    <p class="text-xs text-slate-400">{{ __('Processed in 1–3 business days.') }}</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                {{-- Amount --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Amount') }} <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-400" x-text="isUsd ? '$' : 'Rp'"></span>
                        <input type="number"
                            :value="isUsd ? Math.round((amount / rate) * 100) / 100 : amount"
                            @input="amount = isUsd ? Math.round((parseFloat($event.target.value) || 0) * rate) : parseInt($event.target.value || 0, 10)"
                            :step="isUsd ? 0.01 : 1"
                            :min="isUsd ? Math.round((({{ config('datacore.min_withdrawal') }}) / rate) * 100) / 100 : {{ config('datacore.min_withdrawal') }}"
                            :max="isUsd ? Math.round((({{ $balance }}) / rate) * 100) / 100 : {{ $balance }}"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 font-mono text-sm text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        <input type="hidden" name="amount" :value="amount">
                    </div>
                    <div class="mt-1.5 flex items-center justify-between text-xs text-slate-400">
                        <span>{{ __('Min') }} @rupiah(config('datacore.min_withdrawal'))</span>
                        <button type="button" @click="amount = {{ $balance }}"
                            class="font-medium text-indigo-600 hover:text-indigo-700">
                            {{ __('Full balance') }} (@rupiah($balance))
                        </button>
                    </div>
                    @error('amount') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- Bank name + account number --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Bank') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                            placeholder="{{ __('e.g. BCA, Mandiri') }}"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        @error('bank_name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Account no.') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}"
                            placeholder="1234567890"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        @error('account_number') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Account holder name --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Account holder name') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="account_name" value="{{ old('account_name', Auth::user()->name) }}"
                        placeholder="{{ __('Full name on the bank account') }}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    @error('account_name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-5">
                <p class="text-xs text-slate-400">
                    <i data-lucide="info" class="mr-1 inline h-3.5 w-3.5"></i>
                    {{ __('Funds leave immediately, arrive in 1–3 days.') }}
                </p>
                <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-rose-200 bg-rose-50 px-5 py-2.5 text-sm font-semibold text-rose-600 shadow-sm transition hover:bg-rose-100">
                    <i data-lucide="send" class="h-4 w-4"></i>
                    {{ __('Withdraw') }} <span x-text="format(amount)" class="font-mono"></span>
                </button>
            </div>
        </form>
    </div>

    {{-- Recent activity (full width) --}}
    <div class="mt-6 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-base font-semibold text-slate-900">{{ __('Recent activity') }}</h2>
            <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('View all') }}</a>
        </div>
        <div class="mt-4 divide-y divide-slate-50">
            @forelse ($transactions as $tx)
                <div class="flex items-center gap-3 py-3">
                    <span @class([
                        'flex h-9 w-9 items-center justify-center rounded-xl',
                        'bg-emerald-50 text-emerald-600' => $tx->direction === 'credit',
                        'bg-rose-50 text-rose-600'       => $tx->direction === 'debit',
                    ])>
                        <i data-lucide="{{ $tx->direction === 'credit' ? 'arrow-down-left' : 'arrow-up-right' }}" class="h-4 w-4"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $tx->description ?? ucfirst($tx->type) }}</p>
                        <p class="font-mono text-xs text-slate-400">{{ $tx->reference }} · {{ $tx->created_at->format('d M, H:i') }}</p>
                    </div>
                    <p @class(['font-mono text-sm font-semibold', 'text-emerald-600' => $tx->direction === 'credit', 'text-slate-700' => $tx->direction === 'debit'])>
                        {{ $tx->direction === 'credit' ? '+' : '−' }}@rupiah($tx->amount)
                    </p>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-slate-400">{{ __('No transactions yet. Top up to get started.') }}</p>
            @endforelse
        </div>
    </div>
@endsection
