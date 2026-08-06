<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        // The carousel is a "most bought" shelf: sales first, then the datasets
        // people engaged with, then the newest. Every term is a count or a
        // timestamp — never a nullable column — because DESC puts NULLs first on
        // Postgres and last on SQLite, which would have ordered the shelf
        // differently in production than in development. A marketplace where
        // nothing has sold yet still fills up, from the tie-breakers down.
        $featured = Collection::published()
            ->with(['user', 'category'])
            ->withCount(['entries', 'reviews', 'purchases'])
            ->orderByDesc('purchases_count')
            ->orderByDesc('reviews_count')
            ->latest()
            ->take(6)
            ->get();

        $query = Collection::published()
            ->with(['user', 'category'])
            ->withCount(['entries', 'reviews'])
            ->withAvg('reviews', 'rating');

        if ($slug = $request->query('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $slug));
        }

        if ($search = $request->query('q')) {
            // Case-insensitive on every driver — see SurveyController.
            $query->whereLike('title', "%{$search}%", caseSensitive: false);
        }

        $sort = in_array($request->query('sort'), ['date', 'price', 'rating', 'reviews'])
            ? $request->query('sort')
            : 'date';

        $dir = in_array($request->query('dir'), ['asc', 'desc'])
            ? $request->query('dir')
            : 'desc';

        $column = match ($sort) {
            'price'   => 'price',
            'rating'  => 'reviews_avg_rating',
            'reviews' => 'reviews_count',
            default   => 'created_at',
        };

        $query->orderBy($column, $dir);

        $collections = $query->paginate(9)->withQueryString();

        return view('marketplace.index', compact('categories', 'featured', 'collections', 'sort', 'dir', 'search', 'slug'));
    }

    public function show(string $locale, Collection $collection)
    {
        abort_unless($collection->status === 'published' || $collection->user_id === Auth::id(), 404);

        $collection->load(['user.verification', 'category', 'questions', 'reviews.user'])
            ->loadCount(['entries', 'reviews']);

        $samples = $collection->entries()->orderBy('id')->take(4)->get();

        $preview = [
            'raw' => $samples->pluck('raw_data')->filter()->values(),
            'clean1' => $samples->pluck('clean1_data')->filter()->values(),
            'clean2' => $samples->pluck('clean2_data')->filter()->values(),
        ];

        $user = Auth::user();
        $purchased = $collection->purchasedBy($user);
        $owns = $collection->user_id === $user?->id;
        $canReview = $purchased && ! $collection->reviews()->where('user_id', $user?->id)->exists();

        return view('marketplace.show', compact('collection', 'preview', 'purchased', 'owns', 'canReview'));
    }
}
