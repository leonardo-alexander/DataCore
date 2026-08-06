<?php

it('saves the demographics a survey can ask for', function () {
    $user = makeUser(['name' => 'Old Name', 'email' => 'profile@example.test']);

    $this->actingAs($user)
        ->put(route('profile.update', ['locale' => 'en']), [
            'name'           => 'New Name',
            'email'          => 'profile@example.test',
            'phone_number'   => '+62 812 0000 0000',
            'gender'         => 'Female',
            'dob'            => now()->subYears(28)->toDateString(),
            'address'        => 'Jl. Merdeka 1',
            'city'           => 'Yogyakarta',
            'profession'     => 'Researcher',
            'marital_status' => 'Married',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $profile = $user->fresh()->profile;

    expect($user->fresh()->name)->toBe('New Name')
        ->and($profile->city)->toBe('Yogyakarta')
        ->and($profile->profession)->toBe('Researcher')
        ->and($profile->marital_status)->toBe('Married')
        ->and($profile->gender)->toBe('Female')
        ->and($profile->age())->toBe(28);
});

it('exposes every metadata field a survey can request on the profile form', function () {
    $this->actingAs(makeUser())
        ->get(route('profile.edit', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('name="city"', false)
        ->assertSee('name="profession"', false)
        ->assertSee('name="marital_status"', false)
        ->assertSee('name="gender"', false)
        ->assertSee('name="dob"', false);
});

it('rejects a marital status that is not on the list', function () {
    $user = makeUser(['email' => 'reject@example.test']);

    $this->actingAs($user)
        ->put(route('profile.update', ['locale' => 'en']), [
            'name'           => 'Test User',
            'email'          => 'reject@example.test',
            'marital_status' => 'Something else',
        ])
        ->assertSessionHasErrors('marital_status');

    expect($user->fresh()->profile->marital_status)->toBeNull();
});

it('rejects a date of birth under the minimum age', function () {
    $user = makeUser(['email' => 'young@example.test']);

    $this->actingAs($user)
        ->put(route('profile.update', ['locale' => 'en']), [
            'name'  => 'Test User',
            'email' => 'young@example.test',
            'dob'   => now()->subYears(10)->toDateString(),
        ])
        ->assertSessionHasErrors('dob');
});

it('will not let a profile take an email already in use', function () {
    makeUser(['email' => 'taken@example.test']);
    $user = makeUser(['email' => 'mine@example.test']);

    $this->actingAs($user)
        ->put(route('profile.update', ['locale' => 'en']), [
            'name'  => 'Test User',
            'email' => 'taken@example.test',
        ])
        ->assertSessionHasErrors('email');

    expect($user->fresh()->email)->toBe('mine@example.test');
});

it('clears a demographic field when it is submitted empty', function () {
    $user = makeUser(['email' => 'clear@example.test'], ['profession' => 'Dentist']);

    $this->actingAs($user)
        ->put(route('profile.update', ['locale' => 'en']), [
            'name'       => 'Test User',
            'email'      => 'clear@example.test',
            'profession' => '',
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->profile->profession)->toBeNull();
});
