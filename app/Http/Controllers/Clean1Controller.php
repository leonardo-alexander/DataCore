<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCleaning;
use App\Models\Collection;
use App\Services\CleaningService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Clean1Controller extends Controller
{
    public function __invoke(string $locale, Collection $collection, CleaningService $cleaning)
    {
        abort_unless($collection->user_id === Auth::id(), 403);

        $count = $collection->entries()->count();

        if ($count === 0) {
            return back()->with('error', 'This collection has no entries to refine yet.');
        }

        if ($count > config('datacore.cleaning_sync_limit')) {
            ProcessCleaning::dispatch($collection, Auth::user(), 'clean1');
            return back()->with('success', 'Large dataset. Clean 1 is running in the background. You will be notified when done.');
        }

        try {
            $payload = $cleaning->process($collection, 'clean1');
            $cleaning->apply($collection, $payload, 'clean1');
        } catch (Throwable $e) {
            return back()->with('error', 'Clean 1 failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Clean 1 complete!');
    }
}
