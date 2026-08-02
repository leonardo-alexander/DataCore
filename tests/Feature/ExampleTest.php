<?php

test('the root path redirects to the default locale', function () {
    $this->get('/')->assertRedirect('/' . config('app.locale'));
});

test('guests reaching a locale root are sent to the login page', function () {
    $this->get('/en')->assertRedirect(route('login', ['locale' => 'en']));
});
