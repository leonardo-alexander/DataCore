@extends('layouts.app')

@section('title', 'Admin Console')

@section('content')
    @php
        $statusBadge = [
            'verified' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'rejected' => 'bg-red-50 text-red-600 ring-red-200',
            'unverified' => 'bg-slate-100 text-slate-500 ring-slate-200',
        ];
        $statusLabel = [
            'verified' => __('Verified'),
            'pending' => __('Pending'),
            'rejected' => __('Rejected'),
            'unverified' => __('Unverified'),
        ];
        $statusHint = [
            'verified' => __('This account is verified. No further action needed.'),
            'rejected' => __('This verification was rejected. The user can submit again.'),
            'unverified' => __('This user has not submitted any verification documents yet.'),
        ];
    @endphp

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">{{ __('Admin Console') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Manage users and verification requests.') }}</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mt-7 grid grid-cols-2 gap-5 sm:grid-cols-4">
        @php
            $cards = [
                [
                    'label' => __('Total users'),
                    'value' => $stats['users'],
                    'icon' => 'users',
                    'tile' => 'from-indigo-500 to-violet-500',
                ],
                [
                    'label' => __('Pending review'),
                    'value' => $stats['pending'],
                    'icon' => 'clock',
                    'tile' => 'from-amber-400 to-orange-500',
                ],
                [
                    'label' => __('Verified'),
                    'value' => $stats['verified'],
                    'icon' => 'shield-check',
                    'tile' => 'from-emerald-400 to-teal-500',
                ],
                [
                    'label' => __('Collections'),
                    'value' => $stats['collections'],
                    'icon' => 'layers',
                    'tile' => 'from-sky-500 to-indigo-500',
                ],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $card['tile'] }} text-white shadow-glow">
                    <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i>
                </span>
                <p class="mt-4 font-display text-2xl font-semibold tracking-tight text-slate-900">{{ $card['value'] }}</p>
                <p class="mt-0.5 text-sm text-slate-500">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Monthly Signups Chart --}}
    <div class="mt-6 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Monthly signups') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Last 12 months') }}</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 font-mono text-xs text-slate-500">
                {{ array_sum($chart['values']) }} total
            </span>
        </div>
        <div class="mt-6 flex h-40 items-end gap-1.5">
            @foreach ($chart['values'] as $i => $value)
                <div class="group flex flex-1 flex-col items-center gap-2">
                    <div class="dc-spectrum w-full rounded-t-sm opacity-80 transition group-hover:opacity-100"
                        style="height: {{ max(3, round(($value / $chart['max']) * 112)) }}px"
                        title="{{ $value }} signups"></div>
                    <span class="font-mono text-[10px] text-slate-400">{{ $chart['labels'][$i] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- User Table --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
            <div>
                <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Users') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ __(':count accounts', ['count' => $users->total()]) }}</p>
            </div>

            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <i data-lucide="search"
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Search name or email…') }}"
                        class="rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-sm focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <div class="w-44">
                    <x-select
                        name="status"
                        :selected="$filter"
                        :autosubmit="true"
                        :options="[
                            ['value' => 'all',        'label' => __('All statuses')],
                            ['value' => 'pending',    'label' => __('Pending')],
                            ['value' => 'verified',   'label' => __('Verified')],
                            ['value' => 'rejected',   'label' => __('Rejected')],
                            ['value' => 'unverified', 'label' => __('Unverified')],
                        ]"
                    />
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-2xl table-fixed text-sm">
                <thead>
                    <tr class="border-y border-slate-100 bg-slate-50/60 text-[11px] uppercase tracking-wide text-slate-400">
                        <th class="px-6 py-3 text-left font-semibold">{{ __('User') }}</th>
                        <th class="w-40 px-6 py-3 text-left font-semibold">{{ __('Joined') }}</th>
                        <th class="w-36 px-6 py-3 text-left font-semibold">{{ __('Status') }}</th>
                        <th class="w-36 px-6 py-3 text-right font-semibold">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 align-middle">
                    @forelse ($users as $user)
                        @php
                            $verification = $user->verification;
                            $profile = $user->profile;
                            $vs = $verification?->status ?? 'unverified';

                            $fields = [
                                ['label' => __('National ID'), 'value' => $verification?->id_number, 'mono' => true, 'required' => true],
                                [
                                    'label' => __('Date of birth'),
                                    'value' => $profile?->dob
                                        ? $profile->dob->format('d M Y') . ($profile->age() !== null ? ' · ' . __(':count yrs', ['count' => $profile->age()]) : '')
                                        : null,
                                    'required' => true,
                                ],
                                ['label' => __('Gender'), 'value' => $profile?->gender, 'required' => true],
                                ['label' => __('Phone number'), 'value' => $profile?->phone_number],
                                ['label' => __('City / domicile'), 'value' => $profile?->city, 'required' => true],
                                ['label' => __('Profession'), 'value' => $profile?->profession],
                                ['label' => __('Marital status'), 'value' => $profile?->marital_status],
                                ['label' => __('Address'), 'value' => $profile?->address, 'required' => true, 'wide' => true],
                            ];

                            $documents = [
                                ['type' => 'id_card', 'label' => __('ID Card'), 'icon' => 'id-card', 'url' => $verification?->id_card_url],
                                ['type' => 'selfie', 'label' => __('Selfie'), 'icon' => 'camera', 'url' => $verification?->selfie_url],
                            ];
                        @endphp
                        <tr x-data="{ open: false, mode: 'view' }" class="transition hover:bg-slate-50/60">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="dc-spectrum flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[11px] font-semibold text-white">
                                        {{ strtoupper(mb_substr($user->name, 0, 2)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="truncate font-medium text-slate-900">{{ $user->name }}</span>
                                            @if ($user->is_admin)
                                                <span
                                                    class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-indigo-600 ring-1 ring-indigo-200">{{ __('Admin') }}</span>
                                            @endif
                                        </div>
                                        <div class="truncate text-xs text-slate-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-slate-600">{{ $user->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-slate-400">{{ $user->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusBadge[$vs] ?? $statusBadge['unverified'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                    {{ $statusLabel[$vs] ?? $statusLabel['unverified'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button type="button"
                                    @click="open = true; mode = 'view'; $nextTick(() => window.renderIcons?.())"
                                    @class([
                                        'inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                                        'dc-spectrum text-white shadow-glow hover:opacity-90' => $vs === 'pending',
                                        'border border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600' => $vs !== 'pending',
                                    ])>
                                    <i data-lucide="{{ $vs === 'pending' ? 'clipboard-check' : 'eye' }}" class="h-3.5 w-3.5"></i>
                                    {{ $vs === 'pending' ? __('Review') : __('Details') }}
                                </button>

                                {{-- Review modal: profile, documents, and verification actions --}}
                                <template x-if="open">
                                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
                                        role="dialog" aria-modal="true" @click.self="open = false"
                                        @keydown.escape.window="open = false">
                                        <div
                                            class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white text-left shadow-2xl">
                                            {{-- Header --}}
                                            <div class="flex items-start gap-4 border-b border-slate-100 p-5 sm:p-6">
                                                <span
                                                    class="dc-spectrum flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white">
                                                    {{ strtoupper(mb_substr($user->name, 0, 2)) }}
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <h3 class="font-display text-lg font-semibold text-slate-900">
                                                            {{ $user->name }}</h3>
                                                        <span
                                                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusBadge[$vs] ?? $statusBadge['unverified'] }}">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                                            {{ $statusLabel[$vs] ?? $statusLabel['unverified'] }}
                                                        </span>
                                                    </div>
                                                    <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
                                                    <p class="mt-0.5 text-xs text-slate-400">
                                                        {{ __('Joined') }} {{ $user->created_at->format('d M Y') }}</p>
                                                </div>
                                                <button type="button" @click="open = false"
                                                    class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                                                    <i data-lucide="x" class="h-4 w-4"></i>
                                                </button>
                                            </div>

                                            {{-- Body --}}
                                            <div class="flex-1 space-y-6 overflow-y-auto p-5 sm:p-6">
                                                <div>
                                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                                        {{ __('Profile') }}</p>
                                                    <dl class="mt-3 grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                                                        @foreach ($fields as $field)
                                                            <div @class(['sm:col-span-2' => $field['wide'] ?? false])>
                                                                <dt class="text-xs text-slate-400">{{ $field['label'] }}</dt>
                                                                @if (filled($field['value']))
                                                                    <dd @class([
                                                                        'mt-0.5 break-words text-sm text-slate-900',
                                                                        'font-mono' => $field['mono'] ?? false,
                                                                    ])>{{ $field['value'] }}</dd>
                                                                @else
                                                                    <dd @class([
                                                                        'mt-0.5 text-sm',
                                                                        'text-amber-600' => $field['required'] ?? false,
                                                                        'text-slate-300' => !($field['required'] ?? false),
                                                                    ])>{{ ($field['required'] ?? false) ? __('Not provided') : '—' }}</dd>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </dl>
                                                </div>

                                                <div>
                                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                                        {{ __('Documents') }}</p>
                                                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                        @foreach ($documents as $document)
                                                            @if (filled($document['url']))
                                                                <a href="{{ route('admin.verifications.document', [$user, $document['type']]) }}"
                                                                    target="_blank"
                                                                    class="group overflow-hidden rounded-xl border border-slate-200 transition hover:border-indigo-300">
                                                                    <img src="{{ route('admin.verifications.document', [$user, $document['type']]) }}"
                                                                        alt="{{ $document['label'] }}" loading="lazy"
                                                                        class="h-40 w-full bg-slate-50 object-contain">
                                                                    <div
                                                                        class="flex items-center justify-between gap-2 border-t border-slate-200 bg-white px-3 py-2">
                                                                        <span
                                                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600">
                                                                            <i data-lucide="{{ $document['icon'] }}" class="h-3.5 w-3.5"></i>
                                                                            {{ $document['label'] }}
                                                                        </span>
                                                                        <i data-lucide="external-link"
                                                                            class="h-3.5 w-3.5 text-slate-400 transition group-hover:text-indigo-600"></i>
                                                                    </div>
                                                                </a>
                                                            @else
                                                                <div
                                                                    class="flex flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-3 py-10 text-center">
                                                                    <i data-lucide="image-off" class="h-4 w-4 text-slate-300"></i>
                                                                    <span class="text-xs text-slate-400">
                                                                        {{ __(':document not submitted', ['document' => $document['label']]) }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>

                                                @if ($verification?->note)
                                                    <div class="rounded-xl bg-slate-50 p-4">
                                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                                            {{ __('Verification note') }}</p>
                                                        <p class="mt-1 text-sm text-slate-600">{{ $verification->note }}</p>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Footer --}}
                                            <div class="border-t border-slate-100 bg-slate-50/60 p-4 sm:px-6">
                                                @if ($vs === 'pending')
                                                    <div x-show="mode === 'view'" class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                                        <button type="button" @click="mode = 'reject'"
                                                            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 ring-1 ring-red-200 transition hover:bg-red-100">
                                                            <i data-lucide="x" class="h-4 w-4"></i> {{ __('Reject') }}
                                                        </button>
                                                        <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                                            @csrf
                                                            <button type="submit"
                                                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                                                <i data-lucide="check" class="h-4 w-4"></i> {{ __('Approve') }}
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <form x-show="mode === 'reject'" method="POST"
                                                        action="{{ route('admin.users.reject', $user) }}">
                                                        @csrf
                                                        <p class="text-sm font-semibold text-slate-900">{{ __('Reject verification') }}</p>
                                                        <p class="mt-0.5 text-xs text-slate-500">
                                                            {{ __('Optionally leave a note explaining why.') }}</p>
                                                        <textarea name="note" rows="2" placeholder="{{ __('Reason (optional)') }}"
                                                            class="mt-3 w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100"></textarea>
                                                        <div class="mt-3 flex justify-end gap-2">
                                                            <button type="button" @click="mode = 'view'"
                                                                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">{{ __('Back') }}</button>
                                                            <button type="submit"
                                                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">{{ __('Confirm reject') }}</button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                                        <p class="text-xs text-slate-500">{{ $statusHint[$vs] ?? '' }}</p>
                                                        <button type="button" @click="open = false"
                                                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">{{ __('Close') }}</button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <i data-lucide="users" class="mx-auto h-8 w-8 text-slate-300"></i>
                                <p class="mt-3 text-sm text-slate-400">{{ __('No users found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

@endsection
