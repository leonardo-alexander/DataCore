<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCollectionRequest;
use App\Models\Activity;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UploadController extends Controller
{
    public function store(ImportCollectionRequest $request)
    {
        $data = $request->validated();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $collection = DB::transaction(function () use ($request, $data, $user) {
            $collection = $user->collections()->create([
                'category_id' => $data['category_id'] ?? null,
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'price'       => $data['price'] ?? 0,
                'reward'      => $data['reward'] ?? 0,
                'status'      => $data['status'],
                'type'        => 'upload',
            ]);

            $this->importCsv($collection, $request->file('csv_file')->getRealPath());

            return $collection;
        });

        Activity::log(
            $user->id,
            'collection',
            __('Dataset imported'),
            __(':title was imported from CSV.', ['title' => $collection->title]),
        );

        return redirect()->route('marketplace.show', $collection)
            ->with('success', __('Dataset imported. Run Clean 1 to refine it.'));
    }

    private function importCsv(Collection $collection, string $path): void
    {
        $handle  = fopen($path, 'r');
        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);

            throw new RuntimeException(__('The CSV file is empty or invalid.'));
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        $questions = collect($headers)
            ->map(fn ($header) => trim((string) $header))
            ->filter()
            ->values()
            ->map(fn ($header, $position) => Question::create([
                'collection_id' => $collection->id,
                'title'         => $header,
                'type'          => 'text',
                'position'      => $position,
            ]));

        while (($row = fgetcsv($handle)) !== false) {
            $values = collect($row)->map(fn ($value) => trim((string) $value));

            if ($values->filter(fn ($value) => $value !== '')->isEmpty()) {
                continue;
            }

            $raw = $values
                ->filter(fn ($value, $index) => $value !== '' && $questions->has($index))
                ->mapWithKeys(fn ($value, $index) => [$questions[$index]->title => $value]);

            Entry::create([
                'collection_id' => $collection->id,
                'user_id'       => null,
                'raw_data'      => $raw->all(),
                'status'        => 'raw',
            ]);
        }

        fclose($handle);
    }
}
