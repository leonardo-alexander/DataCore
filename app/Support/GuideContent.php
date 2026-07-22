<?php

namespace App\Support;

class GuideContent
{
    /**
     * The 8 user-guide sections. Each item may carry:
     * - route:  the named route the tour should navigate to for this step (null = stay put)
     * - target: the data-tour="..." key of the element to spotlight on that page (null = no spotlight)
     * - link:   an optional call-to-action shown inside the tour popup / accordion card
     */
    public static function sections(): array
    {
        return [
            [
                'icon' => 'rocket',
                'color' => 'text-indigo-600', 'bg' => 'bg-indigo-100', 'accent' => 'bg-indigo-500',
                'gradient' => 'from-indigo-50 to-white', 'badge' => 'bg-indigo-100 text-indigo-700',
                'ring' => 'ring-indigo-200', 'link_color' => 'text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100',
                'header_css' => 'background:linear-gradient(135deg,#6366f1,#7c3aed)',
                'title' => 'Getting started',
                'summary' => 'Set up your account, complete your profile, verify your identity, and top up your wallet.',
                'items' => [
                    ['title' => 'Complete your profile', 'body' => 'Head to Profile and fill in your name, phone number, and date of birth. A complete profile builds trust with buyers and survey creators.', 'route' => 'profile.edit', 'target' => 'profile-fields', 'link' => ['route' => 'profile.edit', 'label' => 'Edit profile']],
                    ['title' => 'Verify your identity', 'body' => 'Submit a government-issued ID (passport, national ID, or driving licence) and a selfie to earn the Verified Contributor badge. Approval takes 1 business day.', 'route' => 'verification.index', 'target' => 'verification-form', 'link' => ['route' => 'verification.index', 'label' => 'Start verification']],
                    ['title' => 'Top up your wallet', 'body' => 'Add funds via Virtual Account, QRIS, or E-wallet. You need a balance to run Full Clean and to fund survey reward pools.', 'route' => 'wallet.index', 'target' => 'wallet-topup', 'link' => ['route' => 'wallet.index', 'label' => 'Top up now']],
                ],
            ],
            [
                'icon' => 'clipboard-list',
                'color' => 'text-violet-600', 'bg' => 'bg-violet-100', 'accent' => 'bg-violet-500',
                'gradient' => 'from-violet-50 to-white', 'badge' => 'bg-violet-100 text-violet-700',
                'ring' => 'ring-violet-200', 'link_color' => 'text-violet-600 hover:text-violet-700 bg-violet-50 hover:bg-violet-100',
                'header_css' => 'background:linear-gradient(135deg,#7c3aed,#9333ea)',
                'title' => 'Creating surveys',
                'summary' => 'Build a survey from scratch with 7 question types, set rewards, and launch it to start collecting.',
                'items' => [
                    ['title' => 'Add questions', 'body' => 'Choose from Short text, Paragraph, Multiple choice, Checkbox, Number, Rating scale, or Date/Time. Mark required fields with the toggle.', 'route' => 'collections.create', 'target' => 'survey-questions'],
                    ['title' => 'Set a reward', 'body' => 'Enter an amount per entry and a respondent target. DataCore escrows the total budget. Unused funds are refunded when you end the survey.', 'route' => 'collections.create', 'target' => 'survey-reward'],
                    ['title' => 'Launch to start collecting', 'body' => 'Set the status to Collecting responses to go live immediately. Respondents can find your survey on the Surveys page. Switch to Draft to pause at any time.', 'route' => 'collections.create', 'target' => 'survey-visibility', 'link' => ['route' => 'collections.create', 'label' => 'Create a collection']],
                ],
            ],
            [
                'icon' => 'database',
                'color' => 'text-sky-600', 'bg' => 'bg-sky-100', 'accent' => 'bg-sky-500',
                'gradient' => 'from-sky-50 to-white', 'badge' => 'bg-sky-100 text-sky-700',
                'ring' => 'ring-sky-200', 'link_color' => 'text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100',
                'header_css' => 'background:linear-gradient(135deg,#0ea5e9,#4f46e5)',
                'title' => 'Collecting data',
                'summary' => 'Monitor entries in real time, track completion against your target, and export raw data at any time.',
                'items' => [
                    ['title' => 'Monitor via Analytics', 'body' => 'Go to My Collections → ⋯ → Analytics to see total entries, a 30-day activity chart, and completion rate against your respondent target.', 'route' => 'collections.index', 'target' => 'collections-list', 'link' => ['route' => 'collections.index', 'label' => 'My collections']],
                    ['title' => 'End the survey', 'body' => 'Click End survey in the collection menu when you\'ve collected enough. The unused reward budget is refunded to your wallet immediately.', 'route' => 'collections.index', 'target' => 'collections-list'],
                    ['title' => 'Export raw data', 'body' => 'Download a CSV of all entries at any time from the collection menu, even before cleaning.', 'route' => 'collections.index', 'target' => 'collections-list'],
                ],
            ],
            [
                'icon' => 'sparkles',
                'color' => 'text-fuchsia-600', 'bg' => 'bg-fuchsia-100', 'accent' => 'bg-fuchsia-500',
                'gradient' => 'from-fuchsia-50 to-white', 'badge' => 'bg-fuchsia-100 text-fuchsia-700',
                'ring' => 'ring-fuchsia-200', 'link_color' => 'text-fuchsia-600 hover:text-fuchsia-700 bg-fuchsia-50 hover:bg-fuchsia-100',
                'header_css' => 'background:linear-gradient(135deg,#d946ef,#a855f7)',
                'title' => 'Refining data',
                'summary' => 'Two-stage pipeline: Half Clean (free) strips PII and scores quality; Full Clean (paid) deduplicates and normalises.',
                'items' => [
                    ['title' => 'Half Clean (free)', 'body' => 'Strips PII, calculates a quality score per entry (0–1), and flags incomplete responses. Required before you can publish to the marketplace. Run it from the Pipeline sidebar when editing a collection.', 'route' => 'collections.index', 'target' => 'collections-list'],
                    ['title' => 'Full Clean (paid)', 'body' => 'Removes near-duplicates, validates and casts data types, and normalises text. Produces a detailed cleaning report. Runs after Half Clean. The fee is debited from your wallet on confirmation.', 'route' => 'collections.index', 'target' => 'collections-list'],
                    ['title' => 'Tip', 'body' => 'You can publish after Half Clean alone. Full Clean is optional but datasets with it command higher prices because buyers receive fully structured, deduplicated data.'],
                ],
            ],
            [
                'icon' => 'compass',
                'color' => 'text-violet-600', 'bg' => 'bg-violet-100', 'accent' => 'bg-violet-500',
                'gradient' => 'from-violet-50 to-white', 'badge' => 'bg-violet-100 text-violet-700',
                'ring' => 'ring-violet-200', 'link_color' => 'text-violet-600 hover:text-violet-700 bg-violet-50 hover:bg-violet-100',
                'header_css' => 'background:linear-gradient(135deg,#4f46e5,#7c3aed)',
                'title' => 'Publishing & selling',
                'summary' => 'Publish a cleaned dataset to the marketplace, set a price, and earn on every purchase.',
                'items' => [
                    ['title' => 'Run Half Clean first', 'body' => 'Publishing is locked until at least Half Clean has been run. This ensures every dataset in the marketplace meets a minimum quality bar.', 'route' => 'collections.index', 'target' => 'collections-list'],
                    ['title' => 'Set a price and publish', 'body' => 'Edit your collection, enter a price in IDR (or 0 for free), and change the status to For sale. Your dataset appears in the marketplace immediately with a preview of the first few rows.', 'route' => 'collections.index', 'target' => 'collections-list'],
                    ['title' => 'Earn on every sale', 'body' => 'When a buyer purchases, the amount is credited to your wallet after the platform fee. Withdraw to your bank account at any time.', 'route' => 'marketplace.index', 'target' => 'marketplace-header', 'link' => ['route' => 'marketplace.index', 'label' => 'Browse marketplace']],
                ],
            ],
            [
                'icon' => 'gift',
                'color' => 'text-amber-600', 'bg' => 'bg-amber-100', 'accent' => 'bg-amber-500',
                'gradient' => 'from-amber-50 to-white', 'badge' => 'bg-amber-100 text-amber-700',
                'ring' => 'ring-amber-200', 'link_color' => 'text-amber-600 hover:text-amber-700 bg-amber-50 hover:bg-amber-100',
                'header_css' => 'background:linear-gradient(135deg,#f59e0b,#f97316)',
                'title' => 'Earning rewards',
                'summary' => 'Fill surveys from other creators and get paid per entry, no setup required.',
                'items' => [
                    ['title' => 'Browse open surveys', 'body' => 'Go to the Surveys page and filter by category or reward amount. Surveys still collecting responses with available spots are open to fill.', 'route' => 'surveys.index', 'target' => 'surveys-header', 'link' => ['route' => 'surveys.index', 'label' => 'View surveys']],
                    ['title' => 'Fill & submit', 'body' => 'Complete every required question and submit. Your entry is recorded immediately.', 'route' => 'surveys.index', 'target' => 'surveys-header'],
                    ['title' => 'Reward credited automatically', 'body' => 'The per-entry reward is added to your wallet as soon as the entry is accepted. Check your balance and activity feed for confirmation.', 'route' => 'dashboard', 'target' => 'dashboard-activity'],
                ],
            ],
            [
                'icon' => 'wallet',
                'color' => 'text-indigo-600', 'bg' => 'bg-indigo-100', 'accent' => 'bg-indigo-500',
                'gradient' => 'from-indigo-50 to-white', 'badge' => 'bg-indigo-100 text-indigo-700',
                'ring' => 'ring-indigo-200', 'link_color' => 'text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100',
                'header_css' => 'background:linear-gradient(135deg,#3b82f6,#4f46e5)',
                'title' => 'Wallet & payments',
                'summary' => 'Top up via Virtual Account, QRIS, or E-wallet. Withdraw to a bank account in 1–3 business days.',
                'items' => [
                    ['title' => 'Top up', 'body' => 'Choose an amount and a channel: Virtual Account (BCA, BNI, Mandiri), QRIS, or E-wallet (GoPay, OVO, Dana). After initiating, follow the payment instructions and click "I\'ve paid" to confirm.', 'route' => 'wallet.index', 'target' => 'wallet-topup', 'link' => ['route' => 'wallet.index', 'label' => 'Go to wallet']],
                    ['title' => 'Withdraw', 'body' => 'Enter the amount (min Rp ' . number_format(config('datacore.min_withdrawal'), 0, ',', '.') . '), your bank name, account number, and account holder name. Funds leave your wallet immediately and arrive in 1–3 business days.', 'route' => 'wallet.index', 'target' => 'wallet-withdraw'],
                    ['title' => 'Transaction types', 'body' => 'Top-up, Reward (earned from filling surveys), Escrow (reward pool held for your survey), Escrow refund (unused pool returned), Payment (dataset sale), and Withdrawal.', 'route' => 'transactions.index', 'target' => 'transactions-header'],
                ],
            ],
            [
                'icon' => 'shield-check',
                'color' => 'text-indigo-600', 'bg' => 'bg-indigo-100', 'accent' => 'bg-indigo-500',
                'gradient' => 'from-indigo-50 to-white', 'badge' => 'bg-indigo-100 text-indigo-700',
                'ring' => 'ring-indigo-200', 'link_color' => 'text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100',
                'header_css' => 'background:linear-gradient(135deg,#6366f1,#0d9488)',
                'title' => 'Verification',
                'summary' => 'Submit a government-issued ID and selfie to earn the Verified Contributor badge.',
                'items' => [
                    ['title' => 'Prepare your ID', 'body' => 'Use a passport, national identity card, or driving licence. The ID number and full name must be clearly readable.', 'route' => 'verification.index', 'target' => 'verification-form'],
                    ['title' => 'Take a selfie with your ID', 'body' => 'Hold the ID flat beside your face (not obscuring it) in good light. Both your face and the ID text must be in focus.', 'route' => 'verification.index', 'target' => 'verification-form'],
                    ['title' => 'Submit & get the badge', 'body' => 'Upload both images on the Verification page. Our team reviews within 1 business day. Once approved, the Verified Contributor badge appears on your profile and datasets.', 'route' => 'verification.index', 'target' => 'verification-submit', 'link' => ['route' => 'verification.index', 'label' => 'Start verification']],
                ],
            ],
        ];
    }

    /**
     * A slimmed-down, JSON-friendly copy of the guide content for the
     * cross-page tour engine: resolved URLs instead of route names, and
     * only the fields the tour popup actually renders.
     */
    public static function forTour(): array
    {
        return array_map(function (array $section) {
            return [
                'icon' => $section['icon'],
                'title' => __($section['title']),
                'headerCss' => $section['header_css'],
                'items' => array_map(function (array $item) {
                    return [
                        'title' => __($item['title']),
                        'body' => __($item['body']),
                        'target' => $item['target'] ?? null,
                        'url' => isset($item['route']) ? route($item['route']) : null,
                        'link' => isset($item['link'])
                            ? ['url' => route($item['link']['route']), 'label' => __($item['link']['label'])]
                            : null,
                    ];
                }, $section['items']),
            ];
        }, self::sections());
    }
}
