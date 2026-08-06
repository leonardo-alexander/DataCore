<?php

use App\Models\Collection;

it('serves every guest page', function (string $url) {
    $this->get($url)->assertOk();
})->with([
    '/en/login',
    '/en/register',
    '/id/login',
    '/id/register',
]);

it('serves every signed-in page', function (string $name) {
    $this->actingAs(makeUser([], [], 100_000))
        ->get(route($name, ['locale' => 'en']))
        ->assertOk();
})->with([
    'dashboard',
    'surveys.index',
    'marketplace.index',
    'collections.index',
    'collections.create',
    'purchases.index',
    'transactions.index',
    'wallet.index',
    'profile.edit',
    'verification.index',
    'settings',
]);

it('serves the owner-only collection pages', function () {
    $owner  = makeUser();
    $survey = makeCollection($owner);

    $this->actingAs($owner)
        ->get(route('collections.edit', ['locale' => 'en', 'collection' => $survey]))
        ->assertOk();

    $this->actingAs($owner)
        ->get(route('collections.analytics', ['locale' => 'en', 'collection' => $survey]))
        ->assertOk();
});

it('keeps a stranger out of another user\'s collection pages', function () {
    $survey   = makeCollection(makeUser());
    $stranger = makeUser();

    $this->actingAs($stranger)
        ->get(route('collections.edit', ['locale' => 'en', 'collection' => $survey]))
        ->assertForbidden();

    $this->actingAs($stranger)
        ->get(route('collections.analytics', ['locale' => 'en', 'collection' => $survey]))
        ->assertForbidden();
});

it('shows a published dataset in the marketplace', function () {
    $survey = makeCollection(makeUser(), ['status' => 'published', 'price' => 25_000]);

    $this->actingAs(makeUser())
        ->get(route('marketplace.show', ['locale' => 'en', 'collection' => $survey]))
        ->assertOk()
        ->assertSee($survey->title, false);
});

it('lists an ongoing survey for other users to fill', function () {
    $survey = makeCollection(makeUser(), ['reward' => 1_000, 'respondent_target' => 5]);

    $this->actingAs(makeUser())
        ->get(route('surveys.index', ['locale' => 'en']))
        ->assertOk()
        ->assertSee($survey->title, false);
});

it('keeps a non-admin out of the admin console', function () {
    $this->actingAs(makeUser())
        ->get(route('admin.dashboard', ['locale' => 'en']))
        ->assertForbidden();
});

it('lets an admin into the admin console', function () {
    $this->actingAs(makeUser(['is_admin' => true]))
        ->get(route('admin.dashboard', ['locale' => 'en']))
        ->assertOk();
});

it('switches locale and keeps the user on the same page', function () {
    $user = makeUser();

    $this->actingAs($user)
        ->put(route('language.update', ['locale' => 'en']), ['locale' => 'id'])
        ->assertRedirect();

    expect(session('locale'))->toBe('id');
});

it('creates a collection with the questions and metadata it was given', function () {
    $user = makeUser([], [], 500_000);

    $this->actingAs($user)
        ->post(route('collections.store', ['locale' => 'en']), [
            'title'           => 'Coffee habits',
            'description'     => 'A short survey.',
            'price'           => 0,
            'status'          => 'draft',
            'metadata_fields' => ['age', 'profession'],
            'questions'       => [
                ['text' => 'How often?', 'type' => 'choice', 'options' => ['Daily', 'Weekly'], 'required' => '1'],
                ['text' => 'Why?', 'type' => 'paragraph'],
            ],
        ])
        ->assertSessionHasNoErrors();

    $collection = Collection::where('title', 'Coffee habits')->firstOrFail();

    expect($collection->metadata_fields)->toBe(['age', 'profession'])
        ->and($collection->questions)->toHaveCount(2)
        ->and($collection->questions->first()->options)->toBe(['Daily', 'Weekly'])
        ->and($collection->questions->first()->required)->toBeTruthy()
        ->and($collection->status)->toBe('draft');
});

it('refuses a collection metadata field that is not on the list', function () {
    $this->actingAs(makeUser())
        ->post(route('collections.store', ['locale' => 'en']), [
            'title'           => 'Bad metadata',
            'status'          => 'draft',
            'metadata_fields' => ['salary'],
            'questions'       => [['text' => 'Q', 'type' => 'text']],
        ])
        ->assertSessionHasErrors('metadata_fields.0');
});

it('will not launch a survey whose reward pool exceeds the balance', function () {
    $this->actingAs(makeUser([], [], 1_000))
        ->post(route('collections.store', ['locale' => 'en']), [
            'title'             => 'Too expensive',
            'status'            => 'ongoing',
            'reward'            => 10_000,
            'respondent_target' => 100,
            'questions'         => [['text' => 'Q', 'type' => 'text']],
        ])
        ->assertSessionHasErrors('reward');

    expect(Collection::where('title', 'Too expensive')->exists())->toBeFalse();
});

it('escrows the reward pool when a survey launches', function () {
    $user = makeUser([], [], 500_000);

    $this->actingAs($user)
        ->post(route('collections.store', ['locale' => 'en']), [
            'title'             => 'Funded survey',
            'status'            => 'ongoing',
            'reward'            => 1_000,
            'respondent_target' => 10,
            'questions'         => [['text' => 'Q', 'type' => 'text']],
        ])
        ->assertSessionHasNoErrors();

    // 10 x 1,000 plus the 5% platform fee.
    expect($user->fresh()->balance())->toBe(500_000 - 10_500)
        ->and(Collection::where('title', 'Funded survey')->value('reward_budget'))->toBe(10_500);
});
