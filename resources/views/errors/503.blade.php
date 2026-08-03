@extends('errors.layout')

@section('code', '503')
@section('icon', 'wrench')
@section('label', __('Maintenance'))
@section('title', __('DataCore is briefly offline'))
@section('message', __('We are deploying an update. This usually takes less than a minute, so please try again shortly.'))

@section('action')
    @include('errors._action', [
        'url' => url()->current(),
        'label' => __('Try again'),
        'icon' => 'rotate-cw',
    ])
@endsection
