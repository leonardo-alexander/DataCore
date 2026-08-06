<?php

use App\Models\Transaction;

it('credits the wallet once a top-up is confirmed', function () {
    $user = makeUser([], [], 0);

    $this->actingAs($user)
        ->post(route('wallet.topup', ['locale' => 'en']), ['amount' => 100_000, 'method' => 'QRIS'])
        ->assertRedirect(route('wallet.index', ['locale' => 'en']))
        ->assertSessionHas('topup_instruction');

    expect($user->fresh()->balance())->toBe(0);

    $this->actingAs($user)
        ->post(route('wallet.topup.confirm', ['locale' => 'en']))
        ->assertRedirect(route('wallet.index', ['locale' => 'en']))
        ->assertSessionHas('success');

    expect($user->fresh()->balance())->toBe(100_000)
        ->and(Transaction::where('user_id', $user->id)->where('type', 'topup')->count())->toBe(1);
});

it('will not confirm a top-up that was never started', function () {
    $user = makeUser([], [], 0);

    $this->actingAs($user)
        ->post(route('wallet.topup.confirm', ['locale' => 'en']))
        ->assertSessionHas('error');

    expect($user->fresh()->balance())->toBe(0);
});

it('will not credit the same pending top-up twice', function () {
    $user = makeUser([], [], 0);

    $this->actingAs($user)->post(route('wallet.topup', ['locale' => 'en']), ['amount' => 50_000, 'method' => 'QRIS']);
    $this->actingAs($user)->post(route('wallet.topup.confirm', ['locale' => 'en']));
    $this->actingAs($user)->post(route('wallet.topup.confirm', ['locale' => 'en']))->assertSessionHas('error');

    expect($user->fresh()->balance())->toBe(50_000);
});

it('rejects a top-up below the minimum', function () {
    $user = makeUser([], [], 0);

    $this->actingAs($user)
        ->post(route('wallet.topup', ['locale' => 'en']), [
            'amount' => (int) config('datacore.min_topup') - 1,
            'method' => 'QRIS',
        ])
        ->assertSessionHasErrors('amount');
});

it('cancels a pending top-up without crediting anything', function () {
    $user = makeUser([], [], 0);

    $this->actingAs($user)->post(route('wallet.topup', ['locale' => 'en']), ['amount' => 50_000, 'method' => 'QRIS']);
    $this->actingAs($user)->get(route('wallet.topup.cancel', ['locale' => 'en']));
    $this->actingAs($user)->post(route('wallet.topup.confirm', ['locale' => 'en']))->assertSessionHas('error');

    expect($user->fresh()->balance())->toBe(0);
});

it('debits the wallet on a withdrawal request', function () {
    $user = makeUser([], [], 200_000);

    $this->actingAs($user)
        ->post(route('wallet.withdraw', ['locale' => 'en']), [
            'amount'         => 100_000,
            'bank_name'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'Test User',
        ])
        ->assertSessionHas('success');

    expect($user->fresh()->balance())->toBe(100_000)
        ->and(Transaction::where('user_id', $user->id)->where('type', 'withdraw')->value('status'))->toBe('processing');
});

it('refuses a withdrawal larger than the balance', function () {
    $user = makeUser([], [], 60_000);

    $this->actingAs($user)
        ->post(route('wallet.withdraw', ['locale' => 'en']), [
            'amount'         => 100_000,
            'bank_name'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'Test User',
        ])
        ->assertSessionHasErrors('amount');

    expect($user->fresh()->balance())->toBe(60_000);
});

it('refuses a withdrawal below the minimum', function () {
    $user = makeUser([], [], 1_000_000);

    $this->actingAs($user)
        ->post(route('wallet.withdraw', ['locale' => 'en']), [
            'amount'         => (int) config('datacore.min_withdrawal') - 1,
            'bank_name'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'Test User',
        ])
        ->assertSessionHasErrors('amount');

    expect($user->fresh()->balance())->toBe(1_000_000);
});

it('credits the respondent the survey reward on submission', function () {
    $owner  = makeUser([], [], 100_000);
    $survey = makeCollection($owner, ['reward' => 2_500, 'respondent_target' => 10, 'reward_budget' => 26_250]);

    $respondent = makeUser([], [], 0);

    $this->actingAs($respondent)->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
        'answers' => [$survey->questions->first()->id => 'Done'],
    ])->assertSessionHasNoErrors();

    expect($respondent->fresh()->balance())->toBe(2_500)
        ->and(Transaction::where('user_id', $respondent->id)->where('type', 'reward')->count())->toBe(1);
});

it('keeps one respondent from claiming the same reward twice', function () {
    $survey = makeCollection(makeUser(), ['reward' => 2_500, 'respondent_target' => 10, 'reward_budget' => 26_250]);

    $respondent = makeUser([], [], 0);

    $this->actingAs($respondent)->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
        'answers' => [$survey->questions->first()->id => 'Done'],
    ]);

    $this->actingAs($respondent)
        ->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
            'answers' => [$survey->questions->first()->id => 'Again'],
        ])
        ->assertRedirect(route('surveys.index', ['locale' => 'en']));

    expect($respondent->fresh()->balance())->toBe(2_500)
        ->and($survey->entries()->count())->toBe(1);
});

it('does not let the owner fill their own survey', function () {
    $owner  = makeUser([], [], 0);
    $survey = makeCollection($owner, ['reward' => 2_500, 'respondent_target' => 10, 'reward_budget' => 26_250]);

    $this->actingAs($owner)
        ->get(route('entries.create', ['locale' => 'en', 'collection' => $survey]))
        ->assertRedirect(route('surveys.index', ['locale' => 'en']));

    expect($owner->fresh()->balance())->toBe(0);
});

it('stops collecting once the respondent target is reached', function () {
    $survey = makeCollection(makeUser(), ['reward' => 1_000, 'respondent_target' => 1, 'reward_budget' => 1_050]);

    $this->actingAs(makeUser())->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
        'answers' => [$survey->questions->first()->id => 'First'],
    ]);

    $this->actingAs(makeUser())
        ->get(route('entries.create', ['locale' => 'en', 'collection' => $survey]))
        ->assertRedirect(route('surveys.index', ['locale' => 'en']));

    expect($survey->entries()->count())->toBe(1);
});

it('sends an unverified respondent to verification before they can answer', function () {
    $survey = makeCollection(makeUser());

    $this->actingAs(makeUser([], [], 0, 'unverified'))
        ->get(route('entries.create', ['locale' => 'en', 'collection' => $survey]))
        ->assertRedirect(route('verification.index', ['locale' => 'en']));
});
