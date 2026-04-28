<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'index'])->name('login.view');
Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::get('/register', [RegisterController::class, 'index'])->name('register.view');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::get('/settings/profile', function () {
    return view('settings.profile');
})->name('profile.view');
Route::get('/settings/security', function () {
    return view('settings.security');
})->name('security.view');
Route::get('/settings/activity', function () {
    return view('settings.activity');
})->name('activity.view');

Route::get('/choose-survey', function () {
    return view('survey.choose-survey');
});

Route::get('/fill-survey', function () {
    return view('survey.fill-survey');
});

Route::get('/history-survey', function () {
    return view('survey.history-survey');
});


