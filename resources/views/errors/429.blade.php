@extends('errors.layout')

@section('code', '429')
@section('title', __('Slow down for a moment'))
@section('message', __('You sent too many requests in a short time. Wait a minute and try again.'))
