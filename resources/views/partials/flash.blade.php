@php
    $toasts = [];
    if (session('success')) {
        $toasts[] = ['type' => 'success', 'message' => session('success')];
    }
    if (session('error')) {
        $toasts[] = ['type' => 'error', 'message' => session('error')];
    }
    if (session('status')) {
        $toasts[] = ['type' => 'success', 'message' => session('status')];
    }
    if ($errors->any()) {
        $toasts[] = [
            'type' => 'error',
            'message' =>
                $errors->count() === 1
                    ? $errors->first()
                    : __('Please fix the :count highlighted fields.', ['count' => $errors->count()]),
        ];
    }
@endphp

@if (count($toasts))
    <div class="pointer-events-none fixed right-4 top-4 z-50 flex w-full max-w-sm flex-col gap-3 sm:right-6 sm:top-6">
        @foreach ($toasts as $toast)
            <div x-data="{ show: false }" x-init="$nextTick(() => show = true);
            setTimeout(() => show = false, 5500)" x-show="show" x-cloak
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-6"
                x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-x-6"
                class="pointer-events-auto flex items-start gap-3 overflow-hidden rounded-2xl border bg-white p-4 shadow-xl {{ $toast['type'] === 'success' ? 'border-emerald-200' : 'border-rose-200' }}">
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $toast['type'] === 'success' ? 'bg-emerald-500' : 'bg-rose-500' }} text-white">
                    <i data-lucide="{{ $toast['type'] === 'success' ? 'check' : 'alert-triangle' }}"
                        class="h-4.5 w-4.5"></i>
                </span>
                <div class="min-w-0 flex-1 pt-0.5">
                    <p
                        class="text-sm font-semibold {{ $toast['type'] === 'success' ? 'text-emerald-900' : 'text-rose-900' }}">
                        {{ $toast['type'] === 'success' ? __('Success') : __('Something went wrong') }}</p>
                    <p class="mt-0.5 text-sm leading-snug text-slate-600">{{ $toast['message'] }}</p>
                </div>
                <button @click="show = false" class="text-slate-300 transition hover:text-slate-500">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
        @endforeach
    </div>
@endif
