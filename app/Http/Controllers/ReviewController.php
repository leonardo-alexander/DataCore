<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Activity;
use App\Models\Collection;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(string $locale, StoreReviewRequest $request, Collection $collection)
    {
        $data = $request->validated();

        $review = Review::updateOrCreate(
            ['collection_id' => $collection->id, 'user_id' => Auth::id()],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null],
        );

        Activity::log(
            $collection->user_id,
            'review',
            __('New review'),
            __(':title received a :rating-star review.', ['title' => $collection->title, 'rating' => $data['rating']]),
        );

        return back()->with('success', $review->wasRecentlyCreated
            ? __('Thanks for your review!')
            : __('Review updated successfully!'));
    }

    public function delete(string $locale, Collection $collection)
    {
        Review::where('collection_id', $collection->id)
            ->where('user_id', Auth::id())
            ->firstOrFail()
            ->delete();

        return back()->with('success', __('Review deleted!'));
    }
}
