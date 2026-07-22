<div x-show="openEdit" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
    @click.self="openEdit = false">

    <div class="mx-4 w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
        <h3 class="font-display text-base font-semibold text-slate-900">Edit Review</h3>
        <p class="mt-1 text-sm text-slate-500">Update your thoughts on this dataset.</p>

        <form method="POST" action="{{ route('marketplace.review', $collection) }}" class="mt-5">
            @csrf

            <input type="hidden" name="rating" :value="editRating">

            <div class="flex justify-center gap-1.5">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" @click="editRating = {{ $i }}" class="transition hover:scale-125">
                        <svg class="h-9 w-9"
                            :class="editRating >= {{ $i }} ? 'fill-amber-400 text-amber-400' : 'fill-slate-200 text-slate-200'"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </button>
                @endfor
            </div>

            <textarea name="comment" x-model="editComment" rows="4" placeholder="Write a review…"
                class="mt-4 w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100"></textarea>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" @click="openEdit = false"
                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit"
                    class="dc-spectrum rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">Update Review</button>
            </div>
        </form>
    </div>
</div>
