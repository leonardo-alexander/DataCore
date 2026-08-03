@extends('errors.layout')

@section('code', '403')
@section('icon', 'lock')
@section('label', __('Forbidden'))
@section('title', __('You do not have access to this'))
@section('message', __('This dataset or page belongs to someone else. If you think this is a mistake, check that you are signed in with the right account.'))

@section('action')
    @include('errors._action', [
        'url' => rescue(fn() => route('login', ['locale' => app()->getLocale()]), url('/'), false),
        'label' => __('Switch account'),
        'icon' => 'user-round',
    ])
@endsection
