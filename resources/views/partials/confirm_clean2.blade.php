<div x-show="clean2Confirm" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
    @click.self="clean2Confirm = false">
    <div class="mx-4 w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50">
            <i data-lucide="sparkles" class="h-5 w-5 text-violet-600"></i>
        </div>

        <h3 class="mt-4 font-display text-base font-semibold text-slate-900">Run Full Clean?</h3>
        <p class="mt-1 text-sm text-slate-500">This will charge your wallet immediately.</p>

        <div class="mt-4 rounded-xl border border-violet-100 bg-violet-50/60 px-4 py-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-violet-700">Full Clean fee</span>
                <span class="font-display text-base font-semibold text-violet-800">{{ \App\Support\Money::format($fee) }}</span>
            </div>
            <ul class="mt-3 space-y-1.5">
                @foreach (['Duplicate removal', 'Type validation & casting', 'Data normalization', 'Quality score report'] as $item)
                    <li class="flex items-center gap-2 text-xs text-violet-600/80">
                        <i data-lucide="check" class="h-3.5 w-3.5 shrink-0 text-violet-500"></i>
                        {{ $item }}
                    </li>
                @endforeach
            </ul>
        </div>

        <form method="POST" action="{{ $action }}" class="mt-5 flex justify-end gap-2">
            @csrf
            <button type="button" @click="clean2Confirm = false"
                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
            <button type="submit"
                class="dc-spectrum rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">Confirm &amp; pay</button>
        </form>
    </div>
</div>
