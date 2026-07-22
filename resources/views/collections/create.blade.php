@extends('layouts.app')

@section('title', 'New Collection')

@section('content')
    <div>
        <a href="{{ route('collections.index') }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-900">
            <i data-lucide="arrow-left" class="h-4 w-4"></i> Back to collections
        </a>
        <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight text-slate-900">New collection</h1>
        <p class="mt-1 text-sm text-slate-500">Build a survey from scratch or import an existing CSV.</p>
    </div>

    @include('collections._form', [
        'action' => route('collections.store'),
        'method' => 'POST',
        'showImport' => true,
    ])
@endsection
