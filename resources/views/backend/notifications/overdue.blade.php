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
                    <li class="breadcrumb-item"><a href="{{ route('notifications.index') }}">Notifications</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Overdue Alerts</li>
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
                        <h5 class="mb-0">Overdue Delivery Alerts</h5>
                        <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back"></i> All Notifications
                        </a>
                    </div>

                    <!-- Filter buttons -->
                    <div class="mt-3">
                        <div class="btn-group" role="group" aria-label="Filter overdue alerts">
                            <a href="{{ route('notifications.overdue') }}"
                               class="btn {{ $currentFilter == 'all' ? 'btn-warning' : 'btn-outline-warning' }}">
                                <i class="bx bx-time"></i> All Overdue
                                @if(isset($stats['all']) && $stats['all'] > 0)
                                    <span class="badge bg-light text-dark ms-1">{{ $stats['all'] }}</span>
                                @endif
                            </a>
                            <a href="{{ route('notifications.overdue', ['type' => 'carpet']) }}"
                               class="btn {{ $currentFilter == 'carpet' ? 'btn-warning' : 'btn-outline-warning' }}">
                                <i class="mdi mdi-layers-outline"></i> Carpets Only
                                @if(isset($stats['carpet']) && $stats['carpet'] > 0)
                                    <span class="badge bg-light text-dark ms-1">{{ $stats['carpet'] }}</span>
                                @endif
                            </a>
                            <a href="{{ route('notifications.overdue', ['type' => 'laundry']) }}"
                               class="btn {{ $currentFilter == 'laundry' ? 'btn-warning' : 'btn-outline-warning' }}">
                                <i class="fa-solid fa-shirt"></i> Laundry Only
                                @if(isset($stats['laundry']) && $stats['laundry'] > 0)
                                    <span class="badge bg-light text-dark ms-1">{{ $stats['laundry'] }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($notifications->count() > 0)
                        <div class="alert alert-warning">
                            <i class="bx bx-error-circle me-2"></i>
                            <strong>{{ $notifications->total() }}</strong> overdue delivery alert(s) found. Please review and take action.
                        </div>
                    @endif

                    @forelse($notifications as $notification)
                        @php
                            $data = $notification->data;
                        @endphp
                        <div class="notification-item border rounded p-3 mb-3 {{ $notification->read_at ? 'bg-light' : 'bg-warning-subtle' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="notification-icon me-3">
                                            @if($data['service_type'] === 'carpet')
                                                <i class="mdi mdi-layers-outline text-warning fs-2"></i>
                                            @elseif($data['service_type'] === 'laundry')
                                                <i class="fa-solid fa-shirt text-warning fs-2"></i>
                                            @else
                                                <i class="bx bx-time-five text-warning fs-2"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">
                                                {{ ucfirst($data['service_type']) }} Service Overdue
                                                <span class="badge bg-{{ $data['service_type'] === 'carpet' ? 'info' : 'success' }} ms-2">
                                                    {{ ucfirst($data['service_type']) }}
                                                </span>
                                                @if(!$notification->read_at)
                                                    <span class="badge bg-warning ms-2">New Alert</span>
                                                @endif
                                            </h6>
                                            <p class="mb-1">
                                                <strong>Service ID:</strong> {{ $data['service_uniqueid'] }} <br>
                                                <strong>Customer:</strong> {{ $data['customer_phone'] }} <br>
                                                <strong>Location:</strong> {{ $data['location'] }} <br>
                                                <strong>Days Overdue:</strong> <span class="text-danger fw-bold">{{ $data['days_overdue'] }} days</span>
                                            </p>
                                            @if(isset($data['expected_date']))
                                                <p class="mb-1">
                                                    <strong>Expected Delivery:</strong>
                                                    <span class="text-muted">{{ \Carbon\Carbon::parse($data['expected_date'])->format('M d, Y') }}</span>
                                                </p>
                                            @endif
                                            <small class="text-muted">
                                                Alert sent: {{ $notification->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ $data['action_url'] }}" class="btn btn-primary">
                                                <i class="bx bx-show"></i> View Details
                                            </a>
                                            @if(!$notification->read_at)
                                                <button class="btn btn-success"
                                                        onclick="markAsRead('{{ $notification->id }}')"
                                                        title="Mark as read">
                                                    <i class="bx bx-check"></i> Mark as Read
                                                </button>
                                            @endif

                                            @if(Auth::user()->can('mpesa.compare'))
                                            <button class="btn btn-outline-danger"
                                                    onclick="deleteNotification('{{ $notification->id }}')"
                                                    title="Delete alert">
                                                <i class="bx bx-trash"></i> Delete Alert
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <div class="badge bg-{{ $data['days_overdue'] > 7 ? 'danger' : ($data['days_overdue'] > 3 ? 'warning' : 'info') }} fs-6 p-2">
                                        {{ $data['days_overdue'] }}<br>
                                        <small>Days Late</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bx bx-check-circle fs-1 text-success"></i>
                            @if($currentFilter !== 'all')
                                <h5 class="mt-3 text-success">No {{ ucfirst($currentFilter) }} Overdue Deliveries!</h5>
                                <p class="text-muted">All {{ $currentFilter }} deliveries are on track. Great job!</p>
                                <a href="{{ route('notifications.overdue') }}" class="btn btn-warning">
                                    <i class="bx bx-time"></i> View All Overdue
                                </a>
                            @else
                                <h5 class="mt-3 text-success">No Overdue Deliveries!</h5>
                                <p class="text-muted">All deliveries are on track. Great job!</p>
                                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                    <i class="bx bx-home"></i> Back to Dashboard
                                </a>
                            @endif
                        </div>
                    @endforelse

                    <!-- Pagination -->
                    @if($notifications->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $notifications->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notification-item {
    transition: all 0.2s ease;
}

.notification-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
    border-left: 4px solid #ffc107;
}
</style>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>

@endsection
