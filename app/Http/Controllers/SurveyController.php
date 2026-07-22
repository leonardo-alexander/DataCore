<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SurveyController extends Controller
{
    public function __invoke(Request $request)
    {
        $userId     = Auth::id();
        $categories = Category::orderBy('name')->get();

        $query = Collection::where('status', 'ongoing')
            ->whereNull('survey_ended_at')
            ->whereHas('questions')
            ->where('user_id', '!=', $userId)
            ->whereDoesntHave('entries', fn ($q) => $q->where('user_id', $userId))
            ->where(function ($q) {
                $q->whereNull('respondent_target')
                  ->orWhereRaw('(SELECT COUNT(*) FROM entries WHERE entries.collection_id = collections.id) < respondent_target');
            })
            ->with(['user', 'category'])
            ->withCount(['entries', 'questions']);

        if ($slug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
        }

        if ($search = $request->query('q')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $sort = in_array($request->query('sort'), ['date', 'reward', 'responses'])
            ? $request->query('sort')
            : 'date';

        $dir = in_array($request->query('dir'), ['asc', 'desc'])
            ? $request->query('dir')
            : 'desc';

        $column = match ($sort) {
            'reward'    => 'reward',
            'responses' => 'entries_count',
            default     => 'created_at',
        };

        $query->orderBy($column, $dir);

        $surveys = $query->paginate(9)->withQueryString();

        return view('surveys.index', compact('surveys', 'categories', 'slug', 'search', 'sort', 'dir'));
    }
}
