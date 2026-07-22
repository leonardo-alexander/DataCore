@php
    $val               = fn ($field, $default = '') => old($field, $currentValues[$field] ?? $default);
    $statusVal         = old('status',          $currentValues['status']          ?? 'draft');
    $categoryVal       = old('category_id',     $currentValues['category_id']     ?? '');
    $metadataFieldsVal = old('metadata_fields', $currentValues['metadata_fields'] ?? []);
@endphp

<div x-data="{ mode: 'build', clean2Open: false }" @open-clean2.window="clean2Open = true">
    @if ($showImport)
        <div class="mt-6 inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
            <button type="button" @click="mode = 'build'"
                :class="mode === 'build' ? 'dc-spectrum text-white shadow-glow' : 'text-slate-500 hover:text-slate-900'"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition">
                <i data-lucide="list-plus" class="h-4 w-4"></i> Build a survey
            </button>
            <button type="button" @click="mode = 'import'"
                :class="mode === 'import' ? 'dc-spectrum text-white shadow-glow' : 'text-slate-500 hover:text-slate-900'"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition">
                <i data-lucide="upload" class="h-4 w-4"></i> Import CSV
            </button>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" x-show="mode === 'build'"
        class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3" x-data="{
            status: @js($statusVal),
            reward: {{ (int) $val('reward', 0) }},
            target: {{ (int) $val('respondent_target', 0) }},
            feeRate: {{ $feeRate }},
            get base()   { return this.reward > 0 && this.target > 0 ? this.reward * this.target : 0; },
            get fee()    { return this.base > 0 ? Math.round(this.base * this.feeRate) : 0; },
            get budget() { return this.base + this.fee; },
            questions: @js($initialQuestions),
            dragIndex: null,
            dragOverIndex: null,
            addQuestion() {
                this.questions.push({ text: '', type: 'text', description: '', required: false, options: ['', ''], hasOther: false, maxStars: 5, dateMode: 'date' });
                this.refresh();
            },
            removeQuestion(i) {
                if (this.questions.length === 1) return;
                this.questions.splice(i, 1);
                this.refresh();
            },
            duplicateQuestion(i) {
                this.questions.splice(i + 1, 0, JSON.parse(JSON.stringify(this.questions[i])));
                this.refresh();
            },
            moveQuestion(i, dir) {
                const j = i + dir;
                if (j < 0 || j >= this.questions.length) return;
                const [moved] = this.questions.splice(i, 1);
                this.questions.splice(j, 0, moved);
                this.refresh();
            },
            dropQuestion(i) {
                const from = this.dragIndex;
                this.dragIndex = null;
                this.dragOverIndex = null;
                if (from === null || from === i) return;
                const [moved] = this.questions.splice(from, 1);
                this.questions.splice(from < i ? i - 1 : i, 0, moved);
                this.refresh();
            },
            setType(i, t) {
                this.questions[i].type = t;
                if ((t === 'choice' || t === 'checkbox') && this.questions[i].options.length === 0)
                    this.questions[i].options.push('', '');
                this.refresh();
            },
            addOption(i) {
                this.questions[i].options.push('');
                this.refresh();
            },
            removeOption(i, oi) {
                this.questions[i].options.splice(oi, 1);
                this.refresh();
            },
            refresh() { this.$nextTick(() => window.renderIcons && window.renderIcons()); }
        }">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="space-y-6 lg:col-span-2">
            {{-- Details --}}
            <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                <h2 class="font-display text-lg font-semibold text-slate-900">Details</h2>
                <div class="mt-5 space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ $val('title') }}" required
                            placeholder="e.g. Coffee Habits 2026"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Description</label>
                        <textarea name="description" rows="3" placeholder="What is this dataset about?"
                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">{{ $val('description') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Category</label>
                            <x-select
                                name="category_id"
                                :selected="(string) $categoryVal"
                                placeholder="Uncategorized"
                                :options="collect($categories)->map(fn($c) => ['value' => (string) $c->id, 'label' => $c->name])->prepend(['value' => '', 'label' => 'Uncategorized'])->all()"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Respondent metadata: only meaningful for live surveys; CSV imports have no respondent to snapshot a profile from --}}
            @if (!isset($collection) || $collection->type !== 'upload')
                <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Respondent metadata</h2>
                    <p class="mt-1 text-sm text-slate-500">Auto-attached from each respondent's verified profile. Tick only what you need; it appears as extra columns when you export the CSV.</p>
                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach (\App\Models\Collection::METADATA as $metaKey => $metaLabel)
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3.5 py-2.5 transition has-checked:border-indigo-300 has-checked:bg-indigo-50/50">
                                <input type="checkbox" name="metadata_fields[]" value="{{ $metaKey }}"
                                    @checked(in_array($metaKey, $metadataFieldsVal))
                                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-200">
                                <span class="text-sm font-medium text-slate-700">{{ $metaLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div data-tour="survey-questions" class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-slate-900">Questions <span class="text-rose-500">*</span></h2>
                        <p class="mt-0.5 flex items-center gap-1 text-xs text-slate-400">
                            <i data-lucide="grip-horizontal" class="h-3.5 w-3.5"></i> Drag the handle to reorder.
                        </p>
                    </div>
                    <button type="button" @click="addQuestion()"
                        class="dc-spectrum inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                        <i data-lucide="plus" class="h-4 w-4"></i> Add question
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <template x-for="(q, i) in questions" :key="i">
                        <div @dragover.prevent="dragIndex !== null ? (dragOverIndex = i) : null"
                            @drop="dropQuestion(i)"
                            :class="[
                                dragIndex === i ? 'opacity-40' : '',
                                dragOverIndex === i && dragIndex !== null && dragIndex !== i ? 'ring-2 ring-indigo-300' : 'ring-0'
                            ]"
                            class="rounded-2xl border border-slate-200 bg-white shadow-sm transition">

                            {{-- Drag handle --}}
                            <div draggable="true"
                                @dragstart="dragIndex = i; $event.dataTransfer.effectAllowed = 'move'"
                                @dragend="dragIndex = null; dragOverIndex = null"
                                title="Drag to reorder"
                                class="flex cursor-grab items-center justify-center rounded-t-2xl border-b border-slate-100 bg-slate-50/70 py-1.5 text-slate-300 transition hover:bg-slate-100 hover:text-slate-500 active:cursor-grabbing">
                                <i data-lucide="grip-horizontal" class="h-4 w-4"></i>
                            </div>

                            <div class="p-5">
                                {{-- Hidden fields --}}
                                <input type="hidden" :name="`questions[${i}][type]`" :value="q.type">
                                <input type="hidden" :name="`questions[${i}][required]`" :value="q.required ? '1' : '0'">
                                <input type="hidden" :name="`questions[${i}][has_other]`" :value="q.hasOther ? '1' : '0'">
                                <input type="hidden" :name="`questions[${i}][max_stars]`" :value="q.maxStars">
                                <input type="hidden" :name="`questions[${i}][date_mode]`" :value="q.dateMode">

                                {{-- Top row: number + question text + type dropdown --}}
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                                    <span class="mt-2 hidden h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-50 font-mono text-xs font-semibold text-indigo-600 sm:flex" x-text="i + 1"></span>

                                    <div class="min-w-0 flex-1">
                                        <input type="text" :name="`questions[${i}][text]`" x-model="q.text"
                                            placeholder="Question"
                                            class="w-full border-0 border-b-2 border-slate-200 bg-transparent px-1 py-2 text-base font-medium text-slate-900 transition placeholder:font-normal placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-0">
                                        <input type="text" :name="`questions[${i}][description]`" x-model="q.description"
                                            placeholder="Description / helper text (optional)"
                                            class="mt-1 w-full border-0 border-b border-transparent bg-transparent px-1 py-1 text-sm text-slate-500 transition placeholder:text-slate-300 hover:border-slate-200 focus:border-indigo-400 focus:outline-none focus:ring-0">
                                    </div>

                                    {{-- Type dropdown (Google Forms style) --}}
                                    <div x-data="{ typeOpen: false }" @click.outside="typeOpen = false" class="relative shrink-0 sm:w-52">
                                        <button type="button" @click="typeOpen = !typeOpen"
                                            class="flex w-full items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-indigo-300">
                                            <span class="flex h-4 w-4 shrink-0 items-center justify-center text-indigo-500">
                                                @foreach ($typeOptions as $key => $opt)
                                                    <i data-lucide="{{ $opt['icon'] }}" class="h-4 w-4" x-show="q.type === '{{ $key }}'" x-cloak></i>
                                                @endforeach
                                            </span>
                                            <span class="min-w-0 flex-1 truncate text-left">
                                                @foreach ($typeOptions as $key => $opt)
                                                    <span x-show="q.type === '{{ $key }}'" x-cloak>{{ $opt['label'] }}</span>
                                                @endforeach
                                            </span>
                                            <i data-lucide="chevron-down" class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="typeOpen && 'rotate-180'"></i>
                                        </button>
                                        <div x-show="typeOpen" x-cloak
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                            class="absolute right-0 z-40 mt-1.5 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white p-1 shadow-xl">
                                            @foreach ($typeOptions as $key => $opt)
                                                <button type="button" @click="setType(i, '{{ $key }}'); typeOpen = false"
                                                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition"
                                                    :class="q.type === '{{ $key }}' ? 'bg-indigo-50 font-medium text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                                                    <i data-lucide="{{ $opt['icon'] }}" class="h-4 w-4"
                                                        :class="q.type === '{{ $key }}' ? 'text-indigo-500' : 'text-slate-400'"></i>
                                                    {{ $opt['label'] }}
                                                    <i data-lucide="check" class="ml-auto h-4 w-4 text-indigo-500" x-show="q.type === '{{ $key }}'" x-cloak></i>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Answer preview / config --}}
                                <div class="mt-4 sm:pl-10">
                                    {{-- Short text --}}
                                    <div x-show="q.type === 'text'" x-cloak
                                        class="w-2/3 border-b border-dashed border-slate-200 pb-1 text-sm text-slate-300">Short answer text</div>
                                    {{-- Paragraph --}}
                                    <div x-show="q.type === 'paragraph'" x-cloak
                                        class="border-b border-dashed border-slate-200 pb-6 text-sm text-slate-300">Long answer text</div>
                                    {{-- Number --}}
                                    <div x-show="q.type === 'number'" x-cloak
                                        class="w-1/3 border-b border-dashed border-slate-200 pb-1 text-sm text-slate-300">0</div>

                                    {{-- Choice / Checkbox options --}}
                                    <div x-show="q.type === 'choice' || q.type === 'checkbox'" x-cloak class="space-y-2.5">
                                        <template x-for="(opt, oi) in q.options" :key="oi">
                                            <div class="flex items-center gap-2.5">
                                                <i x-show="q.type === 'choice'" data-lucide="circle" class="h-4 w-4 shrink-0 text-slate-300"></i>
                                                <i x-show="q.type === 'checkbox'" data-lucide="square" class="h-4 w-4 shrink-0 text-slate-300"></i>
                                                <input type="text" :name="`questions[${i}][options][]`"
                                                    x-model="q.options[oi]" :placeholder="`Option ${oi + 1}`"
                                                    class="flex-1 border-0 border-b border-transparent bg-transparent px-1 py-1 text-sm text-slate-700 transition placeholder:text-slate-400 hover:border-slate-200 focus:border-indigo-400 focus:outline-none focus:ring-0">
                                                <button type="button" @click="removeOption(i, oi)" title="Remove option"
                                                    :class="q.options.length <= 1 && !q.hasOther ? 'invisible' : ''"
                                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-500">
                                                    <i data-lucide="x" class="h-4 w-4"></i>
                                                </button>
                                            </div>
                                        </template>

                                        {{-- "Other" pseudo-option --}}
                                        <div x-show="q.hasOther" x-cloak class="flex items-center gap-2.5">
                                            <i x-show="q.type === 'choice'" data-lucide="circle" class="h-4 w-4 shrink-0 text-slate-300"></i>
                                            <i x-show="q.type === 'checkbox'" data-lucide="square" class="h-4 w-4 shrink-0 text-slate-300"></i>
                                            <span class="flex-1 border-b border-dashed border-slate-200 px-1 py-1 text-sm italic text-slate-400">Other…</span>
                                            <button type="button" @click="q.hasOther = false" title="Remove Other"
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-500">
                                                <i data-lucide="x" class="h-4 w-4"></i>
                                            </button>
                                        </div>

                                        {{-- Add option / Other --}}
                                        <div class="flex items-center gap-2.5 pt-0.5">
                                            <i x-show="q.type === 'choice'" data-lucide="circle" class="h-4 w-4 shrink-0 text-slate-200"></i>
                                            <i x-show="q.type === 'checkbox'" data-lucide="square" class="h-4 w-4 shrink-0 text-slate-200"></i>
                                            <button type="button" @click="addOption(i)" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Add option</button>
                                            <span x-show="!q.hasOther" class="text-sm text-slate-400">or</span>
                                            <button type="button" x-show="!q.hasOther" @click="q.hasOther = true" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">add &ldquo;Other&rdquo;</button>
                                        </div>
                                    </div>

                                    {{-- Rating --}}
                                    <div x-show="q.type === 'rating'" x-cloak class="flex flex-wrap items-center gap-3">
                                        <label class="text-xs font-medium text-slate-500">Max stars</label>
                                        <input type="number" x-model.number="q.maxStars" min="3" max="10"
                                            class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                        <div class="flex items-center gap-0.5">
                                            <template x-for="n in (q.maxStars || 5)" :key="n">
                                                <svg class="h-4 w-4 fill-amber-400 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Datetime --}}
                                    <div x-show="q.type === 'datetime'" x-cloak class="flex items-center gap-3">
                                        <label class="text-xs font-medium text-slate-500">Mode</label>
                                        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                                            <button type="button" @click="open = !open"
                                                class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm shadow-sm transition hover:border-indigo-300 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                                <span x-text="{ date: 'Date only', time: 'Time only', 'datetime-local': 'Date & Time' }[q.dateMode] ?? 'Date only'"></span>
                                                <i data-lucide="chevron-down" class="h-3.5 w-3.5 text-slate-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                                            </button>
                                            <div x-show="open" x-cloak x-transition
                                                class="absolute z-50 mt-1 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                                                <template x-for="opt in [
                                                    { value: 'date', label: 'Date only' },
                                                    { value: 'time', label: 'Time only' },
                                                    { value: 'datetime-local', label: 'Date & Time' }
                                                ]" :key="opt.value">
                                                    <button type="button" @click="q.dateMode = opt.value; open = false"
                                                        class="flex w-full items-center gap-3 px-4 py-2 text-sm transition"
                                                        :class="q.dateMode === opt.value ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-slate-600 hover:bg-slate-50'">
                                                        <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 transition"
                                                            :class="q.dateMode === opt.value ? 'border-indigo-500 bg-indigo-500' : 'border-slate-300'">
                                                            <span x-show="q.dateMode === opt.value" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                                        </span>
                                                        <span x-text="opt.label"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <input :type="q.dateMode || 'date'" disabled
                                            class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-sm text-slate-400">
                                    </div>

                                    {{-- File upload preview --}}
                                    <div x-show="q.type === 'file'" x-cloak
                                        class="flex items-center gap-3 rounded-xl border-2 border-dashed border-slate-200 px-4 py-5 text-slate-400">
                                        <i data-lucide="cloud-upload" class="h-6 w-6 shrink-0"></i>
                                        <div>
                                            <p class="text-sm font-medium text-slate-500">Respondents can upload a file</p>
                                            <p class="text-xs text-slate-400">Images, PDF, Office docs, CSV, up to 10 MB.</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer: actions + required toggle --}}
                                <div class="mt-5 flex items-center justify-end gap-1 border-t border-slate-100 pt-3">
                                    <button type="button" @click="moveQuestion(i, -1)" :disabled="i === 0" title="Move up"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition enabled:hover:bg-slate-100 enabled:hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-30">
                                        <i data-lucide="chevron-up" class="h-4 w-4"></i>
                                    </button>
                                    <button type="button" @click="moveQuestion(i, 1)" :disabled="i === questions.length - 1" title="Move down"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition enabled:hover:bg-slate-100 enabled:hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-30">
                                        <i data-lucide="chevron-down" class="h-4 w-4"></i>
                                    </button>
                                    <button type="button" @click="duplicateQuestion(i)" title="Duplicate question"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-indigo-50 hover:text-indigo-600">
                                        <i data-lucide="copy" class="h-4 w-4"></i>
                                    </button>
                                    <button type="button" @click="removeQuestion(i)" :disabled="questions.length === 1" title="Delete question"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition enabled:hover:bg-rose-50 enabled:hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-30">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                    <span class="mx-1.5 h-6 w-px bg-slate-200"></span>
                                    <span class="text-xs font-medium text-slate-600">Required</span>
                                    <button type="button" @click="q.required = !q.required" role="switch" :aria-checked="q.required" title="Toggle required"
                                        :class="q.required ? 'dc-spectrum' : 'bg-slate-200'"
                                        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition">
                                        <span :class="q.required ? 'translate-x-5' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1">
            <div class="space-y-5 lg:sticky lg:top-24">
                <div data-tour="survey-visibility" class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                    <h3 class="font-display text-base font-semibold text-slate-900">Visibility</h3>
                    <div class="mt-3 space-y-2">
                        @if (!isset($collection))
                            {{-- Create: draft or ongoing --}}
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 transition has-checked:border-slate-400 has-checked:bg-slate-50">
                                <input type="radio" name="status" value="draft" x-model="status" @checked($statusVal === 'draft')
                                    class="mt-0.5 h-4 w-4 border-slate-300 text-slate-600 focus:ring-slate-200">
                                <span>
                                    <span class="block text-sm font-medium text-slate-900">Draft</span>
                                    <span class="block text-xs text-slate-500">Saved privately. Switch to Collecting responses when ready to launch.</span>
                                </span>
                            </label>
                            <div class="rounded-xl border border-slate-200 transition has-checked:border-violet-300 has-checked:bg-violet-50/50">
                                <label class="flex cursor-pointer items-start gap-3 p-3">
                                    <input type="radio" name="status" value="ongoing" x-model="status" @checked($statusVal === 'ongoing')
                                        class="mt-0.5 h-4 w-4 border-slate-300 text-violet-600 focus:ring-violet-200">
                                    <span>
                                        <span class="block text-sm font-medium text-slate-900">Collecting responses</span>
                                        <span class="block text-xs text-slate-500">Live immediately, respondents can find and fill it.</span>
                                    </span>
                                </label>
                                <div x-show="status === 'ongoing'" x-collapse x-cloak>
                                    @include('collections._reward_fields')
                                </div>
                            </div>
                        @else
                            {{-- Draft --}}
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 transition has-checked:border-slate-400 has-checked:bg-slate-50">
                                <input type="radio" name="status" value="draft" x-model="status" @checked($statusVal === 'draft')
                                    class="mt-0.5 h-4 w-4 border-slate-300 text-slate-600 focus:ring-slate-200">
                                <span>
                                    <span class="block text-sm font-medium text-slate-900">Draft</span>
                                    <span class="block text-xs text-slate-500">Private, save progress without going live.</span>
                                </span>
                            </label>
                            {{-- Collecting responses --}}
                            <div class="rounded-xl border border-slate-200 transition has-checked:border-violet-300 has-checked:bg-violet-50/50">
                                <label class="flex cursor-pointer items-start gap-3 p-3">
                                    <input type="radio" name="status" value="ongoing" x-model="status" @checked($statusVal === 'ongoing')
                                        class="mt-0.5 h-4 w-4 border-slate-300 text-violet-600 focus:ring-violet-200">
                                    <span>
                                        <span class="block text-sm font-medium text-slate-900">Collecting responses</span>
                                        <span class="block text-xs text-slate-500">Live, respondents can find and fill it.</span>
                                    </span>
                                </label>
                                <div x-show="status === 'ongoing'" x-collapse x-cloak>
                                    @include('collections._reward_fields')
                                </div>
                            </div>
                            {{-- For sale --}}
                            <div @class([
                                'rounded-xl border transition',
                                'border-slate-200 has-checked:border-indigo-300 has-checked:bg-indigo-50/50' => $canPublish,
                                'cursor-not-allowed border-slate-100 bg-slate-50/50 opacity-60' => !$canPublish,
                            ])>
                                <label @class(['flex items-start gap-3 p-3', 'cursor-pointer' => $canPublish, 'cursor-not-allowed' => !$canPublish])>
                                    <input type="radio" name="status" value="published" x-model="status" @checked($statusVal === 'published')
                                        @disabled(!$canPublish)
                                        class="mt-0.5 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-200">
                                    <span>
                                        <span class="block text-sm font-medium text-slate-900">For sale</span>
                                        <span class="block text-xs text-slate-500">Cleaned dataset listed in the marketplace.</span>
                                        @if (!$canPublish)
                                            <span class="mt-1 block text-xs font-medium text-amber-600">Run Half Clean first</span>
                                        @endif
                                    </span>
                                </label>
                                <div x-show="status === 'published'" x-collapse x-cloak>
                                    <div class="border-t border-indigo-200/60 p-3">
                                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Sell Price (IDR)</label>
                                        <input type="number" name="price" min="0" max="{{ config('datacore.max_price') }}"
                                            value="{{ $val('price', 0) }}"
                                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                                        <p class="mt-1.5 text-xs text-slate-400">Set 0 to list it for free.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="submit"
                        class="dc-spectrum mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                        <i data-lucide="check" class="h-4 w-4"></i>
                        {{ isset($collection) ? 'Save changes' : 'Create collection' }}
                    </button>

                    @if (isset($collection) && $collection->status === 'ongoing' && $collection->survey_ended_at === null)
                        <button type="submit" form="end-survey-form"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-5 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-100">
                            <i data-lucide="square-x" class="h-4 w-4"></i> End survey &amp; refund
                        </button>
                    @endif
                </div>

                @if (isset($collection))
                    <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                        <h3 class="font-display text-sm font-semibold text-slate-900">Pipeline</h3>

                        <div class="mt-3 space-y-1.5">
                            @foreach ([
                                ['key' => 'raw',    'label' => 'Raw',     'icon' => 'database',     'active' => true],
                                ['key' => 'clean1', 'label' => 'Half Clean', 'icon' => 'shield-check', 'active' => in_array($pipelineState, ['clean1', 'clean2'])],
                                ['key' => 'clean2', 'label' => 'Full Clean', 'icon' => 'sparkles',   'active' => $pipelineState === 'clean2'],
                            ] as $stage)
                                <div @class([
                                    'flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium',
                                    'bg-violet-50 text-violet-700'  => $stage['active'] && $stage['key'] === 'clean2',
                                    'bg-indigo-50 text-indigo-700'  => $stage['active'] && $stage['key'] === 'clean1',
                                    'bg-slate-100 text-slate-600'   => $stage['key'] === 'raw',
                                    'bg-slate-50  text-slate-300'   => ! $stage['active'] && $stage['key'] !== 'raw',
                                ])>
                                    <i data-lucide="{{ $stage['icon'] }}" class="h-3.5 w-3.5 shrink-0"></i>
                                    <span class="flex-1">{{ $stage['label'] }}</span>
                                    @if ($stage['active'] && $stage['key'] !== 'raw')
                                        <i data-lucide="check" class="h-3.5 w-3.5 shrink-0"></i>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if ($pipelineState === 'raw')
                            @if ($pipelineCount === 0)
                                <p class="mt-3 text-xs text-slate-400">No entries yet. Collect responses first, then run Half Clean.</p>
                            @else
                                <button type="submit" form="clean1-form"
                                    class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-100">
                                    <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                                    Run Half Clean &mdash; free
                                </button>
                            @endif
                        @elseif ($pipelineState === 'clean1')
                            <button type="button" @click="$dispatch('open-clean2')"
                                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-4 py-2 text-xs font-semibold text-violet-600 transition hover:bg-violet-100">
                                <i data-lucide="sparkles" class="h-3.5 w-3.5"></i>
                                Run Full Clean ({{ \App\Support\Money::format($clean2Fee) }})
                            </button>
                        @else
                            <p class="mt-3 flex items-center gap-1.5 text-xs font-medium text-violet-600">
                                <i data-lucide="sparkles" class="h-3.5 w-3.5 shrink-0"></i> Fully refined &amp; ready to publish.
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </form>

    {{-- Sidebar action forms, kept outside the main form because browsers drop nested <form> tags;
         their buttons reference them via the form="" attribute --}}
    @if (isset($collection))
        @if ($collection->status === 'ongoing' && $collection->survey_ended_at === null)
            <form id="end-survey-form" method="POST" action="{{ route('collections.end-survey', $collection) }}" x-data
                @submit.prevent="if(confirm('End this survey? Rewards for uncollected entries will be refunded to your wallet (the platform fee is not refunded). This cannot be undone.')) $el.submit()">
                @csrf
            </form>
        @endif
        @if ($pipelineState === 'raw' && $pipelineCount > 0)
            <form id="clean1-form" method="POST" action="{{ route('collections.clean1', $collection) }}">
                @csrf
            </form>
        @elseif ($pipelineState === 'clean1')
            <form id="clean2-form" method="POST" action="{{ route('collections.clean2', $collection) }}">
                @csrf
            </form>
        @endif
    @endif

    {{-- Full Clean confirmation modal --}}
    @if (isset($collection) && $pipelineState === 'clean1')
        <div x-show="clean2Open" x-cloak @keydown.escape.window="clean2Open = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="clean2Open = false"></div>
            <div x-show="clean2Open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                <div class="flex items-start gap-3.5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <i data-lucide="sparkles" class="h-5 w-5"></i>
                    </span>
                    <div>
                        <h3 class="font-display text-base font-semibold text-slate-900">Run Full Clean?</h3>
                        <p class="mt-1 text-sm leading-relaxed text-slate-500">
                            This costs {{ \App\Support\Money::format($clean2Fee) }}, charged to your wallet immediately. This cannot be undone.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2.5">
                    <button type="button" @click="clean2Open = false"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button"
                        @click="clean2Open = false; document.getElementById('clean2-form').submit()"
                        class="dc-spectrum inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                        <i data-lucide="sparkles" class="h-4 w-4"></i> Run Full Clean
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showImport)
        <form method="POST" action="{{ route('collections.import') }}" enctype="multipart/form-data"
            x-show="mode === 'import'" x-cloak class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            @csrf
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Import from CSV</h2>
                    <p class="mt-1 text-sm text-slate-500">Each column becomes a field, each row becomes an entry.</p>
                    <div class="mt-5 space-y-5">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Title <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        </div>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Category</label>
                                <x-select
                                    name="category_id"
                                    :selected="''"
                                    placeholder="Uncategorized"
                                    :options="collect($categories)->map(fn($c) => ['value' => (string) $c->id, 'label' => $c->name])->prepend(['value' => '', 'label' => 'Uncategorized'])->all()"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Price (IDR)</label>
                                <input type="number" name="price" min="0"
                                    max="{{ config('datacore.max_price') }}" value="{{ old('price', 0) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                            </div>
                        </div>
                        <div x-data="{ name: '' }">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">CSV file <span class="text-rose-500">*</span></label>
                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 px-4 py-8 text-center transition hover:border-indigo-300 hover:bg-slate-50">
                                <span
                                    class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                    <i data-lucide="file-spreadsheet" class="h-5 w-5"></i>
                                </span>
                                <p class="mt-3 text-sm font-medium text-slate-700" x-show="!name">
                                    Drop CSV or <span class="text-indigo-600">browse</span>
                                </p>
                                <p class="mt-3 flex items-center gap-1.5 text-sm font-medium text-emerald-600"
                                    x-show="name" x-cloak>
                                    <i data-lucide="check-circle-2" class="h-4 w-4"></i>
                                    <span x-text="name"></span>
                                </p>
                                <input type="file" name="csv_file" accept=".csv,text/csv" required class="hidden"
                                    @change="name = $event.target.files[0]?.name ?? ''">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-1">
                <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card lg:sticky lg:top-24">
                    <h3 class="font-display text-base font-semibold text-slate-900">Visibility</h3>
                    <div class="mt-3 space-y-2">
                        <input type="hidden" name="status" value="draft">
                        <div class="flex items-start gap-3 rounded-xl border border-indigo-200 bg-indigo-50/50 p-3">
                            <i data-lucide="file-text" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500"></i>
                            <span>
                                <span class="block text-sm font-medium text-slate-900">Draft</span>
                                <span class="block text-xs text-slate-500">Run Half Clean after import, then publish to
                                    the marketplace.</span>
                            </span>
                        </div>
                    </div>
                    <button type="submit"
                        class="dc-spectrum mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                        <i data-lucide="upload" class="h-4 w-4"></i> Import dataset
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
