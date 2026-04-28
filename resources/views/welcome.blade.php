@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex">

    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <div class="flex-grow-1 p-4" style="margin-left: 250px; background: linear-gradient(135deg, #5b4bff, #2e1f8f); min-height: 100vh;">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 text-white">
            <div>
                <h5>Hi Nyoney,</h5>
                <h3>Welcome to DataCore!</h3>
            </div>

            <input type="text" class="form-control w-25" placeholder="Search...">
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Dataset</p>
                        <h4>56</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-muted mb-1">Active Contributor</p>
                        <h4>102</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-muted mb-1">Active Workflow</p>
                        <h4>3</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white border-0" style="background: linear-gradient(135deg, #7b6cff, #a18bff);">
                    <div class="card-body">
                        <p class="mb-1">Revenue</p>
                        <h4>Rp100.000</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart + Activity -->
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Balance</h6>
                        <p class="text-success">On track</p>

                        <!-- Dummy Chart -->
                        <div style="height:150px; background:#f5f5f5; border-radius:10px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Recent Activity</h6>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                Submitted Dataset
                                <span class="text-danger">-3</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                Purchased Dataset
                                <span class="text-success">+40</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                Training Dataset
                                <span class="text-success">+12</span>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="row g-3">

            <!-- Balance Card -->
            <div class="col-md-4">
                <div class="card text-white border-0" style="background: linear-gradient(135deg, #a18bff, #7b6cff);">
                    <div class="card-body">
                        <h6>Your Balance</h6>
                        <h3>10</h3>

                        <ul class="mt-3 small">
                            <li>Survey Reward +10</li>
                            <li>Purchase Dataset -40</li>
                            <li>Dataset Approval +40</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Active Surveys -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="mb-3">Active Surveys</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <img src="https://via.placeholder.com/300x150" class="card-img-top">
                                    <div class="card-body">
                                        <h6>Professional Gadget General Survey</h6>
                                        <p class="small text-muted">Reward 50 Point</p>
                                        <button class="btn btn-primary btn-sm">Ikuti Survei</button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <img src="https://via.placeholder.com/300x150" class="card-img-top">
                                    <div class="card-body">
                                        <h6>Professional Gadget General Survey</h6>
                                        <p class="small text-muted">Reward 50 Point</p>
                                        <button class="btn btn-primary btn-sm">Ikuti Survei</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection