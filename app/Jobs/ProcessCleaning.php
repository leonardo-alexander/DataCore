<?php

namespace App\Jobs;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Activity;
use App\Models\Collection;
use App\Models\User;
use App\Services\CleaningService;
use App\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class ProcessCleaning implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 1;

    /**
     * How long a dispatcher may hold the clean lock.
     *
     * Has to outlast the longest legitimate clean, or the lock expires mid-run and
     * a second clean can start on top of the first — which for Clean 2 means being
     * charged twice. Kept equal to the job timeout so a killed process (platform
     * request timeout, worker restart) still releases it instead of blocking the
     * collection forever.
     */
    public const LOCK_SECONDS = 300;

    public function __construct(
        public Collection $collection,
        public User $user,
        public string $stage,
        public ?string $lockOwner = null,
    ) {}

    /**
     * One clean at a time per collection. The dispatcher takes the lock and hands
     * its owner token here so this job can release it when the work is done.
     */
    public static function lockKey(Collection $collection): string
    {
        return 'cleaning:'.$collection->id;
    }

    public function handle(CleaningService $cleaning, WalletService $wallet): void
    {
        try {
            $this->run($cleaning, $wallet);
        } finally {
            $this->releaseLock();
        }
    }

    private function run(CleaningService $cleaning, WalletService $wallet): void
    {
        $label = $this->stage === 'clean2' ? 'Clean 2' : 'Clean 1';

        try {
            $payload = $cleaning->process($this->collection, $this->stage);
        } catch (Throwable $e) {
            report($e);

            Activity::log($this->user->id, 'cleaning', $label.' failed', $this->collection->title.' — '.$this->reason($e));

            return;
        }

        if ($this->stage === 'clean2') {
            $fee = (int) config('datacore.clean2_fee');

            try {
                $wallet->debit($this->user, $fee, 'payment', [
                    'collection_id' => $this->collection->id,
                    'description' => 'Clean 2 premium refine — '.$this->collection->title,
                    'activity' => 'Clean 2 charged Rp '.number_format($fee, 0, ',', '.').' for '.$this->collection->title,
                ]);
            } catch (InsufficientBalanceException $e) {
                Activity::log($this->user->id, 'cleaning', 'Clean 2 not charged', 'Top up your wallet and try again — '.$this->collection->title.'.');

                return;
            }
        }

        $cleaning->apply($this->collection, $payload, $this->stage);

        $rows = $cleaning->rowCount($payload);
        $score = $payload['report']['quality']['final_quality_score'] ?? null;

        Activity::log(
            $this->user->id,
            'cleaning',
            $label.' complete',
            $this->collection->title.' — '.$rows.' rows refined'.($score !== null ? ', quality '.$score.'.' : '.'),
        );
    }

    public function failed(Throwable $e): void
    {
        $label = $this->stage === 'clean2' ? 'Clean 2' : 'Clean 1';

        Activity::log($this->user->id, 'cleaning', $label.' failed', $this->collection->title.' could not be refined.');

        // handle()'s finally covers the normal path; this catches a worker killed
        // mid-job, where that finally never ran.
        $this->releaseLock();
    }

    /**
     * Text safe to show a user. Only the cleaning service's own RuntimeExceptions
     * are written for a person — an HTTP client failure would put the status code
     * and a slice of the response body straight into their notifications.
     */
    private function reason(Throwable $e): string
    {
        return $e instanceof RuntimeException
            ? $e->getMessage()
            : 'it could not be refined right now. Please try again in a moment.';
    }

    private function releaseLock(): void
    {
        if ($this->lockOwner === null) {
            return;
        }

        Cache::restoreLock(static::lockKey($this->collection), $this->lockOwner)->release();
    }
}
