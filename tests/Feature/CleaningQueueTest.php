<?php

use App\Jobs\ProcessCleaning;
use App\Models\Entry;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

function fakeCleaningService(int $rows = 1): void
{
    Http::fake([
        '*' => Http::response([
            'data'   => array_fill(0, $rows, ['How was it?' => 'Great']),
            'report' => ['quality' => ['final_quality_score' => 0.9, 'avg_score' => 0.9]],
        ]),
    ]);
}

function seedEntries(App\Models\Collection $survey, int $count): void
{
    for ($i = 0; $i < $count; $i++) {
        Entry::create([
            'collection_id' => $survey->id,
            'user_id'       => null,
            'raw_data'      => ['How was it?' => 'Great'],
            'status'        => 'raw',
        ]);
    }
}

it('hands a large clean to the queue by default', function () {
    config(['datacore.cleaning_force_sync' => false, 'datacore.cleaning_sync_limit' => 3]);
    Bus::fake();

    $owner  = makeUser();
    $survey = makeCollection($owner);
    seedEntries($survey, 5);

    $this->actingAs($owner)
        ->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('success');

    Bus::assertDispatched(ProcessCleaning::class);
});

it('runs every clean inline when the demo switch is on', function () {
    config(['datacore.cleaning_force_sync' => true, 'datacore.cleaning_sync_limit' => 3]);
    Bus::fake();
    fakeCleaningService(5);

    $owner  = makeUser();
    $survey = makeCollection($owner);
    seedEntries($survey, 5);

    $this->actingAs($owner)
        ->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('success');

    // Nothing queued, and the result is already on the page rather than pending.
    Bus::assertNothingDispatched();

    expect($survey->fresh()->cleanState())->toBe('clean1')
        ->and(session('success'))->toContain('Clean 1 complete');
});

it('runs Clean 2 inline and charges the fee when the demo switch is on', function () {
    config(['datacore.cleaning_force_sync' => true, 'datacore.cleaning_sync_limit' => 3]);
    Bus::fake();
    fakeCleaningService(5);

    $fee    = (int) config('datacore.clean2_fee');
    $owner  = makeUser([], [], $fee);
    $survey = makeCollection($owner);
    seedEntries($survey, 5);

    $this->actingAs($owner)->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]));
    $this->actingAs($owner)
        ->post(route('collections.clean2', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('success');

    Bus::assertNothingDispatched();

    expect($survey->fresh()->cleanState())->toBe('clean2')
        ->and($owner->fresh()->balance())->toBe(0);
});

it('still reports a cleaning failure on the page in demo mode', function () {
    config(['datacore.cleaning_force_sync' => true, 'datacore.cleaning_sync_limit' => 3]);
    Http::fake(['*' => Http::response('waking up', 503)]);

    $owner  = makeUser();
    $survey = makeCollection($owner);
    seedEntries($survey, 5);

    $this->actingAs($owner)
        ->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('error');

    expect($survey->fresh()->cleanState())->toBe('raw');
});

it('releases the clean lock in demo mode so it can be run again', function () {
    config(['datacore.cleaning_force_sync' => true, 'datacore.cleaning_sync_limit' => 3]);
    fakeCleaningService(5);

    $owner  = makeUser();
    $survey = makeCollection($owner);
    seedEntries($survey, 5);

    $this->actingAs($owner)->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]));

    // A second run right away must not be refused by a lock left behind.
    $this->actingAs($owner)
        ->post(route('collections.clean1', ['locale' => 'en', 'collection' => $survey]))
        ->assertSessionHas('success');
});

it('decides where a clean runs from the entry count and the demo switch', function () {
    config(['datacore.cleaning_sync_limit' => 300]);

    config(['datacore.cleaning_force_sync' => false]);
    expect(ProcessCleaning::shouldQueue(1))->toBeFalse()
        ->and(ProcessCleaning::shouldQueue(300))->toBeFalse()
        ->and(ProcessCleaning::shouldQueue(301))->toBeTrue();

    config(['datacore.cleaning_force_sync' => true]);
    expect(ProcessCleaning::shouldQueue(1))->toBeFalse()
        ->and(ProcessCleaning::shouldQueue(100_000))->toBeFalse();
});
