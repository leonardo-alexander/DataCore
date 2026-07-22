@php $ins = session('topup_instruction'); @endphp

<div class="relative overflow-hidden rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-card">
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                <i data-lucide="clock" class="h-4 w-4"></i>
            </span>
            <div>
                <h2 class="font-display text-base font-semibold text-slate-900">{{ __('Awaiting payment') }}</h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    {{ __('Complete your top-up of') }}
                    <span class="font-semibold text-slate-700">@rupiah($ins['amount'])</span>
                    {{ __('via') }} <span class="font-medium">{{ $ins['method'] }}</span>.
                </p>
            </div>
        </div>
        <a href="{{ route('wallet.topup.cancel') }}"
            class="shrink-0 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-400 transition hover:bg-rose-50 hover:text-rose-500">
            {{ __('Cancel') }}
        </a>
    </div>

    @if ($ins['method'] === 'Virtual Account')
        <div class="mt-5 rounded-xl border border-amber-200 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-slate-400">{{ $ins['va_bank'] }} Virtual Account</p>
            <div class="mt-2 flex items-center gap-3">
                <p class="font-mono text-2xl font-bold tracking-widest text-slate-900">{{ $ins['va_number'] }}</p>
                <button type="button"
                    onclick="navigator.clipboard.writeText('{{ $ins['va_number'] }}').then(()=>{ this.innerHTML='<i data-lucide=\'check\' class=\'h-4 w-4\'></i>'; setTimeout(()=>{ this.innerHTML='<i data-lucide=\'copy\' class=\'h-4 w-4\'></i>'; lucide.createIcons(); },1500); lucide.createIcons(); })"
                    class="text-slate-300 transition hover:text-indigo-600">
                    <i data-lucide="copy" class="h-4 w-4"></i>
                </button>
            </div>
            <p class="mt-2 text-xs text-slate-400">
                {{ __('Transfer exactly') }} <span class="font-semibold text-slate-700">@rupiah($ins['amount'])</span> {{ __('to this VA number. Payment expires in 24 hours.') }}
            </p>
        </div>

    @elseif ($ins['method'] === 'QRIS')
        <div class="mt-5 flex flex-col items-start gap-5 sm:flex-row">
            <div class="shrink-0 rounded-xl border border-amber-200 bg-white p-3 shadow-sm">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=0&data={{ urlencode($ins['qr_payload']) }}"
                    alt="QRIS code" class="h-40 w-40">
            </div>
            <div class="space-y-3">
                <p class="text-sm font-medium text-slate-700">{{ __('Scan with GoPay, OVO, Dana, or any QRIS-compatible app.') }}</p>
                <p class="text-xs text-slate-400">
                    {{ __('Amount:') }} <span class="font-semibold text-slate-700">@rupiah($ins['amount'])</span> · {{ __('Expires in 15 minutes.') }}
                </p>
            </div>
        </div>

    @elseif ($ins['method'] === 'E-wallet')
        <div class="mt-5 rounded-xl border border-amber-200 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-slate-400">{{ $ins['ewallet_name'] }}</p>
            <div class="mt-2 flex items-center gap-3">
                <p class="font-mono text-2xl font-bold tracking-widest text-slate-900">{{ $ins['ewallet_number'] }}</p>
                <button type="button"
                    onclick="navigator.clipboard.writeText('{{ $ins['ewallet_number'] }}').then(()=>{ this.innerHTML='<i data-lucide=\'check\' class=\'h-4 w-4\'></i>'; setTimeout(()=>{ this.innerHTML='<i data-lucide=\'copy\' class=\'h-4 w-4\'></i>'; lucide.createIcons(); },1500); lucide.createIcons(); })"
                    class="text-slate-300 transition hover:text-indigo-600">
                    <i data-lucide="copy" class="h-4 w-4"></i>
                </button>
            </div>
            <p class="mt-2 text-xs text-slate-400">
                {{ __('Send') }} <span class="font-semibold text-slate-700">@rupiah($ins['amount'])</span>
                {{ __('to this') }} {{ $ins['ewallet_name'] }} {{ __('number. Payment expires in 1 hour.') }}
            </p>
        </div>
    @endif

    <div class="mt-5 flex items-center justify-between border-t border-amber-100 pt-4">
        <p class="font-mono text-xs text-slate-400">Ref: {{ $ins['ref'] }}</p>
        <form method="POST" action="{{ route('wallet.topup.confirm') }}">
            @csrf
            <button type="submit"
                class="dc-spectrum inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                <i data-lucide="check-circle" class="h-4 w-4"></i> {{ __("I've paid") }}
            </button>
        </form>
    </div>
</div>
