@props([
    'name',
    'selected' => '',
    'placeholder' => 'Select...',
    'options' => [],
    'autosubmit' => false,
])

@php
    $sel     = (string) ($selected ?? '');
    $found   = collect($options)->first(fn($o) => (string) $o['value'] === $sel);
    $initLabel = $found ? $found['label'] : $placeholder;
    $jsOpts  = collect($options)
        ->map(fn($o) => ['value' => (string) $o['value'], 'label' => (string) $o['label']])
        ->values()
        ->all();
@endphp

<div
    x-data="{
        open: false,
        value: {{ json_encode($sel) }},
        label: {{ json_encode($initLabel) }},
        isPlaceholder: {{ json_encode(!$found) }},
        options: {{ json_encode($jsOpts) }},
        select(opt) {
            this.value = opt.value;
            this.label = opt.label;
            this.isPlaceholder = false;
            this.open = false;
            this.$dispatch('change');
            @if($autosubmit)
                // Alpine flushes :value to the hidden input on the next tick, so
                // submitting synchronously here sent the *previous* selection — the
                // filter appeared to do nothing because the query string never changed.
                this.$nextTick(() => this.$el.closest('form').submit());
            @endif
        }
    }"
    @click.outside="open = false"
    {{ $attributes->class(['relative w-full']) }}
>
    <input type="hidden" name="{{ $name }}" :value="value">

    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition hover:border-indigo-300 focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100"
    >
        <span :class="isPlaceholder ? 'text-slate-400' : 'text-slate-900'" x-text="label"></span>
        <i data-lucide="chevron-down"
           class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200"
           :class="{ 'rotate-180': open }"></i>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute z-50 mt-1.5 w-full overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg"
    >
        <template x-for="opt in options" :key="opt.value">
            <button
                type="button"
                @click="select(opt)"
                class="flex w-full items-center gap-3 px-4 py-2.5 text-sm transition"
                :class="value === opt.value
                    ? 'bg-indigo-50 text-indigo-700 font-medium'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
            >
                <span
                    class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 transition"
                    :class="value === opt.value ? 'border-indigo-500 bg-indigo-500' : 'border-slate-300'"
                >
                    <span x-show="value === opt.value" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </span>
                <span x-text="opt.label"></span>
            </button>
        </template>
    </div>
</div>
