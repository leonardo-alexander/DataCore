<?php

namespace App\Services;

use App\Models\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CleaningService
{
    public function buildCsv(Collection $collection): string
    {
        $collection->loadMissing(['entries', 'questions']);

        $headers = $collection->questions->pluck('title')->map(fn ($t) => trim($t))->all();

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($collection->entries as $entry) {
            $row = [];

            foreach ($collection->questions as $question) {
                $key = trim($question->title);
                $row[] = $entry->raw_data[$key] ?? '';
            }

            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    public function process(Collection $collection, string $stage): array
    {
        if ($collection->entries()->count() === 0) {
            throw new RuntimeException('This collection has no entries to refine yet.');
        }

        $mode = $stage === 'clean2' ? 'full' : 'step1';
        $csv = $this->buildCsv($collection);

        // The cleaning service sleeps when idle. Waking it takes ~20s, during which
        // it either holds the connection open or answers 5xx — so back off between
        // attempts rather than giving up inside three seconds. throw: false keeps
        // the raw RequestException (status code + response body) out of our errors.
        //
        // The whole budget stays under 95s on purpose: QUEUE_CONNECTION is sync in
        // production, so even the "background" clean runs inside the web request,
        // and a request that outlives the platform's timeout returns a 502 instead
        // of our error page. 45 + 5 + 45 covers a cold start plus one retry.
        try {
            $response = Http::timeout(45)
                ->connectTimeout(15)
                ->retry(2, 5000, throw: false)
                ->attach('file', $csv, 'dataset.csv')
                ->post($this->endpoint().'?mode='.$mode);
        } catch (ConnectionException $e) {
            throw new RuntimeException('The cleaning service could not be reached. Please try again in a minute.', previous: $e);
        }

        if ($response->serverError()) {
            throw new RuntimeException('The cleaning service is starting up. Please try again in a minute.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('Cleaning service responded with status '.$response->status().'.');
        }

        $data = $response->json();

        if (! isset($data['data']) || ! is_array($data['data'])) {
            throw new RuntimeException('Cleaning service returned an unexpected response.');
        }

        return $data;
    }

    public function apply(Collection $collection, array $payload, string $stage): void
    {
        $column = $stage === 'clean2' ? 'clean2_data' : 'clean1_data';
        $report = $payload['report'] ?? [];

        DB::transaction(function () use ($collection, $payload, $report, $column, $stage) {
            $collection->update([
                'cleaning_report' => $report,
                'quality_score' => $report['quality']['final_quality_score'] ?? $collection->quality_score,
                'quality_avg' => $report['quality']['avg_score'] ?? $collection->quality_avg,
            ]);

            $entries = $collection->entries()->orderBy('id')->get();

            foreach ($entries as $i => $entry) {
                if (! isset($payload['data'][$i])) {
                    continue;
                }

                $entry->update([
                    $column => $payload['data'][$i],
                    'status' => $stage,
                ]);
            }
        });
    }

    public function rowCount(array $payload): int
    {
        return is_array($payload['data'] ?? null) ? count($payload['data']) : 0;
    }

    private function endpoint(): string
    {
        return rtrim(config('datacore.cleaning_url'), '/');
    }
}
