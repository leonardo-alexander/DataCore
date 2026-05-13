@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex">

    @include('components.sidebar')

    <!-- Main Content -->
    <div class="flex-grow-1 p-4 text-white"
        style="
            margin-left: 250px;
            min-height: 100vh;
            background: linear-gradient(to bottom, #4536C5, #584FA7, #211A5F);
        ">

        <!-- TOP -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <small class="opacity-75">Hi Nyong,</small>
                <h3 class="fw-bold mb-0">Welcome to DataCore!</h3>
            </div>

            <input type="text"
                class="form-control rounded-pill px-3"
                placeholder="Search"
                style="width: 250px;">
        </div>

        <!-- STAT CARDS -->
        <div class="row g-3 mb-4">

            @foreach([
                ['title' => 'Total Data Set', 'value' => '56'],
                ['title' => 'Active Contributor', 'value' => '102'],
                ['title' => 'Active Workflow', 'value' => '3'],
                ['title' => 'Revenue', 'value' => 'Rp100.000'],
            ] as $item)

            <div class="col-md-3">
                <div class="p-3 rounded-4 h-100 stat-card">
                    <small class="text-muted">{{ $item['title'] }}</small>
                    <h5 class="fw-bold mb-0 text-dark">{{ $item['value'] }}</h5>
                </div>
            </div>

            @endforeach

        </div>

        <!-- ROW 2 -->
        <div class="row g-3 mb-4">

            <!-- Balance -->
            <div class="col-md-8">
                <div class="p-4 rounded-4 stat-card h-100">

                    <div class="d-flex justify-content-between">
                        <h5 class="mb-0 text-dark">Balance</h5>
                        <span class="badge bg-success">On track</span>
                    </div>

                    <div class="row mt-3">
                        <div class="col">
                            <small class="text-muted">Credits Earned</small>
                            <h4 class="fw-bold text-dark">230</h4>
                        </div>
                        <div class="col">
                            <small class="text-muted">Credit Used</small>
                            <h4 class="fw-bold text-dark">220</h4>
                        </div>
                    </div>

                    <!-- fake chart -->
                    <div class="mt-4" style="height: 80px; background: #eee; border-radius: 10px;"></div>

                </div>
            </div>

            <!-- Activity -->
            <div class="col-md-4">
                <div class="p-4 rounded-4 stat-card h-100">

                    <h5 class="text-dark">Recent Activity</h5>

                    <div class="mt-3 small">

                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <strong class="text-dark">Submitted Dataset</strong><br>
                                <span class="text-muted">Today</span>
                            </div>
                            <span class="text-success">+2</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <strong class="text-dark">Purchased Dataset</strong><br>
                                <span class="text-muted">Today</span>
                            </div>
                            <span class="text-danger">-2</span>
                        </div>

                    </div>

                </div>
            </div>

        </div>

        <!-- ROW 3 -->
        <div class="row g-3">

            <div class="col-md-4">
                <div class="p-4 rounded-4 stat-card h-100">
                    <small class="text-muted">Credit Balance</small>
                    <h3 class="fw-bold text-dark">10</h3>
                </div>
            </div>

            <div class="col-md-8">
                <div class="p-4 rounded-4 stat-card h-100">

                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="text-dark">Active Surveys</h5>
                        <small class="text-muted">230 Credits</small>
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <img src="{{ asset('images/datacore-logo.png') }}" class="card-img-top">
                                <div class="card-body">
                                    <h6>Survey Gadget</h6>
                                    <button class="btn btn-primary btn-sm">Klik</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <img src="{{ asset('images/datacore-logo.png') }}" class="card-img-top">
                                <div class="card-body">
                                    <h6>Survey Gadget</h6>
                                    <button class="btn btn-primary btn-sm">Klik</button>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>

    </div>
</div>

<!-- STYLE -->
<style>
.stat-card {
    background: #ffffff; 
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    transition: 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}
</style>

@endsection