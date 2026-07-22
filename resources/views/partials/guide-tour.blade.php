{{-- Cross-page product tour: data + spotlight + popup. Driven by the global $store.tour (resources/js/tour.js). --}}
<script id="dc-guide-data" type="application/json">{!! json_encode(\App\Support\GuideContent::forTour()) !!}</script>

<template x-if="$store.tour.active && $store.tour.bound">
    <div class="pointer-events-none fixed z-[49] transition-all duration-300"
        :style="`top:${$store.tour.bound.top - 6}px;left:${$store.tour.bound.left - 6}px;width:${$store.tour.bound.width + 12}px;height:${$store.tour.bound.height + 12}px;border-radius:14px;box-shadow:0 0 0 9999px rgba(8,10,20,0.80);border:1.5px solid rgba(139,92,246,0.55);`">
    </div>
</template>
<div x-show="$store.tour.active && !$store.tour.bound" x-cloak
    class="pointer-events-none fixed inset-0 z-[49] bg-slate-950/75 transition-opacity"></div>

<div x-show="$store.tour.active" x-cloak @keydown.escape.window="$store.tour.close()" @click.stop
    class="fixed left-1/2 z-50 w-[26rem] max-w-[calc(100vw-32px)] max-h-[calc(100vh-32px)] -translate-x-1/2 overflow-x-hidden overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-black/10 lg:left-[calc(50%+9rem)]"
    :style="$store.tour.bound
        ? `top:${Math.max(16, Math.min($store.tour.bound.bottom + 14, window.innerHeight - 16 - $refs.tourCard.offsetHeight))}px`
        : 'top:50%;transform:translate(-50%,-50%)'"
    x-ref="tourCard">

    <div x-show="$store.tour.bound" class="pointer-events-none absolute -top-[9px] left-1/2 z-10 -translate-x-1/2">
        <div class="h-0 w-0 border-x-[9px] border-b-[9px] border-x-transparent border-b-white drop-shadow-sm"></div>
    </div>

    {{-- Gradient header --}}
    <div class="relative overflow-hidden px-6 pb-6 pt-7" :style="$store.tour.currentSection?.headerCss || 'background:linear-gradient(135deg,#6366f1,#7c3aed)'">
        <div class="pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/4 h-20 w-20 rounded-full bg-black/10 blur-xl"></div>

        <button @click="$store.tour.close()" class="absolute right-4 top-4 flex h-7 w-7 items-center justify-center rounded-full bg-white/15 text-white/70 transition hover:bg-white/25 hover:text-white">
            <i data-lucide="x" class="h-3.5 w-3.5"></i>
        </button>

        <div class="absolute left-6 top-4 flex items-center gap-1.5 rounded-full bg-black/20 px-2.5 py-1">
            <span class="font-mono text-[10px] font-semibold text-white/80" x-text="`${$store.tour.step + 1} of ${$store.tour.currentSection?.items?.length ?? 1}`"></span>
        </div>

        <div class="mt-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 shadow-sm backdrop-blur-sm">
            {{-- Pre-render all 8 section icons; Alpine toggles visibility --}}
            <i data-lucide="rocket"         class="h-6 w-6 text-white" x-show="$store.tour.section === 0"></i>
            <i data-lucide="clipboard-list" class="h-6 w-6 text-white" x-show="$store.tour.section === 1"></i>
            <i data-lucide="database"       class="h-6 w-6 text-white" x-show="$store.tour.section === 2"></i>
            <i data-lucide="sparkles"       class="h-6 w-6 text-white" x-show="$store.tour.section === 3"></i>
            <i data-lucide="compass"        class="h-6 w-6 text-white" x-show="$store.tour.section === 4"></i>
            <i data-lucide="gift"           class="h-6 w-6 text-white" x-show="$store.tour.section === 5"></i>
            <i data-lucide="wallet"         class="h-6 w-6 text-white" x-show="$store.tour.section === 6"></i>
            <i data-lucide="shield-check"   class="h-6 w-6 text-white" x-show="$store.tour.section === 7"></i>
        </div>

        <p class="mt-3 text-xs font-semibold uppercase tracking-widest text-white/60" x-text="$store.tour.currentSection?.title"></p>
    </div>

    {{-- Step content --}}
    <div class="px-6 py-5">
        <p class="font-display text-base font-bold text-slate-900" x-text="$store.tour.currentStep?.title"></p>
        <p class="mt-2 text-sm leading-relaxed text-slate-500" x-text="$store.tour.currentStep?.body"></p>

        <template x-if="$store.tour.currentStep?.link">
            <a :href="$store.tour.currentStep.link.url"
                class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3.5 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                <span x-text="$store.tour.currentStep.link.label"></span>
                <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
            </a>
        </template>
    </div>

    {{-- Footer: dot progress + navigation --}}
    <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
        <div class="flex items-center gap-1.5">
            <template x-for="(s, idx) in ($store.tour.currentSection?.items ?? [])" :key="idx">
                <button type="button" @click="$store.tour.goTo(idx)"
                    class="rounded-full transition-all duration-200"
                    :class="idx === $store.tour.step ? 'w-5 h-2 bg-indigo-500' : 'w-2 h-2 bg-slate-200 hover:bg-slate-300'">
                </button>
            </template>
        </div>
        <div class="flex items-center gap-2">
            <button @click="$store.tour.prev()" x-show="$store.tour.step > 0"
                class="flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                <i data-lucide="arrow-left" class="h-3 w-3"></i> Back
            </button>
            <button @click="$store.tour.next()"
                class="dc-spectrum flex items-center gap-1.5 rounded-lg px-4 py-1.5 text-xs font-semibold text-white shadow-glow transition hover:brightness-110">
                <span x-text="$store.tour.isLastStep ? 'Finish ✓' : 'Next'"></span>
                <i data-lucide="arrow-right" class="h-3 w-3" x-show="!$store.tour.isLastStep"></i>
            </button>
        </div>
    </div>
</div>
