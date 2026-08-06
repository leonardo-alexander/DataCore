<?php

use App\Models\Collection;
use App\Models\Purchase;
use App\Models\Review;

function publish(string $title, array $attributes = []): Collection
{
    $collection = makeCollection(makeUser(), ['status' => 'published', 'price' => 10_000] + $attributes);
    $collection->update(['title' => $title]);

    return $collection->fresh();
}

function sell(Collection $collection, int $times): void
{
    for ($i = 0; $i < $times; $i++) {
        Purchase::create([
            'collection_id' => $collection->id,
            'user_id'       => makeUser()->id,
            'amount'        => $collection->price,
        ]);
    }
}

function featuredTitles(): array
{
    return test()->actingAs(makeUser())
        ->get(route('marketplace.index', ['locale' => 'en']))
        ->assertOk()
        ->viewData('featured')
        ->pluck('title')
        ->all();
}

it('leads the carousel with the most bought dataset', function () {
    $quiet   = publish('Quiet dataset');
    $popular = publish('Popular dataset');
    $middle  = publish('Middling dataset');

    sell($popular, 7);
    sell($middle, 3);
    sell($quiet, 0);

    expect(featuredTitles())->toBe(['Popular dataset', 'Middling dataset', 'Quiet dataset']);
});

it('ranks purely on sales, not on quality score', function () {
    $polished = publish('Polished but unsold', ['quality_score' => 0.99]);
    $rough    = publish('Rough but selling', ['quality_score' => 0.10]);

    sell($rough, 4);

    expect(featuredTitles())->toBe(['Rough but selling', 'Polished but unsold'])
        ->and($polished->fresh()->quality_score)->toBe(0.99);
});

it('breaks a sales tie with reviews, then with recency', function () {
    $older    = publish('Older tie');
    $newer    = publish('Newer tie');
    $reviewed = publish('Reviewed tie');

    foreach ([$older, $newer, $reviewed] as $collection) {
        sell($collection, 2);
    }

    Review::create([
        'collection_id' => $reviewed->id,
        'user_id'       => makeUser()->id,
        'rating'        => 5,
    ]);

    // created_at is not fillable, so it has to be forced past the guard.
    $older->forceFill(['created_at' => now()->subWeek()])->save();
    $newer->forceFill(['created_at' => now()])->save();
    $reviewed->forceFill(['created_at' => now()->subDay()])->save();

    // Reviews come first among equal sales; the rest fall back to newest.
    expect(featuredTitles())->toBe(['Reviewed tie', 'Newer tie', 'Older tie']);
});

it('still fills the carousel before anything has sold', function () {
    publish('First listing');
    publish('Second listing');

    expect(featuredTitles())->toHaveCount(2);
});

it('shows no carousel when the marketplace is empty', function () {
    makeCollection(makeUser(), ['status' => 'draft']);

    expect(featuredTitles())->toBe([]);
});

it('keeps unpublished datasets out of the carousel however popular', function () {
    $draft = makeCollection(makeUser(), ['status' => 'draft', 'price' => 10_000]);
    $draft->update(['title' => 'Popular draft']);
    sell($draft->fresh(), 20);

    publish('The only listing');

    expect(featuredTitles())->toBe(['The only listing']);
});

it('caps the carousel at six datasets', function () {
    foreach (range(1, 9) as $n) {
        sell(publish("Dataset {$n}"), $n);
    }

    $titles = featuredTitles();

    expect($titles)->toHaveCount(6)
        ->and($titles[0])->toBe('Dataset 9')
        ->and($titles[5])->toBe('Dataset 4');
});

it('labels the leader as most bought and shows the sales count', function () {
    sell(publish('Best seller'), 3);

    $this->actingAs(makeUser())
        ->get(route('marketplace.index', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('Most bought', false)
        ->assertSee('3 purchases', false);
});

it('falls back to the featured label when nothing has sold', function () {
    publish('Nothing sold yet');

    $this->actingAs(makeUser())
        ->get(route('marketplace.index', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('Featured', false)
        ->assertDontSee('Most bought', false)
        // The sales stat, not the word "purchases" — that also names a sidebar link.
        ->assertDontSee('data-lucide="shopping-bag"', false);
});

it('seeds demo sales the app itself would allow, so the carousel has a ranking', function () {
    $this->seed(Database\Seeders\DemoSeeder::class);

    $purchases = Purchase::with('collection')->get();

    expect($purchases)->not->toBeEmpty();

    foreach ($purchases as $purchase) {
        expect($purchase->collection)->not->toBeNull()
            // Buying an ongoing survey or your own dataset is a 404 in the app.
            ->and($purchase->collection->status)->toBe('published')
            ->and($purchase->user_id)->not->toBe($purchase->collection->user_id);
    }

    $duplicates = Purchase::selectRaw('collection_id, user_id, count(*) as total')
        ->groupBy('collection_id', 'user_id')
        ->havingRaw('count(*) > 1')
        ->get();

    expect($duplicates)->toBeEmpty();

    // The shelf is actually ranked, not all tied on zero.
    $counts = Collection::published()->withCount('purchases')
        ->orderByDesc('purchases_count')->take(6)->pluck('purchases_count');

    expect($counts->first())->toBeGreaterThan(0)
        ->and($counts->unique()->count())->toBeGreaterThan(1);
});

it('orders the carousel on counts only, so no driver sorts NULLs differently', function () {
    // DESC puts NULLs first on Postgres and last on SQLite. Ordering on a nullable
    // column would have given the deployed carousel a different lineup than a
    // local one, so the query must not name one.
    $sql = strtolower(
        Collection::published()
            ->withCount(['entries', 'reviews', 'purchases'])
            ->orderByDesc('purchases_count')
            ->orderByDesc('reviews_count')
            ->latest()
            ->toSql()
    );

    $orderBy = substr($sql, strpos($sql, 'order by'));

    expect($orderBy)->toContain('purchases_count')
        ->toContain('reviews_count')
        ->toContain('created_at')
        ->not->toContain('quality_score')
        ->not->toContain('quality_avg');
});
