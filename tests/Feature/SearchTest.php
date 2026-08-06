<?php

use App\Models\Collection;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Search has to stay case-insensitive on every driver. SQLite (development)
 * treats LIKE as case-insensitive for ASCII, Postgres (production) does not — so
 * a behaviour test alone passes here while the deployed app finds nothing. The
 * driver assertions below pin the compiled SQL as well.
 */

it('finds a survey whatever case the query is typed in', function (string $query) {
    $survey = makeCollection(makeUser(), ['reward' => 1_000, 'respondent_target' => 5], ['Q']);
    $survey->update(['title' => 'Coffee Habits In Jakarta']);

    $this->actingAs(makeUser())
        ->get(route('surveys.index', ['locale' => 'en', 'q' => $query]))
        ->assertOk()
        ->assertSee('Coffee Habits In Jakarta', false);
})->with(['coffee', 'COFFEE', 'Coffee', 'cOfFeE hAbItS', 'jakarta']);

it('finds a marketplace dataset whatever case the query is typed in', function (string $query) {
    $dataset = makeCollection(makeUser(), ['status' => 'published', 'price' => 10_000]);
    $dataset->update(['title' => 'Retail Prices Survey']);

    $this->actingAs(makeUser())
        ->get(route('marketplace.index', ['locale' => 'en', 'q' => $query]))
        ->assertOk()
        ->assertSee('Retail Prices Survey', false);
})->with(['retail', 'RETAIL', 'prices survey']);

it('finds an owned collection whatever case the query is typed in', function (string $query) {
    $owner = makeUser();
    makeCollection($owner, ['status' => 'draft'])->update(['title' => 'Household Budget 2026']);

    $this->actingAs($owner)
        ->get(route('collections.index', ['locale' => 'en', 'search' => $query]))
        ->assertOk()
        ->assertSee('Household Budget 2026', false);
})->with(['household', 'HOUSEHOLD', 'budget']);

it('finds a user in the admin console whatever case the query is typed in', function (string $query) {
    makeUser(['name' => 'Grace Hopper', 'email' => 'Grace.Hopper@example.test']);

    $this->actingAs(makeUser(['is_admin' => true]))
        ->get(route('admin.dashboard', ['locale' => 'en', 'q' => $query]))
        ->assertOk()
        ->assertSee('Grace Hopper', false);
})->with(['grace', 'GRACE', 'hopper@example']);

it('returns nothing for a survey title that does not exist', function () {
    makeCollection(makeUser(), ['reward' => 1_000, 'respondent_target' => 5])
        ->update(['title' => 'Coffee Habits']);

    $this->actingAs(makeUser())
        ->get(route('surveys.index', ['locale' => 'en', 'q' => 'zzz-no-such-survey']))
        ->assertOk()
        ->assertDontSee('Coffee Habits', false);
});

it('keeps the search term when a category or sort is applied', function () {
    $survey = makeCollection(makeUser(), ['reward' => 1_000, 'respondent_target' => 5]);
    $survey->update(['title' => 'Coffee Habits']);

    $this->actingAs(makeUser())
        ->get(route('surveys.index', ['locale' => 'en', 'q' => 'coffee', 'sort' => 'reward', 'dir' => 'asc']))
        ->assertOk()
        ->assertSee('Coffee Habits', false)
        ->assertSee('value="coffee"', false);
});

it('compiles a case-insensitive search on every driver we deploy to', function (string $driver, string $expected) {
    $sql = DB::connection($driver)
        ->table('collections')
        ->whereLike('title', '%coffee%', caseSensitive: false)
        ->toSql();

    expect(strtolower($sql))->toContain($expected);
})->with([
    // Postgres is what Render runs; a plain LIKE there is case-sensitive.
    ['pgsql', 'ilike'],
    ['mysql', 'like'],
    ['sqlite', 'like'],
]);

it('never falls back to a case-sensitive LIKE in a search controller', function () {
    $sources = [
        app_path('Http/Controllers/SurveyController.php'),
        app_path('Http/Controllers/MarketplaceController.php'),
        app_path('Http/Controllers/CollectionController.php'),
        app_path('Http/Controllers/AdminController.php'),
    ];

    foreach ($sources as $source) {
        expect(file_get_contents($source))->not->toContain("'like'", basename($source));
    }
});
