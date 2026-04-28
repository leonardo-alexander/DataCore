@extends('layouts.app')

@section('title', 'Fill Survey')

@section('content')
<div class="d-flex">

    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <div class="flex-grow-1 p-4"
         style="margin-left: 250px; min-height:100vh;
         background: linear-gradient(135deg, #5b4bff, #2e1f8f);">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 text-white">
            <h2 class="fw-bold">Choose The Survey</h2>

            <input type="text" class="form-control w-25 rounded-pill"
                   placeholder="🔍 Search">
        </div>

        <!-- Filter -->
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <button class="btn btn-light rounded-pill px-3">All</button>
            <button class="btn btn-light rounded-pill px-3">Health</button>
            <button class="btn btn-light rounded-pill px-3">Education</button>
            <button class="btn btn-light rounded-pill px-3">Technology</button>
            <button class="btn btn-light rounded-pill px-3">Economy & Business</button>
            <button class="btn btn-light rounded-pill px-3">Environment</button>
        </div>

        <!-- Cards -->
        <div class="row g-4">

            @for ($i = 0; $i < 8; $i++)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                    <!-- Image -->
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71"
                         class="card-img-top" style="height:180px; object-fit:cover;">

                    <!-- Content -->
                    <div class="card-body">

                        <h6 class="fw-semibold mb-2">
                            Effects of Sleep Deprivation
                        </h6>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary rounded-pill px-3">
                                Health
                            </span>

                            <span class="badge bg-success-subtle text-success rounded-pill px-3">
                                +2
                            </span>
                        </div>

                        <button class="btn w-100 text-white rounded-pill"
                                style="background: linear-gradient(135deg, #7b6cff, #5b4bff);">
                            Join
                        </button>

                    </div>
                </div>
            </div>
            @endfor

        </div>

    </div>
</div>
@endsection