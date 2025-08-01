@extends('admin_master')
@section('admin')

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">System</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('audit.index') }}">Audit Trail</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Statistics</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Audit Trail Statistics</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" onchange="changePeriod(this.value)" style="width: auto;">
                                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                            </select>
                            <a href="{{ route('audit.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bx bx-arrow-back"></i> Back to Audit Trail
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card gradient-deepblue">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <p class="mb-0 text-white">Total Activities</p>
                                            <h4 class="my-1 text-white">{{ number_format($stats['total_activities']) }}</h4>
                                            <p class="mb-0 font-13 text-white">Last {{ $days }} days</p>
                                        </div>
                                        <div class="widgets-icons bg-white text-primary ms-auto">
                                            <i class="bx bx-file-find"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card gradient-orange">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <p class="mb-0 text-white">Active Users</p>
                                            <h4 class="my-1 text-white">{{ $stats['unique_users'] }}</h4>
                                            <p class="mb-0 font-13 text-white">Users with activity</p>
                                        </div>
                                        <div class="widgets-icons bg-white text-warning ms-auto">
                                            <i class="bx bx-user"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card gradient-ohhappiness">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <p class="mb-0 text-white">Avg Daily Activity</p>
                                            <h4 class="my-1 text-white">{{ number_format($stats['total_activities'] / $days, 1) }}</h4>
                                            <p class="mb-0 font-13 text-white">Actions per day</p>
                                        </div>
                                        <div class="widgets-icons bg-white text-success ms-auto">
                                            <i class="bx bx-trending-up"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card gradient-ibiza">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <p class="mb-0 text-white">Most Active Event</p>
                                            <h4 class="my-1 text-white">{{ ucfirst($stats['events_breakdown']->first()->event ?? 'None') }}</h4>
                                            <p class="mb-0 font-13 text-white">{{ $stats['events_breakdown']->first()->count ?? 0 }} times</p>
                                        </div>
                                        <div class="widgets-icons bg-white text-danger ms-auto">
                                            <i class="bx bx-bolt"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <!-- Events Breakdown -->
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Events Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="eventsChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Models Breakdown -->
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Models Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="modelsChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Activity Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Daily Activity Trend</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="dailyChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Users Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Top Active Users</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>User</th>
                                                    <th>Activities</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($stats['top_users'] as $index => $userStat)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            @if($userStat->user)
                                                                <strong>{{ $userStat->user->name }}</strong>
                                                                <br><small class="text-muted">{{ $userStat->user->email }}</small>
                                                            @else
                                                                <span class="text-muted">Unknown User</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary">{{ $userStat->count }}</span>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $percentage = $stats['total_activities'] > 0 ? ($userStat->count / $stats['total_activities']) * 100 : 0;
                                                            @endphp
                                                            <div class="progress" style="height: 6px;">
                                                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                                            </div>
                                                            <small>{{ number_format($percentage, 1) }}%</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Events Breakdown Chart
    const eventsCtx = document.getElementById('eventsChart').getContext('2d');
    new Chart(eventsCtx, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($stats['events_breakdown'] as $event)
                    '{{ ucfirst($event->event) }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($stats['events_breakdown'] as $event)
                        {{ $event->count }},
                    @endforeach
                ],
                backgroundColor: [
                    '#0d6efd', '#198754', '#dc3545', '#ffc107', '#6f42c1', '#fd7e14', '#20c997'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Models Breakdown Chart
    const modelsCtx = document.getElementById('modelsChart').getContext('2d');
    new Chart(modelsCtx, {
        type: 'bar',
        data: {
            labels: [
                @foreach($stats['models_breakdown'] as $model)
                    '{{ class_basename($model->auditable_type) }}',
                @endforeach
            ],
            datasets: [{
                label: 'Activities',
                data: [
                    @foreach($stats['models_breakdown'] as $model)
                        {{ $model->count }},
                    @endforeach
                ],
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Daily Activity Chart
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($stats['daily_activity'] as $day)
                    '{{ \Carbon\Carbon::parse($day->date)->format('M d') }}',
                @endforeach
            ],
            datasets: [{
                label: 'Daily Activities',
                data: [
                    @foreach($stats['daily_activity'] as $day)
                        {{ $day->count }},
                    @endforeach
                ],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});

function changePeriod(days) {
    window.location.href = "{{ route('audit.stats') }}?days=" + days;
}
</script>

@endsection