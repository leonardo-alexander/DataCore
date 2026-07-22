@extends('layouts.app')

@section('title', 'Admin Dashboard - DataCore')

@section('content')

    <div class="d-flex">

        @include('partials.sidebar')

        <!-- MAIN CONTENT -->
        <div class="flex-grow-1 p-4"
            style="
            margin-left: 250px;
            min-height: 100vh;
            background: #f0f2f6;
        ">

            <!-- TOP BAR -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h4 class="fw-bold mb-0">👋 Welcome back, Admin</h4>
                    <small class="text-muted">Manage user approvals and monitor platform activity</small>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <input type="text" class="form-control rounded-pill px-4" placeholder="Search users..."
                        style="width: 250px; border: 2px solid #e0e0e0;">
                    <div class="rounded-circle bg-white p-2 shadow-sm"
                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="bi bi-bell text-primary"></i>
                    </div>
                    <div class="rounded-circle bg-white p-2 shadow-sm"
                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="bi bi-person-circle text-secondary" style="font-size: 24px;"></i>
                    </div>
                </div>

            </div>

            <!-- STAT CARDS -->
            <div class="row g-4 mb-4">

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted">Total Users</small>
                                <h3 class="fw-bold mb-0">1,248</h3>
                                <small class="text-success"><i class="bi bi-arrow-up"></i> +12%</small>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-people text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted">Pending Requests</small>
                                <h3 class="fw-bold mb-0 text-warning">18</h3>
                                <small class="text-danger"><i class="bi bi-arrow-up"></i> +5</small>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="bi bi-clock-history text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted">Approved Users</small>
                                <h3 class="fw-bold mb-0 text-success">1,102</h3>
                                <small class="text-success"><i class="bi bi-arrow-up"></i> +8%</small>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted">Active Surveys</small>
                                <h3 class="fw-bold mb-0">47</h3>
                                <small class="text-primary"><i class="bi bi-arrow-up"></i> +3</small>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="bi bi-file-earmark-text text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- CHART & RECENT ACTIVITY -->
            <div class="row g-4 mb-4">

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h5 class="fw-bold mb-3">User Registration Trend</h5>
                        <div
                            style="height: 180px; background: #f8f9fa; border-radius: 16px; display: flex; align-items: flex-end; padding: 20px;">
                            <div class="d-flex gap-2 w-100" style="height: 100px; align-items: flex-end;">
                                <div style="width: 10%; height: 40px; background: #4f46e5; border-radius: 8px 8px 0 0;">
                                </div>
                                <div style="width: 10%; height: 60px; background: #6366f1; border-radius: 8px 8px 0 0;">
                                </div>
                                <div style="width: 10%; height: 45px; background: #818cf8; border-radius: 8px 8px 0 0;">
                                </div>
                                <div style="width: 10%; height: 75px; background: #a5b4fc; border-radius: 8px 8px 0 0;">
                                </div>
                                <div style="width: 10%; height: 55px; background: #4f46e5; border-radius: 8px 8px 0 0;">
                                </div>
                                <div style="width: 10%; height: 90px; background: #6366f1; border-radius: 8px 8px 0 0;">
                                </div>
                                <div style="width: 10%; height: 70px; background: #818cf8; border-radius: 8px 8px 0 0;">
                                </div>
                                <div style="width: 10%; height: 100px; background: #a5b4fc; border-radius: 8px 8px 0 0;">
                                </div>
                                <div style="width: 10%; height: 80px; background: #4f46e5; border-radius: 8px 8px 0 0;">
                                </div>
                                <div style="width: 10%; height: 95px; background: #6366f1; border-radius: 8px 8px 0 0;">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 text-muted small">
                            <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span>
                            <span>May</span><span>Jun</span><span>Jul</span><span>Aug</span>
                            <span>Sep</span><span>Oct</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                        <h5 class="fw-bold mb-3">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary rounded-pill py-2"
                                onclick="alert('Navigate to pending approvals')">
                                <i class="bi bi-clock-history me-2"></i> Review Pending (18)
                            </button>
                            <button class="btn btn-outline-secondary rounded-pill py-2" onclick="alert('Export user list')">
                                <i class="bi bi-download me-2"></i> Export Users
                            </button>
                            <button class="btn btn-outline-success rounded-pill py-2" onclick="alert('Send bulk approval')">
                                <i class="bi bi-send me-2"></i> Bulk Approve
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- USER TABLE -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">User Management</h5>
                    <div>
                        <span class="badge bg-primary rounded-pill px-3 py-2">Total: 1,248</span>
                        <span class="badge bg-warning rounded-pill px-3 py-2 ms-1">Pending: 18</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ([['id' => 1, 'name' => 'Andi Pratama', 'email' => 'andi@example.com', 'role' => 'Contributor', 'status' => 'pending', 'joined' => '2024-12-01'], ['id' => 2, 'name' => 'Budi Santoso', 'email' => 'budi@example.com', 'role' => 'Researcher', 'status' => 'approved', 'joined' => '2024-11-15'], ['id' => 3, 'name' => 'Citra Dewi', 'email' => 'citra@example.com', 'role' => 'Student', 'status' => 'pending', 'joined' => '2024-12-10'], ['id' => 4, 'name' => 'Doni Salman', 'email' => 'doni@example.com', 'role' => 'Contributor', 'status' => 'rejected', 'joined' => '2024-10-20'], ['id' => 5, 'name' => 'Eka Putri', 'email' => 'eka@example.com', 'role' => 'Researcher', 'status' => 'approved', 'joined' => '2024-09-05']] as $user)
                                <tr>
                                    <td>{{ $user['id'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-10 me-2"
                                                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                {{ strtoupper(substr($user['name'], 0, 2)) }}
                                            </div>
                                            {{ $user['name'] }}
                                        </div>
                                    </td>
                                    <td>{{ $user['email'] }}</td>
                                    <td><span class="badge bg-info bg-opacity-10 text-info">{{ $user['role'] }}</span></td>
                                    <td>
                                        @if ($user['status'] == 'approved')
                                            <span class="badge bg-success rounded-pill px-3">Approved</span>
                                        @elseif($user['status'] == 'pending')
                                            <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                                        @else
                                            <span class="badge bg-danger rounded-pill px-3">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($user['joined'])->format('d M Y') }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            @if ($user['status'] == 'pending')
                                                <button class="btn btn-sm btn-success rounded-pill px-3"
                                                    onclick="approveUser({{ $user['id'] }})">
                                                    <i class="bi bi-check-lg"></i> Accept
                                                </button>
                                                <button class="btn btn-sm btn-danger rounded-pill px-3"
                                                    onclick="rejectUser({{ $user['id'] }})">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                                    onclick="viewUser({{ $user['id'] }})">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination placeholder -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Showing 5 of 1,248 users</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>

            </div>

        </div>

    </div>

    <!-- STYLE KUSTOM -->
    <style>
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.10) !important;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn-sm {
            font-size: 0.75rem;
        }
    </style>

    <!-- SCRIPT DUMMY (untuk simulasi aksi) -->
    <script>
        function approveUser(id) {
            if (confirm('Yakin ingin menyetujui user ID ' + id + '?')) {
                alert('User ' + id + ' berhasil di-approve! (simulasi)');
            }
        }

        function rejectUser(id) {
            if (confirm('Yakin ingin menolak user ID ' + id + '?')) {
                alert('User ' + id + ' ditolak. (simulasi)');
            }
        }

        function viewUser(id) {
            alert('Menampilkan detail user ID ' + id + ' (simulasi)');
        }
    </script>

@endsection
