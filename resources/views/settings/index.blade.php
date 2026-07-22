@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">{{ __('Settings') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Manage your account, notifications, and security preferences.') }}</p>
    </div>

    {{-- Save feedback toast (triggered by background saves via the dc-toast window event) --}}
    <div x-data="{ show: false, message: '', error: false, timer: null,
            pop(msg, err = false) { this.message = msg; this.error = err; this.show = true; clearTimeout(this.timer); this.timer = setTimeout(() => this.show = false, 4000) } }"
        @dc-toast.window="pop($event.detail.message, $event.detail.error ?? false)"
        @if (session('success')) x-init="pop(@js(session('success')))" @endif
        x-show="show" x-cloak
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 -translate-y-2"
        :class="error ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'"
        class="fixed inset-x-0 top-6 z-50 mx-auto flex w-fit items-center gap-3 rounded-xl border px-4 py-3 text-sm shadow-lg">
        <i data-lucide="circle-check" class="h-4 w-4 shrink-0" x-show="!error"></i>
        <i data-lucide="circle-alert" class="h-4 w-4 shrink-0" x-show="error" x-cloak></i>
        <span x-text="message"></span>
    </div>

    <div class="mt-7 grid grid-cols-1 gap-6 lg:grid-cols-[220px_1fr]">

        <aside class="lg:sticky lg:top-24 lg:self-start"
            x-data="{
                active: 'preferences',
                init() {
                    const sections = document.querySelectorAll('[data-spy]');
                    const obs = new IntersectionObserver(entries => {
                        entries.forEach(e => { if (e.isIntersecting) this.active = e.target.dataset.spy; });
                    }, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });
                    sections.forEach(s => obs.observe(s));
                }
            }">
            <nav class="flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:gap-1 lg:overflow-visible lg:pb-0">
                @php
                    $sections = [
                        ['id' => 'preferences',   'icon' => 'sliders-horizontal', 'label' => __('Preferences')],
                        ['id' => 'notifications', 'icon' => 'bell',           'label' => __('Notifications')],
                        ['id' => 'security',      'icon' => 'shield',         'label' => __('Security')],
                        ['id' => 'faq',           'icon' => 'circle-help',    'label' => __('FAQ')],
                        ['id' => 'guide',         'icon' => 'book-open',      'label' => __('User guide')],
                        ['id' => 'danger',        'icon' => 'triangle-alert', 'label' => __('Danger zone')],
                    ];
                @endphp
                @foreach ($sections as $sec)
                    <a href="#{{ $sec['id'] }}" @click="active = '{{ $sec['id'] }}'"
                        class="flex items-center gap-2.5 whitespace-nowrap rounded-xl px-3.5 py-2.5 text-sm font-medium transition"
                        :class="active === '{{ $sec['id'] }}'
                            ? 'bg-white text-indigo-600 shadow-card'
                            : 'text-slate-500 hover:bg-white/70 hover:text-slate-900'">
                        <i data-lucide="{{ $sec['icon'] }}" class="h-4 w-4 shrink-0"></i>
                        {{ $sec['label'] }}
                        <span x-show="active === '{{ $sec['id'] }}'"
                            class="ml-auto h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0 space-y-6">
        {{-- Account & notifications: auto-saves in the background on any change --}}
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-6"
            x-data @submit.prevent @change="$nextTick(() => dcSaveSettings($el))">
            @csrf
            @method('PUT')

            <section id="preferences" data-spy="preferences" class="scroll-mt-24 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"><i data-lucide="sliders-horizontal" class="h-4.5 w-4.5"></i></span>
                    <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Preferences') }}</h2>
                </div>
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div @change.stop="$nextTick(() => dcChangeLanguage($el.querySelector('input[name=locale]').value))">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Language') }}</label>
                        <x-select
                            name="locale"
                            :selected="session('locale', 'en')"
                            :options="[
                                ['value' => 'en', 'label' => __('English')],
                                ['value' => 'id', 'label' => __('Bahasa Indonesia')],
                            ]"
                        />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Currency') }}</label>
                        <x-select
                            name="currency"
                            :selected="old('currency', auth()->user()->currency)"
                            :options="[
                                ['value' => 'IDR', 'label' => __('IDR (Indonesian Rupiah)')],
                                ['value' => 'USD', 'label' => __('USD (US Dollar)')],
                            ]"
                        />
                    </div>
                </div>
            </section>

            <section id="notifications" data-spy="notifications" class="scroll-mt-24 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600"><i data-lucide="bell" class="h-4.5 w-4.5"></i></span>
                    <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Notifications') }}</h2>
                </div>
                <div class="mt-3 divide-y divide-slate-100">
                    @php
                        $prefs = auth()->user();
                        $toggles = [
                            ['name' => 'notify_sales',    'title' => __('Dataset sales'),    'desc' => __('When someone purchases one of your datasets'),   'on' => (bool) $prefs->notify_sales],
                            ['name' => 'notify_rewards',  'title' => __('Entry rewards'),    'desc' => __('When you earn a reward for a submitted entry'),   'on' => (bool) $prefs->notify_rewards],
                            ['name' => 'notify_cleaning', 'title' => __('Half Clean complete'), 'desc' => __('When your Half Clean pipeline finishes'),     'on' => (bool) $prefs->notify_cleaning],
                            ['name' => 'notify_marketing','title' => __('Marketing updates'),'desc' => __('Occasional product news and feature announcements'),'on' => (bool) $prefs->notify_marketing],
                        ];
                    @endphp
                    @foreach ($toggles as $t)
                        <div class="flex items-center justify-between gap-4 py-4" x-data="{ on: {{ $t['on'] ? 'true' : 'false' }} }">
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $t['title'] }}</p>
                                <p class="text-xs text-slate-400">{{ $t['desc'] }}</p>
                            </div>
                            <button type="button" @click="on = !on; $dispatch('change')" :class="on ? 'dc-spectrum' : 'bg-slate-200'" class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition">
                                <span :class="on ? 'translate-x-5' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition"></span>
                            </button>
                            <input type="hidden" :name="'{{ $t['name'] }}'" :value="on ? 1 : 0">
                        </div>
                    @endforeach
                </div>
            </section>
        </form>

        {{-- Password change: separate form with its own confirm popup, saved in the background --}}
        <form method="POST" action="{{ route('settings.update') }}"
            x-data="{ confirmOpen: false }" @submit.prevent="confirmOpen = true">
            @csrf
            @method('PUT')

            <section id="security" data-spy="security" class="scroll-mt-24 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600"><i data-lucide="shield" class="h-4.5 w-4.5"></i></span>
                    <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Security') }}</h2>
                </div>
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div x-data="{ show: false }">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">New password</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" placeholder="••••••••••" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-11 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i data-lucide="eye" class="h-4 w-4" x-show="!show"></i>
                                <i data-lucide="eye-off" class="h-4 w-4" x-show="show" x-cloak></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-data="{ show: false }">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Confirm password</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" placeholder="••••••••••" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-11 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i data-lucide="eye" class="h-4 w-4" x-show="!show"></i>
                                <i data-lucide="eye-off" class="h-4 w-4" x-show="show" x-cloak></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="button" @click="confirmOpen = true"
                        class="dc-spectrum inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                        <i data-lucide="key-round" class="h-4 w-4"></i> {{ __('Update password') }}
                    </button>
                </div>

                @include('settings.partials.confirm-save')
            </section>
        </form>

            <section id="faq" data-spy="faq" class="scroll-mt-24 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-500">
                        <i data-lucide="circle-help" class="h-4.5 w-4.5"></i>
                    </span>
                    <div>
                        <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Frequently Asked Questions') }}</h2>
                        <p class="text-xs text-slate-400">{{ __('Everything you need to know about DataCore.') }}</p>
                    </div>
                </div>

                @php
                    $faqs = [
                        ['q' => __('What is DataCore?'),                                                          'a' => __('DataCore is a data marketplace where you can collect survey responses, refine raw data through an automated cleaning pipeline, and sell or buy high-quality datasets. Collectors earn per-entry rewards; buyers get clean, structured data ready for analysis.')],
                        ['q' => __('What is the difference between Half Clean and Full Clean?'),                   'a' => __('Half Clean (free) strips personally identifiable information and generates a quality score for each entry. Full Clean (paid) goes further: it removes duplicates, validates data types, casts values to the correct format, and normalises text, producing a dataset that is truly market-ready.')],
                        ['q' => __('How do I earn rewards from surveys?'),                                         'a' => __('Browse the Surveys page for ongoing collections that offer per-entry rewards. Fill out the survey and submit; the reward is credited to your wallet automatically once the entry is accepted. Minimum reward amounts are set by the collection creator.')],
                        ['q' => __('How do I publish a dataset to the marketplace?'),                              'a' => __('Create a collection, switch it to Collecting responses to start gathering data, then run at least Half Clean on the entries. Once cleaned, change the status to For sale and set a price. Your dataset will appear in the marketplace immediately.')],
                        ['q' => __('How do I top up my wallet?'),                                                  'a' => __('Go to Wallet → Top up balance. Choose an amount and a payment channel (Virtual Account, QRIS, or E-wallet). You will receive payment instructions and a reference code. After paying, click "I\'ve paid" to confirm and the balance will be credited instantly.')],
                        ['q' => __('How long do withdrawals take?'),                                               'a' => __('Withdrawal requests are processed within 1–3 business days. The funds are debited from your wallet immediately when you submit the request, and arrive in your bank account once the transfer clears. The minimum withdrawal amount is Rp 50,000.')],
                        ['q' => __('What happens to my reward budget if I stop a survey early?'),                  'a' => __('When you end a survey or move it back to Draft, DataCore calculates how many entries were submitted and refunds the unused portion of the reward pool to your wallet automatically.')],
                        ['q' => __('How does identity verification work?'),                                        'a' => __('Submit a photo of a government-issued ID (passport, national ID, or driving licence) and a selfie holding it. Our team reviews the submission; approval usually takes 1 business day. Once verified you earn the "Verified Contributor" badge, which increases buyer confidence in your datasets.')],
                        ['q' => __('Can I edit a collection after it goes live?'),                                 'a' => __('Yes. You can edit the title, description, category, and price at any time. However, reward amount and respondent target are locked once the escrow budget has been debited to prevent underpayment to respondents.')],
                        ['q' => __('How is the quality score calculated?'),                                        'a' => __('Quality score is computed during the Half Clean step. It measures completeness (how many required fields are filled), consistency (type correctness), and uniqueness (absence of near-duplicates) across all entries, giving a score between 0 and 1.')],
                    ];
                @endphp

                <div class="mt-5 divide-y divide-slate-100" x-data="{ open: null }">
                    @foreach ($faqs as $i => $faq)
                        <div class="py-4">
                            <button type="button"
                                @click="open = open === {{ $i }} ? null : {{ $i }}"
                                class="flex w-full items-start justify-between gap-4 text-left">
                                <span class="text-sm font-semibold text-slate-900">{{ $faq['q'] }}</span>
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition"
                                    :class="open === {{ $i }} ? 'rotate-45 bg-indigo-100 text-indigo-600' : ''">
                                    <i data-lucide="plus" class="h-3 w-3"></i>
                                </span>
                            </button>
                            <div x-show="open === {{ $i }}" x-cloak x-collapse
                                class="mt-3 text-sm leading-relaxed text-slate-500">
                                {{ $faq['a'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="guide" data-spy="guide" class="scroll-mt-24 overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card"
                x-data="{ open: null }">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                <i data-lucide="book-open" class="h-4.5 w-4.5"></i>
                            </span>
                            <div>
                                <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('User Guide') }}</h2>
                                <p class="text-xs text-slate-400">{{ __('Everything you need to master DataCore, from setup to selling.') }}</p>
                            </div>
                        </div>
                        <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500 sm:inline-block">8 {{ __('topics') }}</span>
                    </div>

                    {{-- Topic pills --}}
                    <div class="mt-5 flex flex-wrap gap-2">
                        @php
                            $guide = \App\Support\GuideContent::sections();
                        @endphp
                        @foreach ($guide as $j => $pill)
                            <button type="button"
                                @click="open = open === {{ $j }} ? null : {{ $j }}; $nextTick(() => { if (open === {{ $j }}) $el.closest('#guide').querySelector('[data-section=\'{{ $j }}\']').scrollIntoView({ behavior: 'smooth', block: 'nearest' }) })"
                                class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium ring-1 ring-inset transition hover:brightness-95 {{ $pill['bg'] }} {{ $pill['color'] }} {{ $pill['ring'] }}"
                                :class="open === {{ $j }} ? 'ring-2' : ''">
                                <i data-lucide="{{ $pill['icon'] }}" class="h-3 w-3"></i>
                                {{ $pill['title'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="divide-y divide-slate-100 border-t border-slate-100 bg-white">
                    @foreach ($guide as $i => $section)
                        <div data-section="{{ $i }}">
                            <div data-section-header="{{ $i }}" class="group flex w-full items-center gap-4 px-6 py-5 transition"
                                :class="open === {{ $i }} ? 'bg-gradient-to-r {{ $section['gradient'] }}' : 'hover:bg-slate-50/70'">
                                {{-- Colored accent bar --}}
                                <div class="w-1 self-stretch rounded-full transition-all"
                                    :class="open === {{ $i }} ? '{{ $section['accent'] }} opacity-100' : 'bg-slate-200 opacity-0 group-hover:opacity-50'"></div>

                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $section['bg'] }} transition group-hover:scale-105"
                                    :class="open === {{ $i }} ? 'shadow-sm scale-105' : ''">
                                    <i data-lucide="{{ $section['icon'] }}" class="h-5 w-5 {{ $section['color'] }}"></i>
                                </span>

                                {{-- Title + summary (clickable to open accordion) --}}
                                <button type="button" class="min-w-0 flex-1 text-left"
                                    @click="open = open === {{ $i }} ? null : {{ $i }}">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-bold text-slate-900">{{ $section['title'] }}</p>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $section['badge'] }}"
                                            x-show="open !== {{ $i }}">{{ count($section['items']) }} {{ __('steps') }}</span>
                                    </div>
                                    <p class="mt-0.5 truncate text-xs text-slate-400 transition"
                                        :class="open === {{ $i }} ? 'text-slate-500' : ''">{{ $section['summary'] }}</p>
                                </button>

                                {{-- Tour button --}}
                                <button type="button" @click.stop="$store.tour.start({{ $i }})"
                                    class="hidden shrink-0 items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 sm:flex">
                                    <i data-lucide="play-circle" class="h-3.5 w-3.5"></i> {{ __('Tour') }}
                                </button>

                                <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}"
                                    class="ml-1 shrink-0">
                                    <i data-lucide="chevron-down"
                                        class="h-4 w-4 transition-transform {{ $section['color'] }} opacity-60"
                                        :class="open === {{ $i }} ? 'rotate-180 opacity-100' : 'group-hover:opacity-80'"></i>
                                </button>
                            </div>

                            <div x-show="open === {{ $i }}" x-cloak x-collapse>
                                <div class="bg-gradient-to-b {{ $section['gradient'] }} px-6 pb-6 pt-1">
                                    <div class="grid gap-3 sm:grid-cols-3">
                                        @foreach ($section['items'] as $k => $item)
                                            <div class="relative rounded-2xl border bg-white p-5 shadow-sm ring-1 {{ $section['ring'] }}">
                                                <div class="mb-3 flex items-center gap-2.5">
                                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[11px] font-bold {{ $section['badge'] }}">{{ $k + 1 }}</span>
                                                    <p class="text-sm font-bold leading-snug text-slate-900">{{ $item['title'] }}</p>
                                                </div>
                                                <p class="text-sm leading-relaxed text-slate-500">{{ $item['body'] }}</p>
                                                @if (isset($item['link']))
                                                    <a href="{{ route($item['link']['route']) }}"
                                                        class="mt-3 inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $section['link_color'] }}">
                                                        {{ $item['link']['label'] }}
                                                        <i data-lucide="arrow-right" class="h-3 w-3"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="danger" data-spy="danger" class="scroll-mt-24 rounded-2xl border border-rose-200 bg-rose-50/40 p-6">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                        <i data-lucide="triangle-alert" class="h-4.5 w-4.5"></i>
                    </span>
                    <h2 class="font-display text-lg font-semibold text-rose-900">{{ __('Danger zone') }}</h2>
                </div>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-rose-700/90">{{ __('Permanently delete your account, datasets, and wallet history. This cannot be undone.') }}</p>
                    <button type="button"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-rose-300 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-600 hover:text-white">
                        <i data-lucide="trash-2" class="h-4 w-4"></i> {{ __('Delete account') }}
                    </button>
                </div>
            </section>
        </div>
    </div>

    {{-- Language change: posts to the LanguageController, then navigates to the correctly localized URL --}}
    <script>
        async function dcChangeLanguage(locale) {
            const toast = (message, error = false) =>
                window.dispatchEvent(new CustomEvent('dc-toast', { detail: { message, error } }));
            try {
                const res = await fetch(@js(route('language.update')), {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ locale }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    toast(data.message || @js(__('Failed to save.')), true);
                    return;
                }
                window.location.href = data.redirect;
            } catch (e) {
                toast(@js(__('Failed to save.')), true);
            }
        }
    </script>

    {{-- Background save: posts via fetch, then soft-refreshes (scroll position and toast survive the reload) --}}
    <script>
        async function dcSaveSettings(form) {
            const toast = (message, error = false) =>
                window.dispatchEvent(new CustomEvent('dc-toast', { detail: { message, error } }));
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    toast(data.message || @js(__('Failed to save.')), true);
                    return false;
                }
                sessionStorage.setItem('dc-settings-scroll', window.scrollY);
                sessionStorage.setItem('dc-settings-toast', data.message || @js(__('Settings saved.')));
                window.location.reload();
                return true;
            } catch (e) {
                toast(@js(__('Failed to save.')), true);
                return false;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const y = sessionStorage.getItem('dc-settings-scroll');
            if (y !== null) {
                sessionStorage.removeItem('dc-settings-scroll');
                window.scrollTo({ top: parseInt(y, 10), behavior: 'instant' });
            }
            const msg = sessionStorage.getItem('dc-settings-toast');
            if (msg) {
                sessionStorage.removeItem('dc-settings-toast');
                let shown = false;
                const show = () => { if (!shown) { shown = true; window.dispatchEvent(new CustomEvent('dc-toast', { detail: { message: msg } })); } };
                document.addEventListener('alpine:initialized', show, { once: true });
                setTimeout(show, 700);
            }
        });
    </script>
@endsection
