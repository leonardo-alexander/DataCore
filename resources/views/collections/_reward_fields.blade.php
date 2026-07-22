{{-- Reward pool fields, expands inside the "Collecting responses" visibility option --}}
<div data-tour="survey-reward" class="border-t border-violet-200/60 p-3">
    @if ($budgetLocked)
        {{-- Locked: escrow already taken --}}
        <input type="hidden" name="reward" value="{{ $collection->reward }}">
        <input type="hidden" name="respondent_target" value="{{ $collection->respondent_target }}">
        <div class="flex items-center gap-2">
            <i data-lucide="lock" class="h-4 w-4 shrink-0 text-violet-500"></i>
            <span class="text-sm font-medium text-slate-900">Reward pool locked</span>
        </div>
        <div class="mt-2 space-y-1 text-xs text-slate-500">
            <div class="flex justify-between">
                <span>Rp {{ number_format($collection->reward, 0, ',', '.') }} × {{ number_format($collection->respondent_target, 0, ',', '.') }} respondents</span>
                <span>Rp {{ number_format($collection->reward * $collection->respondent_target, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Platform fee ({{ round($feeRate * 100) }}%)</span>
                <span>Rp {{ number_format($collection->reward_budget - $collection->reward * $collection->respondent_target, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between border-t border-violet-200 pt-1 font-semibold text-violet-700">
                <span>Escrowed</span>
                <span>Rp {{ number_format($collection->reward_budget, 0, ',', '.') }}</span>
            </div>
        </div>
    @else
        <div class="space-y-3">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Entry reward (IDR) <span class="text-rose-500">*</span></label>
                <input type="number" name="reward" x-model.number="reward"
                    :min="status === 'ongoing' ? 1 : 0" :required="status === 'ongoing'"
                    max="{{ config('datacore.max_price') }}" value="{{ $val('reward', 0) }}"
                    placeholder="0"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Target respondents <span class="text-rose-500">*</span></label>
                <input type="number" name="respondent_target" x-model.number="target"
                    :required="status === 'ongoing'"
                    min="1" max="1000000" value="{{ $val('respondent_target', '') }}"
                    placeholder="e.g. 100"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </div>
        </div>
        <div x-show="budget > 0" x-cloak class="mt-3 space-y-1 rounded-lg bg-white/70 px-3 py-2.5 text-xs ring-1 ring-violet-100">
            <div class="flex items-center justify-between text-slate-600">
                <span>Respondent rewards</span>
                <span x-text="'Rp ' + base.toLocaleString('id-ID')"></span>
            </div>
            <div class="flex items-center justify-between text-slate-500">
                <span>Platform fee ({{ round($feeRate * 100) }}%)</span>
                <span x-text="'Rp ' + fee.toLocaleString('id-ID')"></span>
            </div>
            <div class="flex items-center justify-between border-t border-violet-200 pt-1 font-semibold text-violet-700">
                <span class="flex items-center gap-1.5"><i data-lucide="wallet" class="h-3.5 w-3.5"></i> Total debited</span>
                <span x-text="'Rp ' + budget.toLocaleString('id-ID')"></span>
            </div>
        </div>
    @endif
</div>
