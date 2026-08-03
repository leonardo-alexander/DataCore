@extends('errors.layout')

@section('code', '419')
@section('icon', 'timer-off')
@section('label', __('Page expired'))
@section('title', __('Your session expired'))
@section('message', __('You were away for a while and the page went stale. Go back and try again, and sign in once more if you are asked to.'))

@section('action')
    @include('errors._action', [
        'url' => rescue(fn() => route('login', ['locale' => app()->getLocale()]), url('/'), false),
        'label' => __('Sign in again'),
        'icon' => 'log-in',
    ])
@endsection
