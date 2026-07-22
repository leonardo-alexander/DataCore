<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Entry;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price'  => ['nullable', 'integer', 'min:0', 'max:'.config('datacore.max_price')],
            'reward' => ['nullable', 'integer', 'min:0', 'max:'.config('datacore.max_price')],
            'status' => ['required', 'in:draft,published'],
            // Validated by extension rather than mimes:csv — CSVs exported from Excel
            // are very commonly reported as application/vnd.ms-excel, which Symfony's
            // MIME map doesn't associate with the csv extension, so mimes:csv silently
            // rejects perfectly valid CSV files.
            'csv_file' => ['required', 'file', 'extensions:csv,txt', 'max:10240'],
        ]);

        $collection = DB::transaction(function () use ($request, $data) {
            $collection = Auth::user()->collections()->create([
                'category_id' => $data['category_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'price'  => $data['price'] ?? 0,
                'reward' => $data['reward'] ?? 0,
                'status' => $data['status'],
                'type'   => 'upload',
            ]);

            $file = fopen($request->file('csv_file')->getRealPath(), 'r');
            $headers = fgetcsv($file);

            if (! $headers) {
                throw new \RuntimeException('The CSV file is empty or invalid.');
            }

            // Strip a UTF-8 BOM from the first header, common in CSVs exported from Excel.
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

            $questions = [];
            $position = 0;

            foreach ($headers as $header) {
                $header = trim($header);

                if ($header === '') {
                    continue;
                }

                $questions[] = Question::create([
                    'collection_id' => $collection->id,
                    'title' => $header,
                    'type' => 'text',
                    'position' => $position++,
                ]);
            }

            while (($row = fgetcsv($file)) !== false) {
                if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                $raw = [];

                foreach ($row as $index => $value) {
                    if (! isset($questions[$index])) {
                        continue;
                    }

                    $value = trim((string) $value);

                    if ($value !== '') {
                        $raw[$questions[$index]->title] = $value;
                    }
                }

                Entry::create([
                    'collection_id' => $collection->id,
                    // Imported rows are historical dataset rows, not a single respondent's
                    // submission, so they don't carry a user_id (which is uniquely
                    // constrained per collection to stop duplicate survey submissions).
                    'user_id' => null,
                    'raw_data' => $raw,
                    'status' => 'raw',
                ]);
            }

            fclose($file);

            return $collection;
        });

        Activity::log(Auth::id(), 'collection', 'Dataset imported', $collection->title.' was imported from CSV.');

        return redirect()->route('marketplace.show', $collection)
            ->with('success', 'Dataset imported. Run Clean 1 to refine it.');
    }
}
