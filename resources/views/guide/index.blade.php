@extends('layouts.app')

@section('title', 'User Guide')

@section('content')
    {{-- Header --}}
    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">User Guide</h1>
        <p class="mt-1 text-sm text-slate-500">Everything you need to get the most out of DataCore, from collecting data to earning on the marketplace.</p>
    </div>

    <div class="mt-7 grid grid-cols-1 gap-8 lg:grid-cols-[220px_1fr]">

        {{-- Sticky sidebar nav --}}
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <nav class="flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:gap-1 lg:overflow-visible lg:pb-0">
                @php
                    $sections = [
                        ['href' => '#getting-started',  'icon' => 'rocket',       'label' => 'Getting started'],
                        ['href' => '#surveys',           'icon' => 'clipboard-list','label' => 'Creating surveys'],
                        ['href' => '#collecting',        'icon' => 'database',     'label' => 'Collecting data'],
                        ['href' => '#pipeline',          'icon' => 'sparkles',     'label' => 'Refining data'],
                        ['href' => '#marketplace',       'icon' => 'compass',      'label' => 'Publishing & selling'],
                        ['href' => '#earning',           'icon' => 'gift',         'label' => 'Earning rewards'],
                        ['href' => '#wallet',            'icon' => 'wallet',       'label' => 'Wallet & payments'],
                        ['href' => '#verification',      'icon' => 'shield-check', 'label' => 'Verification'],
                    ];
                @endphp
                @foreach ($sections as $sec)
                    <a href="{{ $sec['href'] }}"
                        class="flex items-center gap-2.5 whitespace-nowrap rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-white hover:text-slate-900">
                        <i data-lucide="{{ $sec['icon'] }}" class="h-4 w-4 shrink-0"></i>
                        {{ $sec['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        {{-- Main content --}}
        <div class="min-w-0 space-y-10">

            {{-- 1. Getting started --}}
            <section id="getting-started" class="scroll-mt-24">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-glow">
                        <i data-lucide="rocket" class="h-5 w-5"></i>
                    </span>
                    <h2 class="font-display text-2xl font-semibold text-slate-900">Getting started</h2>
                </div>
                <p class="mt-2 text-sm text-slate-500">Set up your account so you can collect and sell data, or earn rewards by filling surveys.</p>

                <div class="mt-5 space-y-3">
                    @php
                        $steps = [
                            ['n' => '1', 'title' => 'Create your account', 'body' => 'Register with your email address. You\'ll receive a welcome notification in your activity feed once your account is active.'],
                            ['n' => '2', 'title' => 'Complete your profile', 'body' => 'Head to Profile and fill in your name, phone number, and date of birth. A complete profile builds trust with buyers and survey creators.', 'link' => ['route' => 'profile.edit', 'label' => 'Go to profile']],
                            ['n' => '3', 'title' => 'Verify your identity (recommended)', 'body' => 'Submit a government-issued ID to earn the Verified Contributor badge. Verified sellers see higher buyer confidence and conversion rates.', 'link' => ['route' => 'verification.index', 'label' => 'Start verification']],
                            ['n' => '4', 'title' => 'Top up your wallet', 'body' => 'Add funds via Virtual Account, QRIS, or E-wallet. You\'ll need a balance to escrow survey reward pools and to run Full Clean.', 'link' => ['route' => 'wallet.index', 'label' => 'Top up now']],
                        ];
                    @endphp
                    @foreach ($steps as $step)
                        <div class="flex gap-4 rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-mono text-xs font-bold text-indigo-600">{{ $step['n'] }}</span>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900">{{ $step['title'] }}</p>
                                <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $step['body'] }}</p>
                                @if (isset($step['link']))
                                    <a href="{{ route($step['link']['route']) }}" class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                        {{ $step['link']['label'] }} <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 2. Creating surveys --}}
            <section id="surveys" class="scroll-mt-24">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-glow">
                        <i data-lucide="clipboard-list" class="h-5 w-5"></i>
                    </span>
                    <h2 class="font-display text-2xl font-semibold text-slate-900">Creating surveys</h2>
                </div>
                <p class="mt-2 text-sm text-slate-500">Design a survey to collect exactly the data you need, with optional rewards to attract respondents.</p>

                <div class="mt-5 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                    <div class="space-y-5">
                        @php
                            $surveySteps = [
                                ['icon' => 'plus-circle', 'color' => 'text-indigo-500', 'title' => 'Open New Collection', 'body' => 'Go to My Collections → New Collection. Choose the Survey tab (or Import a CSV if you already have raw data).'],
                                ['icon' => 'list', 'color' => 'text-violet-500', 'title' => 'Build your questions', 'body' => 'Add questions using 7 types: Short text, Paragraph, Multiple choice, Checkbox, Number, Rating scale, or Date/Time. Mark required fields with the toggle.'],
                                ['icon' => 'gift', 'color' => 'text-emerald-500', 'title' => 'Set a reward (optional)', 'body' => 'Enter an amount per entry and a respondent target. DataCore will hold the total reward budget in escrow. You get unused funds back when the survey ends.'],
                                ['icon' => 'users', 'color' => 'text-sky-500', 'title' => 'Collect respondent metadata', 'body' => 'Enable optional metadata fields (age, gender, city, profession) to enrich each submission automatically without adding extra questions.'],
                                ['icon' => 'zap', 'color' => 'text-amber-500', 'title' => 'Launch as Ongoing', 'body' => 'Set the status to Ongoing to make your survey live immediately. Respondents can find it in the Surveys tab. Change to Draft to pause at any time.'],
                            ];
                        @endphp
                        @foreach ($surveySteps as $i => $step)
                            <div class="flex gap-4 {{ !$loop->last ? 'pb-5 border-b border-slate-100' : '' }}">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50">
                                    <i data-lucide="{{ $step['icon'] }}" class="h-4 w-4 {{ $step['color'] }}"></i>
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $step['title'] }}</p>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $step['body'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <a href="{{ route('collections.create') }}" class="dc-spectrum inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                            <i data-lucide="plus" class="h-4 w-4"></i> Create your first collection
                        </a>
                    </div>
                </div>
            </section>

            {{-- 3. Collecting data --}}
            <section id="collecting" class="scroll-mt-24">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 text-white shadow-glow">
                        <i data-lucide="database" class="h-5 w-5"></i>
                    </span>
                    <h2 class="font-display text-2xl font-semibold text-slate-900">Collecting data</h2>
                </div>
                <p class="mt-2 text-sm text-slate-500">Once your survey is live, respondents can discover and fill it. Here's how to monitor progress.</p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @php
                        $collectCards = [
                            ['icon' => 'share-2', 'title' => 'Share your survey', 'body' => 'Your survey appears automatically in the Surveys tab for all logged-in users. You can also share the direct link.'],
                            ['icon' => 'bar-chart-2', 'title' => 'Monitor via Analytics', 'body' => 'Open My Collections → ⋯ → Analytics to see total entries, a 30-day activity chart, and your completion rate against the target.'],
                            ['icon' => 'square-x', 'title' => 'End the survey', 'body' => 'Click End survey in the collection menu when you\'ve collected enough. Unused reward budget is refunded to your wallet instantly.'],
                            ['icon' => 'download', 'title' => 'Export raw data', 'body' => 'Download a CSV of all raw entries at any time from the collection menu, even before cleaning.'],
                        ];
                    @endphp
                    @foreach ($collectCards as $card)
                        <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50">
                                <i data-lucide="{{ $card['icon'] }}" class="h-4 w-4 text-indigo-500"></i>
                            </div>
                            <p class="mt-3 font-semibold text-slate-900">{{ $card['title'] }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $card['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 4. Refining data --}}
            <section id="pipeline" class="scroll-mt-24">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-fuchsia-500 to-pink-500 text-white shadow-glow">
                        <i data-lucide="sparkles" class="h-5 w-5"></i>
                    </span>
                    <h2 class="font-display text-2xl font-semibold text-slate-900">Refining data</h2>
                </div>
                <p class="mt-2 text-sm text-slate-500">DataCore's two-stage cleaning pipeline transforms raw survey responses into market-ready structured data.</p>

                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {{-- Half Clean --}}
                    <div class="rounded-2xl border border-indigo-200/70 bg-white p-6 shadow-card">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                <i data-lucide="shield-check" class="h-4 w-4"></i>
                            </span>
                            <div>
                                <p class="font-display font-semibold text-slate-900">Half Clean</p>
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Free</span>
                            </div>
                        </div>
                        <ul class="mt-4 space-y-2">
                            @foreach (['Strips personally identifiable information (PII)', 'Calculates a quality score per entry (0–1)', 'Flags incomplete or inconsistent responses', 'Required before publishing to the marketplace'] as $item)
                                <li class="flex items-start gap-2 text-sm text-slate-600">
                                    <i data-lucide="check" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500"></i>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-4 text-xs text-slate-400">Run from My Collections → Edit → Pipeline sidebar, or from the Marketplace detail page.</p>
                    </div>

                    {{-- Full Clean --}}
                    <div class="rounded-2xl border border-violet-200/70 bg-white p-6 shadow-card">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                                <i data-lucide="sparkles" class="h-4 w-4"></i>
                            </span>
                            <div>
                                <p class="font-display font-semibold text-slate-900">Full Clean</p>
                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-semibold text-violet-700">Paid</span>
                            </div>
                        </div>
                        <ul class="mt-4 space-y-2">
                            @foreach (['Removes near-duplicate rows', 'Validates & casts values to the correct data type', 'Normalises text and categorical values', 'Produces a detailed cleaning report'] as $item)
                                <li class="flex items-start gap-2 text-sm text-slate-600">
                                    <i data-lucide="check" class="mt-0.5 h-4 w-4 shrink-0 text-violet-500"></i>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-4 text-xs text-slate-400">Requires Half Clean to have been run first. Fee deducted from your wallet on confirmation.</p>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-amber-200/70 bg-amber-50/60 p-4 text-sm text-amber-800">
                    <i data-lucide="lightbulb" class="mr-1.5 inline h-4 w-4 text-amber-500"></i>
                    <strong>Tip:</strong> You can publish after Half Clean. Full Clean is optional but commands a higher price in the marketplace because buyers receive fully structured, deduplicated data.
                </div>
            </section>

            {{-- 5. Publishing & selling --}}
            <section id="marketplace" class="scroll-mt-24">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-sky-500 text-white shadow-glow">
                        <i data-lucide="compass" class="h-5 w-5"></i>
                    </span>
                    <h2 class="font-display text-2xl font-semibold text-slate-900">Publishing &amp; selling</h2>
                </div>
                <p class="mt-2 text-sm text-slate-500">Turn your cleaned data into a product anyone can discover and purchase.</p>

                <div class="mt-5 space-y-3">
                    @php
                        $pubSteps = [
                            ['n' => '1', 'title' => 'Run Half Clean first', 'body' => 'Publishing is locked until at least Half Clean has been run. This ensures every dataset in the marketplace meets a minimum quality bar.'],
                            ['n' => '2', 'title' => 'Set a price', 'body' => 'Go to Edit Collection and enter a price in IDR, or leave it at 0 to offer the dataset for free. You can update the price at any time.'],
                            ['n' => '3', 'title' => 'Switch status to Published', 'body' => 'Change the Visibility radio to "Published" and save. Your dataset appears immediately in the marketplace with a preview of the first few rows.'],
                            ['n' => '4', 'title' => 'Earn on every sale', 'body' => 'When a buyer purchases, the amount is credited to your wallet after the platform fee. You can withdraw to your bank account at any time.'],
                        ];
                    @endphp
                    @foreach ($pubSteps as $step)
                        <div class="flex gap-4 rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-mono text-xs font-bold text-emerald-700">{{ $step['n'] }}</span>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $step['title'] }}</p>
                                <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $step['body'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 6. Earning rewards --}}
            <section id="earning" class="scroll-mt-24">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-glow">
                        <i data-lucide="gift" class="h-5 w-5"></i>
                    </span>
                    <h2 class="font-display text-2xl font-semibold text-slate-900">Earning rewards</h2>
                </div>
                <p class="mt-2 text-sm text-slate-500">Fill surveys from other creators and get paid per entry, no setup required.</p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    @php
                        $earnCards = [
                            ['icon' => 'clipboard-list', 'step' => '01', 'title' => 'Browse surveys', 'body' => 'Go to the Surveys page and filter by category or reward amount. Ongoing surveys with available spots are open to fill.'],
                            ['icon' => 'pen-line', 'step' => '02', 'title' => 'Fill & submit', 'body' => 'Complete every required question and submit the form. Your entry is recorded immediately.'],
                            ['icon' => 'wallet', 'step' => '03', 'title' => 'Reward credited', 'body' => 'The per-entry reward is added to your wallet automatically. Check your balance and activity feed for confirmation.'],
                        ];
                    @endphp
                    @foreach ($earnCards as $card)
                        <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                            <span class="font-mono text-2xl font-bold text-slate-100">{{ $card['step'] }}</span>
                            <div class="mt-2 flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50">
                                <i data-lucide="{{ $card['icon'] }}" class="h-4 w-4 text-amber-500"></i>
                            </div>
                            <p class="mt-3 font-semibold text-slate-900">{{ $card['title'] }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $card['body'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex justify-start">
                    <a href="{{ route('surveys.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-amber-300 hover:text-amber-700">
                        <i data-lucide="clipboard-list" class="h-4 w-4 text-amber-500"></i> Browse open surveys
                    </a>
                </div>
            </section>

            {{-- 7. Wallet & payments --}}
            <section id="wallet" class="scroll-mt-24">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white shadow-glow">
                        <i data-lucide="wallet" class="h-5 w-5"></i>
                    </span>
                    <h2 class="font-display text-2xl font-semibold text-slate-900">Wallet &amp; payments</h2>
                </div>
                <p class="mt-2 text-sm text-slate-500">Your DataCore wallet holds your balance, tracks every transaction, and is the single place for payments and earnings.</p>

                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                        <h3 class="font-semibold text-slate-900">Top up</h3>
                        <p class="mt-1 text-sm text-slate-500">Choose an amount (min Rp {{ number_format(config('datacore.min_topup'), 0, ',', '.') }}, max Rp {{ number_format(config('datacore.max_topup'), 0, ',', '.') }}) and a channel:</p>
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            @foreach ([['icon' => 'landmark', 'name' => 'Virtual Account', 'desc' => 'BCA, BNI, Mandiri: transfer from any bank'], ['icon' => 'qr-code', 'name' => 'QRIS', 'desc' => 'Scan with any QRIS-compatible app'], ['icon' => 'smartphone', 'name' => 'E-wallet', 'desc' => 'GoPay, OVO, Dana']] as $ch)
                                <div class="flex items-start gap-3 rounded-xl border border-slate-200 p-3">
                                    <i data-lucide="{{ $ch['icon'] }}" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500"></i>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $ch['name'] }}</p>
                                        <p class="text-xs text-slate-400">{{ $ch['desc'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs text-slate-400">After initiating a top-up you'll see payment instructions with a reference code. Click <em>"I've paid"</em> after completing the transfer to credit your wallet.</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                        <h3 class="font-semibold text-slate-900">Withdrawals</h3>
                        <p class="mt-1 text-sm text-slate-500">Request a bank transfer from the Wallet page. Enter the amount (min Rp {{ number_format(config('datacore.min_withdrawal'), 0, ',', '.') }}), your bank name, account number, and account holder name. Funds are debited immediately and arrive within <strong>1–3 business days</strong>.</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                        <h3 class="font-semibold text-slate-900">Transaction types</h3>
                        <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-3">
                            @foreach ([
                                ['type' => 'Top-up', 'desc' => 'Funds added to your wallet'],
                                ['type' => 'Reward', 'desc' => 'Earned from filling a survey'],
                                ['type' => 'Escrow', 'desc' => 'Reward pool held for your survey'],
                                ['type' => 'Escrow refund', 'desc' => 'Unused pool returned after survey ends'],
                                ['type' => 'Payment', 'desc' => 'Dataset purchase by a buyer'],
                                ['type' => 'Withdrawal', 'desc' => 'Bank transfer request'],
                            ] as $tx)
                                <div class="py-2">
                                    <p class="font-medium text-slate-900">{{ $tx['type'] }}</p>
                                    <p class="text-xs text-slate-400">{{ $tx['desc'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- 8. Verification --}}
            <section id="verification" class="scroll-mt-24">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-glow">
                        <i data-lucide="shield-check" class="h-5 w-5"></i>
                    </span>
                    <h2 class="font-display text-2xl font-semibold text-slate-900">Verification</h2>
                </div>
                <p class="mt-2 text-sm text-slate-500">Get the Verified Contributor badge to signal trustworthiness to buyers and survey creators.</p>

                <div class="mt-5 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                    <div class="space-y-5">
                        @foreach ([
                            ['n' => '1', 'title' => 'Prepare your ID', 'body' => 'You\'ll need a government-issued photo ID: a passport, national identity card, or driving licence. The ID number and your full name must be clearly readable.'],
                            ['n' => '2', 'title' => 'Take a selfie with your ID', 'body' => 'Hold the ID flat beside your face (not obscuring it). Make sure the photo is well-lit and both your face and the ID text are in focus.'],
                            ['n' => '3', 'title' => 'Submit for review', 'body' => 'Upload both images on the Verification page. Our team reviews submissions within 1 business day and you\'ll get a notification with the outcome.'],
                            ['n' => '4', 'title' => 'Earn the badge', 'body' => 'Once approved, a Verified Contributor badge appears on your profile, your datasets, and beside your name in reviews. It stays active as long as your account is in good standing.'],
                        ] as $step)
                            <div class="flex gap-4 {{ !$loop->last ? 'border-b border-slate-100 pb-5' : '' }}">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-mono text-xs font-bold text-emerald-700">{{ $step['n'] }}</span>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $step['title'] }}</p>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $step['body'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <a href="{{ route('verification.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                            <i data-lucide="shield-check" class="h-4 w-4"></i> Start verification
                        </a>
                    </div>
                </div>
            </section>

            {{-- Bottom links --}}
            <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                <p class="font-display text-base font-semibold text-slate-900">Still have questions?</p>
                <p class="mt-1 text-sm text-slate-500">Check the FAQ in Settings for quick answers, or explore the platform: everything is designed to guide you.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('settings') }}#faq" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:text-indigo-700">
                        <i data-lucide="circle-help" class="h-4 w-4 text-slate-400"></i> Read the FAQ
                    </a>
                    <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:text-indigo-700">
                        <i data-lucide="compass" class="h-4 w-4 text-slate-400"></i> Explore the marketplace
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection
