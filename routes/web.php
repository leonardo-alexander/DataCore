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
})->name('settings.profile');
Route::get('/settings/security', function () {
    return view('settings.security');
})->name('settings.security');
Route::get('/settings/activity', function () {
    return view('settings.activity');
})->name('settings.activity');
