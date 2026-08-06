<?php

use App\Models\Purchase;
use App\Models\Review;

function buy(App\Models\Collection $collection, App\Models\User $buyer): void
{
    test()->actingAs($buyer)
        ->post(route('marketplace.purchase', ['locale' => 'en', 'collection' => $collection]))
        ->assertSessionHasNoErrors();
}

it('lets a buyer review a dataset they bought', function () {
    $survey = makeCollection(makeUser(), ['status' => 'published', 'price' => 10_000]);
    $buyer  = makeUser([], [], 100_000);

    buy($survey, $buyer);

    $this->actingAs($buyer)
        ->post(route('marketplace.review', ['locale' => 'en', 'collection' => $survey]), [
            'rating'  => 5,
            'comment' => 'Clean and complete.',
        ])
        ->assertSessionHas('success');

    expect(Review::where('collection_id', $survey->id)->where('user_id', $buyer->id)->value('rating'))->toBe(5);
});

it('refuses a review from someone who has not bought the dataset', function () {
    $survey   = makeCollection(makeUser(), ['status' => 'published', 'price' => 10_000]);
    $stranger = makeUser([], [], 100_000);

    $this->actingAs($stranger)
        ->post(route('marketplace.review', ['locale' => 'en', 'collection' => $survey]), ['rating' => 1])
        ->assertForbidden();

    expect(Review::where('collection_id', $survey->id)->count())->toBe(0);
});

it('replaces a review rather than stacking a second one', function () {
    $survey = makeCollection(makeUser(), ['status' => 'published', 'price' => 10_000]);
    $buyer  = makeUser([], [], 100_000);

    buy($survey, $buyer);

    $this->actingAs($buyer)->post(route('marketplace.review', ['locale' => 'en', 'collection' => $survey]), ['rating' => 3]);
    $this->actingAs($buyer)->post(route('marketplace.review', ['locale' => 'en', 'collection' => $survey]), ['rating' => 5]);

    expect(Review::where('collection_id', $survey->id)->count())->toBe(1)
        ->and(Review::where('collection_id', $survey->id)->value('rating'))->toBe(5);
});

it('rejects a rating outside one to five', function () {
    $survey = makeCollection(makeUser(), ['status' => 'published', 'price' => 10_000]);
    $buyer  = makeUser([], [], 100_000);

    buy($survey, $buyer);

    $this->actingAs($buyer)
        ->post(route('marketplace.review', ['locale' => 'en', 'collection' => $survey]), ['rating' => 9])
        ->assertSessionHasErrors('rating');
});

it('deletes only the reviewer\'s own review', function () {
    $survey = makeCollection(makeUser(), ['status' => 'published', 'price' => 0]);
    $one    = makeUser([], [], 10_000);
    $two    = makeUser([], [], 10_000);

    buy($survey, $one);
    buy($survey, $two);

    $this->actingAs($one)->post(route('marketplace.review', ['locale' => 'en', 'collection' => $survey]), ['rating' => 4]);
    $this->actingAs($two)->post(route('marketplace.review', ['locale' => 'en', 'collection' => $survey]), ['rating' => 2]);

    $this->actingAs($one)
        ->delete(route('marketplace.review.delete', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('success');

    expect(Review::where('collection_id', $survey->id)->count())->toBe(1)
        ->and(Review::where('collection_id', $survey->id)->value('user_id'))->toBe($two->id);
});

it('gives a free dataset away without touching the wallet', function () {
    $owner  = makeUser([], [], 5_000);
    $survey = makeCollection($owner, ['status' => 'published', 'price' => 0]);
    $buyer  = makeUser([], [], 5_000);

    buy($survey, $buyer);

    expect(Purchase::where('collection_id', $survey->id)->value('amount'))->toBe(0)
        ->and($buyer->fresh()->balance())->toBe(5_000)
        ->and($owner->fresh()->balance())->toBe(5_000);
});

it('imports a CSV as a draft that still has to be cleaned before it can be sold', function () {
    $user = makeUser();

    $csv = Illuminate\Http\UploadedFile::fake()->createWithContent(
        'data.csv',
        "Name,City\nAda,Jakarta\nGrace,Bandung\n",
    );

    $this->actingAs($user)
        ->post(route('collections.import', ['locale' => 'en']), [
            'title'    => 'Imported people',
            'status'   => 'draft',
            'csv_file' => $csv,
        ])
        ->assertSessionHasNoErrors();

    $collection = App\Models\Collection::where('title', 'Imported people')->firstOrFail();

    expect($collection->status)->toBe('draft')
        ->and($collection->questions->pluck('title')->all())->toBe(['Name', 'City'])
        ->and($collection->entries()->count())->toBe(2)
        ->and($collection->cleanState())->toBe('raw');
});

it('will not import a CSV straight into the marketplace', function () {
    $csv = Illuminate\Http\UploadedFile::fake()->createWithContent('data.csv', "Name\nAda\n");

    $this->actingAs(makeUser())
        ->post(route('collections.import', ['locale' => 'en']), [
            'title'    => 'Straight to sale',
            'status'   => 'published',
            'csv_file' => $csv,
        ])
        ->assertSessionHasErrors('status');

    expect(App\Models\Collection::where('title', 'Straight to sale')->exists())->toBeFalse();
});
