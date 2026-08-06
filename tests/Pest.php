<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * A user with the three records registration always creates alongside them, so a
 * test does not have to reproduce AuthController::setUpNewAccount by hand.
 */
function makeUser(array $attributes = [], array $profile = [], int $balance = 0, string $verification = 'verified'): App\Models\User
{
    $user = App\Models\User::create(array_merge([
        'name'     => 'Test User',
        'email'    => 'user' . fake()->unique()->numberBetween(1, 999999) . '@example.test',
        'password' => 'secret-password',
    ], $attributes));

    App\Models\Profile::create(['user_id' => $user->id] + $profile);
    App\Models\Wallet::create(['user_id' => $user->id, 'balance' => $balance]);
    App\Models\Verification::create(['user_id' => $user->id, 'status' => $verification]);

    return $user->fresh();
}

/**
 * A survey owned by $owner, with one short-text question unless told otherwise.
 */
function makeCollection(App\Models\User $owner, array $attributes = [], array $questions = ['How was it?']): App\Models\Collection
{
    $collection = $owner->collections()->create(array_merge([
        'title'  => 'Survey ' . fake()->unique()->numberBetween(1, 999999),
        'type'   => 'survey',
        'status' => 'ongoing',
        'price'  => 0,
        'reward' => 0,
    ], $attributes));

    foreach (array_values($questions) as $i => $title) {
        App\Models\Question::create([
            'collection_id' => $collection->id,
            'title'         => $title,
            'type'          => 'text',
            'position'      => $i,
        ]);
    }

    return $collection->fresh();
}
