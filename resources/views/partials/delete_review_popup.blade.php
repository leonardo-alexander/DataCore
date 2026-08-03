<div
    x-show="openDeleteConfirm"
    x-cloak
    x-transition
    x-scroll-lock="openDeleteConfirm"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

    <div
        @click.outside="openDeleteConfirm = false"
        class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-xl text-center">

        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>

        <h2 class="mt-4 text-xl font-semibold text-slate-900">
            Delete Review?
        </h2>

        <p class="mt-2 text-sm text-slate-500">
            Are you sure you want to delete this review?
        </p>

        <form :action="deleteUrl" method="POST" class="mt-6 flex justify-end gap-3">
            @csrf
            @method('DELETE')

            <button
                type="button"
                @click="openDeleteConfirm = false"
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                Cancel
            </button>

            <button
                type="submit"
                class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700 shadow-sm transition">
                Delete
            </button>
        </form>
    </div>
</div>