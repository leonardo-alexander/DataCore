{{-- Confirm-save modal, lives inside the settings form's Alpine scope (confirmOpen) --}}
<div x-show="confirmOpen" x-cloak x-scroll-lock="confirmOpen" @keydown.escape.window="confirmOpen = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="confirmOpen = false"></div>
    <div x-show="confirmOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
        <div class="flex items-start gap-3.5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <i data-lucide="key-round" class="h-5 w-5"></i>
            </span>
            <div>
                <h3 class="font-display text-base font-semibold text-slate-900">{{ __('Change password?') }}</h3>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">
                    {{ __('Your password will be updated and you will need to use it the next time you sign in.') }}
                </p>
            </div>
        </div>
        <div class="mt-5 flex justify-end gap-2.5">
            <button type="button" @click="confirmOpen = false"
                class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                {{ __('Cancel') }}
            </button>
            <button type="button"
                @click="confirmOpen = false; dcSaveSettings($root)"
                class="dc-spectrum inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                <i data-lucide="check" class="h-4 w-4"></i> {{ __('Yes, update') }}
            </button>
        </div>
    </div>
</div>
