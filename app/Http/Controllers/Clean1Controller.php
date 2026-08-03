<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCleaning;
use App\Models\Collection;
use App\Services\CleaningService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class Clean1Controller extends Controller
{
    public function __invoke(string $locale, Collection $collection, CleaningService $cleaning)
    {
        abort_unless($collection->user_id === Auth::id(), 403);

        $count = $collection->entries()->count();

        if ($count === 0) {
            return back()->with('error', __('This collection has no entries to refine yet.'));
        }

        // Without this, every click while a clean is queued or running dispatches
        // another one, stacking duplicate runs and duplicate failure notifications.
        $lock = Cache::lock(ProcessCleaning::lockKey($collection), ProcessCleaning::LOCK_SECONDS);

        if (! $lock->get()) {
            return back()->with('error', __('A clean is already running for this collection. Please wait for it to finish.'));
        }

        if ($count > config('datacore.cleaning_sync_limit')) {
            ProcessCleaning::dispatch($collection, Auth::user(), 'clean1', $lock->owner());

            return back()->with('success', __('Large dataset. Clean 1 is running in the background. You will be notified when done.'));
        }

        try {
            $payload = $cleaning->process($collection, 'clean1');
            $cleaning->apply($collection, $payload, 'clean1');
        } catch (Throwable $e) {
            Log::error('Clean 1 failed', ['collection_id' => $collection->id, 'error' => $e->getMessage()]);

            return back()->with('error', __('Clean 1 could not be completed. Please try again in a moment.'));
        } finally {
            $lock->release();
        }

        return back()->with('success', __('Clean 1 complete!'));
    }
}
