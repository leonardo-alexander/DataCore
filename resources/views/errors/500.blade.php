@extends('errors.layout')

@section('code', '500')
@section('icon', 'server-crash')
@section('label', __('Server error'))
@section('title', __('Something broke on our side'))
@section('message', __('An unexpected error stopped this request. Nothing you did caused it. Try again in a moment.'))

@section('action')
    @include('errors._action', [
        'url' => url()->current(),
        'label' => __('Try again'),
        'icon' => 'rotate-cw',
    ])
@endsection
