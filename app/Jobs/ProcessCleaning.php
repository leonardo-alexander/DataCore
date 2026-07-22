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
use Throwable;

class ProcessCleaning implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 1;

    public function __construct(
        public Collection $collection,
        public User $user,
        public string $stage,
    ) {}

    public function handle(CleaningService $cleaning, WalletService $wallet): void
    {
        $label = $this->stage === 'clean2' ? 'Clean 2' : 'Clean 1';

        try {
            $payload = $cleaning->process($this->collection, $this->stage);
        } catch (Throwable $e) {
            Activity::log($this->user->id, 'cleaning', $label . ' failed', $this->collection->title . ' — ' . $e->getMessage());
            return;
        }

        if ($this->stage === 'clean2') {
            $fee = (int) config('datacore.clean2_fee');

            try {
                $wallet->debit($this->user, $fee, 'payment', [
                    'collection_id' => $this->collection->id,
                    'description' => 'Clean 2 premium refine — ' . $this->collection->title,
                    'activity' => 'Clean 2 charged Rp ' . number_format($fee, 0, ',', '.') . ' for ' . $this->collection->title,
                ]);
            } catch (InsufficientBalanceException $e) {
                Activity::log($this->user->id, 'cleaning', 'Clean 2 not charged', 'Top up your wallet and try again — ' . $this->collection->title . '.');
                return;
            }
        }

        $cleaning->apply($this->collection, $payload, $this->stage);

        $rows = $cleaning->rowCount($payload);
        $score = $payload['report']['quality']['final_quality_score'] ?? null;

        Activity::log(
            $this->user->id,
            'cleaning',
            $label . ' complete',
            $this->collection->title . ' — ' . $rows . ' rows refined' . ($score !== null ? ', quality ' . $score . '.' : '.'),
        );
    }

    public function failed(Throwable $e): void
    {
        $label = $this->stage === 'clean2' ? 'Clean 2' : 'Clean 1';

        Activity::log($this->user->id, 'cleaning', $label . ' failed', $this->collection->title . ' could not be refined.');
    }
}
