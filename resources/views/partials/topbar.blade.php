@php
    $activityIcon = [
        'system' => 'bell',
        'cleaning' => 'sparkles',
        'entry' => 'file-plus-2',
        'collection' => 'layers',
        'review' => 'star',
        'payment' => 'credit-card',
        'topup' => 'wallet',
        'reward' => 'gift',
        'purchase' => 'shopping-bag',
    ];
    $user = auth()->user();
@endphp

<header class="sticky top-0 z-20 border-b border-slate-200/70 bg-white/80 backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-5 sm:px-8">
        <button @click="sidebar = true"
            class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 lg:hidden">
            <i data-lucide="menu" class="h-5 w-5"></i>
        </button>

        <form method="GET" action="{{ route('marketplace.index') }}" class="relative hidden flex-1 sm:block">
            <i data-lucide="search"
                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search datasets...') }}"
                class="w-full max-w-md rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
        </form>

        <div class="ml-auto flex items-center gap-2 sm:gap-3">
            <a href="{{ route('wallet.index') }}"
                class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 sm:inline-flex">
                <i data-lucide="wallet" class="h-4 w-4 text-indigo-500"></i>
                @rupiah($navBalance ?? 0)
            </a>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-slate-900">
                    <i data-lucide="bell" class="h-4.5 w-4.5"></i>
                    @if (($navUnread ?? 0) > 0)
                        <span
                            class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-fuchsia-500 px-1 text-[10px] font-semibold text-white">{{ $navUnread > 9 ? '9+' : $navUnread }}</span>
                    @endif
                </button>

                <div x-show="open" x-cloak @click.outside="open = false" x-transition
                    class="absolute right-0 mt-2 w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Notifications') }}</p>
                        @if (($navUnread ?? 0) > 0)
                            <form method="POST" action="{{ route('activities.read') }}">
                                @csrf
                                <button class="text-xs font-medium text-indigo-600 hover:text-indigo-700">{{ __('Mark all read') }}</button>
                            </form>
                        @endif
                    </div>
                    <div class="max-h-80 divide-y divide-slate-50 overflow-y-auto">
                        @forelse ($navActivities ?? [] as $activity)
                            <div class="flex gap-3 px-4 py-3 {{ $activity->is_read ? '' : 'bg-indigo-50/40' }}">
                                <span
                                    class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                    <i data-lucide="{{ $activityIcon[$activity->type] ?? 'circle-dot' }}"
                                        class="h-4 w-4"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-900">{{ $activity->title }}</p>
                                    @if ($activity->description)
                                        <p class="truncate text-xs text-slate-500">{{ $activity->description }}</p>
                                    @endif
                                    <p class="mt-0.5 text-[11px] text-slate-400">
                                        {{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-10 text-center">
                                <i data-lucide="inbox" class="mx-auto h-6 w-6 text-slate-300"></i>
                                <p class="mt-2 text-sm text-slate-400">{{ __('No notifications yet') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-2.5 shadow-sm transition hover:border-slate-300">
                    <span
                        class="dc-spectrum flex h-7 w-7 items-center justify-center rounded-lg text-xs font-semibold text-white">{{ strtoupper(mb_substr($user->name ?? 'U', 0, 2)) }}</span>
                    <span
                        class="hidden text-sm font-medium text-slate-700 sm:block">{{ \Illuminate\Support\Str::of($user->name ?? 'User')->explode(' ')->first() }}</span>
                    <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400"></i>
                </button>

                <div x-show="open" x-cloak @click.outside="open = false" x-transition
                    class="absolute right-0 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white py-1.5 shadow-xl">
                    <div class="border-b border-slate-100 px-4 py-2.5">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                    </div>
                    @php
                        $menu = [
                            ['route' => 'profile.edit', 'icon' => 'user',     'label' => __('Profile')],
                            ['route' => 'wallet.index', 'icon' => 'wallet',   'label' => __('Wallet')],
                            ['route' => 'settings',     'icon' => 'settings', 'label' => __('Settings')],
                        ];
                    @endphp
                    @foreach ($menu as $m)
                        <a href="{{ route($m['route']) }}"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                            <i data-lucide="{{ $m['icon'] }}" class="h-4 w-4 text-slate-400"></i>
                            {{ $m['label'] }}
                        </a>
                    @endforeach
                    <div class="mt-1 border-t border-slate-100 pt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-rose-600 transition hover:bg-rose-50">
                                <i data-lucide="log-out" class="h-4 w-4"></i> {{ __('Log out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
