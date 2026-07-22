<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Collection;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(string $locale, Request $request, Collection $collection)
    {
        $user = Auth::user();

        abort_unless($collection->purchasedBy($user), 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = Review::updateOrCreate(
            ['collection_id' => $collection->id, 'user_id' => $user->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null],
        );

        Activity::log($collection->user_id, 'review', 'New review', $collection->title . ' received a ' . $data['rating'] . '-star review.');

        $message = $review->wasRecentlyCreated ? 'Thanks for your review!' : 'Review updated successfully!';
        return back()->with('success', $message);
    }

    public function delete(string $locale, Collection $collection){
        $user = Auth::user();

        $review = Review::where('collection_id', $collection->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $review->delete();

        return back()->with('success', 'Review deleted!');
    }
}
