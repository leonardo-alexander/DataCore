<?php

use App\Models\Collection;
use App\Models\Entry;
use App\Models\Purchase;
use Illuminate\Support\Facades\Http;

/**
 * The flow the FAQ describes: end the survey, clean it, then sell it.
 */

function cleaningResponse(array $rows): array
{
    return [
        'data'   => $rows,
        'report' => ['quality' => ['final_quality_score' => 0.91, 'avg_score' => 0.88]],
    ];
}

function submitEntry(App\Models\Collection $survey, ?App\Models\User $respondent = null, string $answer = 'Fine'): App\Models\User
{
    $respondent ??= makeUser();

    test()->actingAs($respondent)->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
        'answers' => [$survey->questions->first()->id => $answer],
    ])->assertSessionHasNoErrors();

    return $respondent;
}

it('walks a survey from collecting to sold', function () {
    Http::fake([
        '*' => Http::response(cleaningResponse([
            ['How was it?' => 'Great'],
            ['How was it?' => 'Fine'],
        ])),
    ]);

    $owner  = makeUser([], [], 500_000);
    $survey = makeCollection($owner, [
        'status'            => 'ongoing',
        'reward'            => 1_000,
        'respondent_target' => 10,
        'reward_budget'     => 10_500,
        'price'             => 50_000,
    ]);

    submitEntry($survey, null, 'Great');
    submitEntry($survey, null, 'Fine');

    expect($survey->fresh()->entries()->count())->toBe(2);

    // 1. End the survey.
    $this->actingAs($owner)
        ->post(route('collections.end-survey', ['locale' => 'en', 'collection' => $survey]))
        ->assertRedirect(route('collections.index', ['locale' => 'en']));

    $survey->refresh();

    expect($survey->survey_ended_at)->not->toBeNull()
        ->and($survey->status)->toBe('draft')
        ->and($survey->reward_budget)->toBe(0);

    // 2. Clean it. Publishing is refused until this has happened.
    expect($survey->cleanState())->toBe('raw');

    $this->actingAs($owner)
        ->put(route('collections.update', ['locale' => 'en', 'collection' => $survey]), [
            'title'     => $survey->title,
            'price'     => 50_000,
            'status'    => 'published',
            'questions' => [['text' => 'How was it?', 'type' => 'text']],
        ])
        ->assertSessionHasErrors('status');

    expect($survey->fresh()->status)->toBe('draft');

    $this->actingAs($owner)
        ->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('success');

    $survey->refresh();

    expect($survey->cleanState())->toBe('clean1')
        ->and($survey->quality_score)->toBe(0.91)
        ->and(Entry::where('collection_id', $survey->id)->whereNotNull('clean1_data')->count())->toBe(2);

    // 3. Sell it.
    $this->actingAs($owner)
        ->put(route('collections.update', ['locale' => 'en', 'collection' => $survey]), [
            'title'     => $survey->title,
            'price'     => 50_000,
            'status'    => 'published',
            'questions' => [['text' => 'How was it?', 'type' => 'text']],
        ])
        ->assertSessionHasNoErrors();

    expect($survey->fresh()->status)->toBe('published');

    // A buyer can now find and purchase it.
    $buyer = makeUser([], [], 200_000);

    $this->actingAs($buyer)
        ->get(route('marketplace.index', ['locale' => 'en']))
        ->assertOk()
        ->assertSee($survey->title, false);

    $this->actingAs($buyer)
        ->post(route('marketplace.purchase', ['locale' => 'en', 'collection' => $survey->fresh()]))
        ->assertRedirect(route('marketplace.show', ['locale' => 'en', 'collection' => $survey->fresh()]));

    expect(Purchase::where('collection_id', $survey->id)->where('user_id', $buyer->id)->exists())->toBeTrue()
        ->and($buyer->fresh()->balance())->toBe(150_000)
        ->and($owner->fresh()->balance())->toBeGreaterThan(500_000);
});

it('refunds the unused reward pool when the survey is ended', function () {
    $owner = makeUser([], [], 0);

    // 10 slots at 1,000 plus the 5% fee were escrowed up front.
    $survey = makeCollection($owner, [
        'reward'            => 1_000,
        'respondent_target' => 10,
        'reward_budget'     => 10_500,
    ]);

    submitEntry($survey);
    submitEntry($survey);

    $this->actingAs($owner)->post(route('collections.end-survey', ['locale' => 'en', 'collection' => $survey]));

    // 8 uncollected slots at 1,000 come back; the platform fee does not.
    expect($owner->fresh()->balance())->toBe(8_000)
        ->and($survey->fresh()->reward_budget)->toBe(0);
});

it('stops accepting responses once the survey has ended', function () {
    $survey = makeCollection(makeUser());

    submitEntry($survey);

    $survey->update(['survey_ended_at' => now(), 'status' => 'draft']);

    $this->actingAs(makeUser())
        ->get(route('entries.create', ['locale' => 'en', 'collection' => $survey]))
        ->assertNotFound();
});

it('will not end a survey twice', function () {
    $owner  = makeUser();
    $survey = makeCollection($owner, ['reward' => 1_000, 'respondent_target' => 5, 'reward_budget' => 5_250]);

    $this->actingAs($owner)->post(route('collections.end-survey', ['locale' => 'en', 'collection' => $survey]));

    $balanceAfterFirst = $owner->fresh()->balance();

    $this->actingAs($owner)
        ->post(route('collections.end-survey', ['locale' => 'en', 'collection' => $survey]))
        ->assertStatus(422);

    expect($owner->fresh()->balance())->toBe($balanceAfterFirst);
});

it('only lets the owner end, clean, or export a survey', function () {
    $survey    = makeCollection(makeUser());
    $stranger  = makeUser();

    submitEntry($survey);

    $this->actingAs($stranger)
        ->post(route('collections.end-survey', ['locale' => 'en', 'collection' => $survey]))
        ->assertForbidden();

    $this->actingAs($stranger)
        ->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]))
        ->assertForbidden();

    $this->actingAs($stranger)
        ->get(route('collections.export', ['locale' => 'en', 'collection' => $survey]))
        ->assertForbidden();
});

it('refuses Clean 2 before Clean 1', function () {
    $owner  = makeUser([], [], 500_000);
    $survey = makeCollection($owner);

    submitEntry($survey);

    $this->actingAs($owner)
        ->post(route('collections.clean2', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('error');

    expect($survey->fresh()->cleanState())->toBe('raw');
});

it('charges for Clean 2 and refuses it without the balance', function () {
    Http::fake(['*' => Http::response(cleaningResponse([['How was it?' => 'Great']]))]);

    $fee    = (int) config('datacore.clean2_fee');
    $owner  = makeUser([], [], $fee - 1);
    $survey = makeCollection($owner);

    submitEntry($survey, null, 'Great');

    $this->actingAs($owner)->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]));

    $this->actingAs($owner)
        ->post(route('collections.clean2', ['locale' => 'en', 'collection' => $survey]))
        ->assertRedirect(route('wallet.index', ['locale' => 'en']));

    expect($survey->fresh()->cleanState())->toBe('clean1');

    // Top up past the fee and it goes through.
    $owner->wallet->update(['balance' => $fee]);

    $this->actingAs($owner)
        ->post(route('collections.clean2', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('success');

    expect($survey->fresh()->cleanState())->toBe('clean2')
        ->and($owner->fresh()->balance())->toBe(0);
});

it('will not clean a collection with no entries', function () {
    $owner  = makeUser([], [], 500_000);
    $survey = makeCollection($owner);

    $this->actingAs($owner)
        ->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('error');
});

it('reports a cleaning failure without publishing anything', function () {
    Http::fake(['*' => Http::response('service starting', 503)]);

    $owner  = makeUser();
    $survey = makeCollection($owner);

    submitEntry($survey);

    $this->actingAs($owner)
        ->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('error');

    expect($survey->fresh()->cleanState())->toBe('raw');
});

it('exports the cleaned data once cleaning has run', function () {
    Http::fake(['*' => Http::response(cleaningResponse([['How was it?' => 'GREAT']]))]);

    $owner  = makeUser();
    $survey = makeCollection($owner);

    submitEntry($survey, null, 'great  ');

    $this->actingAs($owner)->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]));

    $csv = $this->actingAs($owner)
        ->get(route('collections.export', ['locale' => 'en', 'collection' => $survey]))
        ->assertOk()
        ->streamedContent();

    expect($csv)->toContain('GREAT');
});

it('keeps an unpublished collection out of the marketplace', function () {
    $survey = makeCollection(makeUser(), ['status' => 'draft', 'price' => 10_000]);
    $buyer  = makeUser([], [], 100_000);

    $this->actingAs($buyer)
        ->get(route('marketplace.index', ['locale' => 'en']))
        ->assertDontSee($survey->title, false);

    $this->actingAs($buyer)
        ->post(route('marketplace.purchase', ['locale' => 'en', 'collection' => $survey]))
        ->assertNotFound();
});

it('refuses to sell a dataset to its own owner', function () {
    $owner  = makeUser([], [], 100_000);
    $survey = makeCollection($owner, ['status' => 'published', 'price' => 10_000]);

    $this->actingAs($owner)
        ->post(route('marketplace.purchase', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('error');

    expect(Purchase::where('collection_id', $survey->id)->count())->toBe(0);
});

it('refuses a purchase the buyer cannot afford', function () {
    $survey = makeCollection(makeUser(), ['status' => 'published', 'price' => 100_000]);
    $buyer  = makeUser([], [], 1_000);

    $this->actingAs($buyer)
        ->post(route('marketplace.purchase', ['locale' => 'en', 'collection' => $survey]))
        ->assertRedirect(route('wallet.index', ['locale' => 'en']));

    expect(Purchase::where('collection_id', $survey->id)->count())->toBe(0)
        ->and($buyer->fresh()->balance())->toBe(1_000);
});

it('will not sell the same dataset to a buyer twice', function () {
    $survey = makeCollection(makeUser(), ['status' => 'published', 'price' => 10_000]);
    $buyer  = makeUser([], [], 100_000);

    $this->actingAs($buyer)->post(route('marketplace.purchase', ['locale' => 'en', 'collection' => $survey]));
    $this->actingAs($buyer)
        ->post(route('marketplace.purchase', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('error');

    expect(Purchase::where('collection_id', $survey->id)->count())->toBe(1)
        ->and($buyer->fresh()->balance())->toBe(90_000);
});

it('publishes the sell-order FAQ on the settings page', function () {
    $this->actingAs(makeUser())
        ->get(route('settings', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('How do I sell my survey?', false)
        ->assertSee('end it, clean it, then sell it', false);
});
