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
    <div class="mt-6 rounded-2xl border border-slate-200/70 bg-white shadow-card">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
            <h2 class="font-display text-lg font-semibold text-slate-900">{{ __('Users') }}</h2>

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
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-t border-slate-100 bg-slate-50/60">
                        <th class="px-6 py-3 text-left font-medium text-slate-500">{{ __('Users') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-slate-500">{{ __('Joined') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-slate-500">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-slate-500">{{ __('Documents') }}</th>
                        <th class="px-6 py-3 text-right font-medium text-slate-500">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        @php
                            $vs = $user->verification?->status ?? 'unverified';
                        @endphp
                        <tr class="group transition hover:bg-slate-50/60">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $user->name }}</div>
                                <div class="text-xs text-slate-400">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $statusBadge[$vs] ?? $statusBadge['unverified'] }}">
                                    {{ ucfirst($vs) }}
                                </span>
                                @if ($user->verification?->note)
                                    <p class="mt-1 max-w-xs truncate text-xs text-slate-400">
                                        {{ $user->verification->note }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($user->verification)
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.verifications.document', [$user, 'id_card']) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 transition hover:border-indigo-200 hover:text-indigo-600">
                                            <i data-lucide="id-card" class="h-3.5 w-3.5"></i> {{ __('ID Card') }}
                                        </a>
                                        <a href="{{ route('admin.verifications.document', [$user, 'selfie']) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 transition hover:border-indigo-200 hover:text-indigo-600">
                                            <i data-lucide="camera" class="h-3.5 w-3.5"></i> {{ __('Selfie') }}
                                        </a>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if ($vs === 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 transition hover:bg-emerald-100">
                                                <i data-lucide="check" class="h-3.5 w-3.5"></i> {{ __('Approve') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.reject', $user) }}"
                                            x-data="{ open: false }" @submit.prevent="open = true">
                                            <button type="button" @click="open = true"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 ring-1 ring-red-200 transition hover:bg-red-100">
                                                <i data-lucide="x" class="h-3.5 w-3.5"></i> {{ __('Reject') }}
                                            </button>
                                            {{-- Reject note modal --}}
                                            <div x-show="open" x-cloak
                                                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
                                                @click.self="open = false">
                                                <div class="mx-4 w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                                                    <h3 class="font-display text-base font-semibold text-slate-900">{{ __('Reject verification') }}</h3>
                                                    <p class="mt-1 text-xs text-slate-500">{{ __('Optionally leave a note explaining why.') }}</p>
                                                    <form method="POST" action="{{ route('admin.users.reject', $user) }}"
                                                        class="mt-4">
                                                        @csrf
                                                        <textarea name="note" rows="3" placeholder="{{ __('Reason (optional)') }}"
                                                            class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100"></textarea>
                                                        <div class="mt-4 flex justify-end gap-2">
                                                            <button type="button" @click="open = false"
                                                                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">{{ __('Cancel') }}</button>
                                                            <button type="submit"
                                                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">{{ __('Reject') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-400">{{ __('No users found.') }}</td>
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
