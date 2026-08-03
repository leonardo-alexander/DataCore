{{-- Optional third action on an error page. Expects $url, $label and $icon. --}}
<a href="{{ $url }}"
    class="inline-flex items-center gap-2 rounded-xl border border-violet-400/25 bg-violet-500/10 px-5 py-2.5 text-sm font-medium text-violet-200 transition hover:border-violet-400/40 hover:bg-violet-500/20 hover:text-white focus:outline-none focus:ring-4 focus:ring-violet-500/20">
    <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
    {{ $label }}
</a>
