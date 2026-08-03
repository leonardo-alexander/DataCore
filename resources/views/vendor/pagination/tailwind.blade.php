{{--
    App-styled paginator. Published deliberately: Tailwind v4 does not scan the
    gitignored vendor/ tree, so the stock view's responsive utilities are never
    generated and its mobile and desktop blocks both render at once.
--}}
@if ($paginator->hasPages())
    @php
        $arrow = 'inline-flex h-9 w-9 items-center justify-center rounded-lg border text-sm transition';
        $page = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2 text-sm font-medium transition';
    @endphp

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
        class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">

        <p class="text-sm text-slate-500">
            @if ($paginator->firstItem())
                {!! __('Showing :first to :last of :total results', [
                    'first' => '<span class="font-medium text-slate-700">' . $paginator->firstItem() . '</span>',
                    'last' => '<span class="font-medium text-slate-700">' . $paginator->lastItem() . '</span>',
                    'total' => '<span class="font-medium text-slate-700">' . $paginator->total() . '</span>',
                ]) !!}
            @else
                {{ trans_choice('{0}No results|{1}:count result|[2,*]:count results', $paginator->count(), ['count' => $paginator->count()]) }}
            @endif
        </p>

        <div class="flex flex-wrap items-center justify-center gap-1">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}"
                    class="{{ $arrow }} cursor-not-allowed border-slate-200 bg-white text-slate-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}"
                    class="{{ $arrow }} border-slate-200 bg-white text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="{{ $page }} cursor-default text-slate-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $pageNumber => $url)
                        @if ($pageNumber == $paginator->currentPage())
                            <span aria-current="page"
                                class="{{ $page }} dc-spectrum text-white shadow-glow">{{ $pageNumber }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $pageNumber]) }}"
                                class="{{ $page }} border border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600">{{ $pageNumber }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}"
                    class="{{ $arrow }} border-slate-200 bg-white text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}"
                    class="{{ $arrow }} cursor-not-allowed border-slate-200 bg-white text-slate-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
