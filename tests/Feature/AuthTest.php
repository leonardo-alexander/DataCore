<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('registers a new account and lands on the dashboard', function () {
    $response = $this->post('/en/register', [
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ada@example.test',
        'password'              => 'secret-password',
        'password_confirmation' => 'secret-password',
        'terms'                 => 'on',
    ]);

    $response->assertRedirect(route('dashboard', ['locale' => 'en']));
    $this->assertAuthenticated();

    $user = User::where('email', 'ada@example.test')->firstOrFail();

    expect($user->profile)->not->toBeNull()
        ->and($user->wallet)->not->toBeNull()
        ->and($user->verification)->not->toBeNull()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

it('refuses registration without accepting the terms', function () {
    $this->from('/en/register')->post('/en/register', [
        'name'                  => 'No Terms',
        'email'                 => 'noterms@example.test',
        'password'              => 'secret-password',
        'password_confirmation' => 'secret-password',
    ])->assertSessionHasErrors('terms');

    $this->assertGuest();
});

it('signs in with valid credentials', function () {
    $user = makeUser(['email' => 'signin@example.test']);

    $this->post('/en/login', [
        'email'    => 'signin@example.test',
        'password' => 'secret-password',
    ])->assertRedirect(route('dashboard', ['locale' => 'en']));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials with a message the login page can show', function () {
    makeUser(['email' => 'bad@example.test']);

    $this->from('/en/login')->post('/en/login', [
        'email'    => 'bad@example.test',
        'password' => 'wrong-password',
    ])
        ->assertRedirect('/en/login')
        ->assertSessionHas('error');

    $this->assertGuest();

    // The guest layout has to render flashed messages, or this is invisible.
    $this->followingRedirects()
        ->from('/en/login')
        ->post('/en/login', ['email' => 'bad@example.test', 'password' => 'wrong-password'])
        ->assertSee('do not match our records', false);
});

it('signs back in after logging out', function () {
    $user = makeUser(['email' => 'cycle@example.test']);

    $this->post('/en/login', [
        'email'    => 'cycle@example.test',
        'password' => 'secret-password',
    ])->assertRedirect(route('dashboard', ['locale' => 'en']));
    $this->assertAuthenticatedAs($user);

    $this->post('/en/logout')->assertRedirect(route('login', ['locale' => 'en']));
    $this->assertGuest();

    $this->get('/en/login')->assertOk();

    $this->post('/en/login', [
        'email'    => 'cycle@example.test',
        'password' => 'secret-password',
    ])->assertRedirect(route('dashboard', ['locale' => 'en']));

    $this->assertAuthenticatedAs($user);
    $this->get('/en/dashboard')->assertOk();
});

it('tells the browser never to cache a page, guest pages included', function () {
    // Without no-store the browser restores the login form from the back/forward
    // cache after a logout, and signing in fails on a CSRF token from the session
    // that has since been thrown away.
    foreach (['/en/login', '/en/register'] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private');
    }

    $this->actingAs(makeUser())->get('/en/dashboard')
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private');
});

it('shows the sign-out confirmation on the login page', function () {
    $this->actingAs(makeUser())
        ->followingRedirects()
        ->post('/en/logout')
        ->assertOk()
        ->assertSee('You have been signed out', false);
});

it('sends a signed-in visitor away from the login page to a locale-aware dashboard', function () {
    $this->actingAs(makeUser())->get('/en/login')
        ->assertRedirect(route('dashboard', ['locale' => 'en']));
});

it('redirects a guest to the login page for protected routes', function () {
    $this->get('/en/dashboard')->assertRedirect(route('login', ['locale' => 'en']));
});

it('returns the guest to the page they wanted after signing in', function () {
    makeUser(['email' => 'intended@example.test']);

    $this->get('/en/wallet')->assertRedirect(route('login', ['locale' => 'en']));

    $this->post('/en/login', [
        'email'    => 'intended@example.test',
        'password' => 'secret-password',
    ])->assertRedirect(route('wallet.index', ['locale' => 'en']));
});

it('does not carry a stale intended url across a logout', function () {
    makeUser(['email' => 'stale@example.test']);

    $this->get('/en/wallet')->assertRedirect(route('login', ['locale' => 'en']));
    $this->post('/en/login', ['email' => 'stale@example.test', 'password' => 'secret-password']);
    $this->post('/en/logout');
    $this->assertGuest();

    $this->post('/en/login', [
        'email'    => 'stale@example.test',
        'password' => 'secret-password',
    ])->assertRedirect(route('dashboard', ['locale' => 'en']));
});

it('keeps the chosen locale through the logout redirect', function () {
    makeUser(['email' => 'locale@example.test']);

    $this->post('/id/login', [
        'email'    => 'locale@example.test',
        'password' => 'secret-password',
    ])->assertRedirect(route('dashboard', ['locale' => 'id']));

    $this->post('/id/logout')->assertRedirect(route('login', ['locale' => 'id']));
});

it('always asks which Google account to use', function () {
    // Without prompt=select_account Google reuses the browser's current account
    // silently, which on a shared machine signs the next person into the last
    // person's session.
    $response = $this->get('/auth/google')->assertRedirect();

    parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

    expect($query['prompt'] ?? null)->toBe('select_account');
});

it('gives a signed-out visitor no access to another session', function () {
    $user = makeUser(['email' => 'session@example.test']);

    $this->actingAs($user)->post('/en/logout');

    $this->get('/en/collections')->assertRedirect(route('login', ['locale' => 'en']));
    $this->get('/en/wallet')->assertRedirect(route('login', ['locale' => 'en']));
    $this->get('/en/profile')->assertRedirect(route('login', ['locale' => 'en']));
});
