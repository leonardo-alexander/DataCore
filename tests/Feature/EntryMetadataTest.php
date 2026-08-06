<?php

use App\Models\Collection;
use App\Models\Entry;

it('attaches the metadata a survey asked for to the entry', function () {
    $owner  = makeUser();
    $survey = makeCollection($owner, [
        'metadata_fields' => ['age', 'profession', 'marital_status'],
    ]);

    $respondent = makeUser([], [
        'dob'            => now()->subYears(29)->toDateString(),
        'profession'     => 'Data Analyst',
        'marital_status' => 'Single',
        'city'           => 'Bandung',
    ]);

    $this->actingAs($respondent)
        ->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
            'answers' => [$survey->questions->first()->id => 'It was fine'],
        ])
        ->assertRedirect(route('dashboard', ['locale' => 'en']));

    $entry = Entry::where('collection_id', $survey->id)->firstOrFail();

    expect($entry->metadata)->toBe([
        'age'            => 29,
        'profession'     => 'Data Analyst',
        'marital_status' => 'Single',
    ]);
});

it('stores no metadata when the survey asked for none', function () {
    $survey = makeCollection(makeUser(), ['metadata_fields' => []]);

    $respondent = makeUser([], ['dob' => now()->subYears(31)->toDateString(), 'city' => 'Jakarta']);

    $this->actingAs($respondent)->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
        'answers' => [$survey->questions->first()->id => 'Fine'],
    ]);

    expect(Entry::where('collection_id', $survey->id)->firstOrFail()->metadata)->toBe([]);
});

it('asks the respondent for requested metadata their profile cannot supply', function () {
    $survey = makeCollection(makeUser(), ['metadata_fields' => ['profession', 'marital_status']]);

    $respondent = makeUser(); // empty profile

    $this->actingAs($respondent)
        ->get(route('entries.create', ['locale' => 'en', 'collection' => $survey]))
        ->assertOk()
        ->assertSee('name="metadata[profession]"', false)
        ->assertSee('name="metadata[marital_status]"', false);
});

it('refuses an entry that leaves requested metadata blank', function () {
    $survey = makeCollection(makeUser(), ['metadata_fields' => ['profession']]);

    $this->actingAs(makeUser())
        ->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
            'answers' => [$survey->questions->first()->id => 'Fine'],
        ])
        ->assertSessionHasErrors('metadata.profession');

    expect(Entry::where('collection_id', $survey->id)->count())->toBe(0);
});

it('saves metadata typed on the survey form to the entry and the profile', function () {
    $survey = makeCollection(makeUser(), [
        'metadata_fields' => ['age', 'city', 'profession', 'marital_status', 'gender'],
    ]);

    $respondent = makeUser(); // empty profile: every field has to be asked for

    $this->actingAs($respondent)
        ->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
            'answers'  => [$survey->questions->first()->id => 'Fine'],
            'metadata' => [
                'age'            => now()->subYears(34)->toDateString(),
                'city'           => 'Surabaya',
                'profession'     => 'Nurse',
                'marital_status' => 'Married',
                'gender'         => 'Female',
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', ['locale' => 'en']));

    expect(Entry::where('collection_id', $survey->id)->firstOrFail()->metadata)->toBe([
        'age'            => 34,
        'gender'         => 'Female',
        'city'           => 'Surabaya',
        'profession'     => 'Nurse',
        'marital_status' => 'Married',
    ]);

    // Written back to the profile, so the next survey does not ask again.
    $profile = $respondent->profile->fresh();

    expect($profile->profession)->toBe('Nurse')
        ->and($profile->city)->toBe('Surabaya')
        ->and($profile->marital_status)->toBe('Married')
        ->and($profile->age())->toBe(34);
});

it('only asks for the fields that are actually missing', function () {
    $survey = makeCollection(makeUser(), ['metadata_fields' => ['city', 'profession']]);

    $respondent = makeUser([], ['city' => 'Medan']);

    $this->actingAs($respondent)
        ->get(route('entries.create', ['locale' => 'en', 'collection' => $survey]))
        ->assertOk()
        ->assertSee('name="metadata[profession]"', false)
        ->assertDontSee('name="metadata[city]"', false)
        ->assertSee('Medan', false);
});

it('rejects a marital status outside the accepted list', function () {
    $survey = makeCollection(makeUser(), ['metadata_fields' => ['marital_status']]);

    $this->actingAs(makeUser())
        ->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
            'answers'  => [$survey->questions->first()->id => 'Fine'],
            'metadata' => ['marital_status' => 'Complicated'],
        ])
        ->assertSessionHasErrors('metadata.marital_status');
});

it('rejects a date of birth under the minimum age', function () {
    $survey = makeCollection(makeUser(), ['metadata_fields' => ['age']]);

    $this->actingAs(makeUser())
        ->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
            'answers'  => [$survey->questions->first()->id => 'Fine'],
            'metadata' => ['age' => now()->subYears(12)->toDateString()],
        ])
        ->assertSessionHasErrors('metadata.age');
});

it('exports requested metadata alongside the answers', function () {
    $owner  = makeUser();
    $survey = makeCollection($owner, [
        'metadata_fields' => ['age', 'profession'],
    ], ['How was it?']);

    $respondent = makeUser([], [
        'dob'        => now()->subYears(41)->toDateString(),
        'profession' => 'Teacher',
    ]);

    $this->actingAs($respondent)->post(route('entries.store', ['locale' => 'en', 'collection' => $survey]), [
        'answers' => [$survey->questions->first()->id => 'Great'],
    ]);

    $csv = $this->actingAs($owner)
        ->get(route('collections.export', ['locale' => 'en', 'collection' => $survey]))
        ->assertOk()
        ->streamedContent();

    expect($csv)->toContain('How was it?')
        ->toContain(Collection::METADATA['age'])
        ->toContain(Collection::METADATA['profession'])
        ->toContain('Great')
        ->toContain('41')
        ->toContain('Teacher');
});
