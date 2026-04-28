@extends('layouts.app')

@section('title', 'My History')

@section('content')
<div class="d-flex">
    @include('components.sidebar')

    <div class="flex-grow-1 p-4" style="margin-left: 250px; background: linear-gradient(135deg, #5b4bff, #7b6cff); min-height: 100vh;">
        
        <div class="container mt-3">
            <div class="d-flex justify-content-between align-items-center mb-4 text-white">
                <h2>My History</h2>
                <input type="text" class="form-control w-25 border-0" placeholder="Search...">
            </div>

            @foreach($histories as $history)
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 20px;">
                <div class="card-body d-flex align-items-center p-3">
                    <img src="{{ asset('img/dataset-icon.png') }}" class="me-3" style="width: 80px; height: 80px; border-radius: 10px; object-fit: cover;">
                    
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">From <strong>{{ $history->provider }}</strong></small>
                            <small class="text-muted">Order ID <strong>{{ $history->order_id }}</strong></small>
                            <small class="text-muted">Purchased <strong>{{ $history->date }}</strong></small>
                        </div>
                        <h6 class="mb-0">{{ $history->title }}</h6>
                        <span class="badge bg-light text-dark mt-1">{{ $history->points }}</span>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="mt-4">
                <a href="#" class="text-white text-decoration-none">← Back</a>
            </div>
        </div>
    </div>
</div>
@endsection