<div x-show="deleteOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
    @click.self="deleteOpen = false">
    <div class="mx-4 w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50">
            <i data-lucide="trash-2" class="h-5 w-5 text-rose-600"></i>
        </div>
        <h3 class="mt-4 font-display text-base font-semibold text-slate-900">Delete <span x-text="deleteLabel"></span>?</h3>
        <p class="mt-1 text-sm text-slate-500">This action cannot be undone.</p>
        <form :action="deleteUrl" method="POST" class="mt-5 flex justify-end gap-2">
            @csrf
            @method('DELETE')
            <button type="button" @click="deleteOpen = false"
                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
            <button type="submit"
                class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">Delete</button>
        </form>
    </div>
</div>
