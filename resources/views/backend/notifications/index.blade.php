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
                    <li class="breadcrumb-item active" aria-current="page">Notifications</li>
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
                        <h5 class="mb-0">All Notifications</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm" onclick="markAllAsRead()">
                                <i class="bx bx-check-double"></i> Mark All as Read
                            </button>
                            <a href="{{ route('notifications.overdue') }}" class="btn btn-warning btn-sm">
                                <i class="bx bx-time"></i> Overdue Alerts
                            </a>
                        </div>
                    </div>

                    <!-- Filter buttons -->
                    <div class="mt-3">
                        <div class="btn-group" role="group" aria-label="Filter notifications">
                            <a href="{{ route('notifications.index') }}"
                               class="btn {{ $currentFilter == 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                                <i class="bx bx-list-ul"></i> All
                                @if(isset($stats['all']) && $stats['all'] > 0)
                                    <span class="badge bg-light text-dark ms-1">{{ $stats['all'] }}</span>
                                @endif
                            </a>
                            <a href="{{ route('notifications.index', ['type' => 'carpet']) }}"
                               class="btn {{ $currentFilter == 'carpet' ? 'btn-primary' : 'btn-outline-primary' }}">
                                <i class="mdi mdi-layers-outline"></i> Carpets
                                @if(isset($stats['carpet']) && $stats['carpet'] > 0)
                                    <span class="badge bg-light text-dark ms-1">{{ $stats['carpet'] }}</span>
                                @endif
                            </a>
                            <a href="{{ route('notifications.index', ['type' => 'laundry']) }}"
                               class="btn {{ $currentFilter == 'laundry' ? 'btn-primary' : 'btn-outline-primary' }}">
                                <i class="fa-solid fa-shirt"></i> Laundry
                                @if(isset($stats['laundry']) && $stats['laundry'] > 0)
                                    <span class="badge bg-light text-dark ms-1">{{ $stats['laundry'] }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @forelse($notifications as $notification)
                        <div class="notification-item {{ $notification->read_at ? '' : 'unread' }} border-bottom py-3"
                             data-id="{{ $notification->id }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="notification-icon me-3">
                                            @if($notification->type === 'App\\Notifications\\OverdueDeliveryNotification')
                                                @if(isset($notification->data['service_type']) && $notification->data['service_type'] === 'carpet')
                                                    <i class="mdi mdi-layers-outline text-warning fs-4"></i>
                                                @elseif(isset($notification->data['service_type']) && $notification->data['service_type'] === 'laundry')
                                                    <i class="fa-solid fa-shirt text-warning fs-4"></i>
                                                @else
                                                    <i class="bx bx-time-five text-warning fs-4"></i>
                                                @endif
                                            @else
                                                <i class="bx bx-bell text-info fs-4"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">
                                                @if($notification->type === 'App\\Notifications\\OverdueDeliveryNotification')
                                                    @if(isset($notification->data['service_type']))
                                                        {{ ucfirst($notification->data['service_type']) }} - Overdue Delivery
                                                        <span class="badge bg-{{ $notification->data['service_type'] === 'carpet' ? 'info' : 'success' }} ms-1">
                                                            {{ ucfirst($notification->data['service_type']) }}
                                                        </span>
                                                    @else
                                                        Overdue Delivery Alert
                                                    @endif
                                                @else
                                                    {{ class_basename($notification->type) }}
                                                @endif
                                                @if(!$notification->read_at)
                                                    <span class="badge bg-primary ms-2">New</span>
                                                @endif
                                            </h6>
                                            <p class="mb-1 text-muted">
                                                {{ $notification->data['message'] ?? 'No message available' }}
                                            </p>
                                            <small class="text-muted">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="d-flex flex-wrap gap-2">
                                            @if($notification->type === 'App\\Notifications\\OverdueDeliveryNotification' && isset($notification->data['action_url']))
                                                <a href="{{ $notification->data['action_url'] }}" class="btn btn-primary btn-sm">
                                                    <i class="bx bx-show"></i> View Details
                                                </a>
                                            @endif

                                            @if(!$notification->read_at)
                                                <button class="btn btn-success btn-sm"
                                                        onclick="markAsRead('{{ $notification->id }}')"
                                                        title="Mark as read">
                                                    <i class="bx bx-check"></i> Mark as Read
                                                </button>
                                            @endif

                                             @if(Auth::user()->can('mpesa.compare'))
                                            <button class="btn btn-outline-danger btn-sm"
                                                    onclick="deleteNotification('{{ $notification->id }}')"
                                                    title="Delete notification">
                                                <i class="bx bx-trash"></i> Delete
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bx bx-bell-off fs-1 text-muted"></i>
                            @if($currentFilter !== 'all')
                                <h5 class="mt-3 text-muted">No {{ ucfirst($currentFilter) }} notifications found</h5>
                                <p class="text-muted">No {{ $currentFilter }} service notifications at the moment.</p>
                                <a href="{{ route('notifications.index') }}" class="btn btn-primary">
                                    <i class="bx bx-list-ul"></i> View All Notifications
                                </a>
                            @else
                                <h5 class="mt-3 text-muted">No notifications found</h5>
                                <p class="text-muted">You're all caught up!</p>
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
.notification-item.unread {
    background-color: rgba(13, 110, 253, 0.05);
    border-left: 4px solid #0d6efd;
}

.notification-item:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.notification-item .btn {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.notification-item .d-flex.gap-2 {
    gap: 0.5rem !important;
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
            const notification = document.querySelector(`[data-id="${notificationId}"]`);
            notification.classList.remove('unread');
            notification.querySelector('.badge')?.remove();
            notification.querySelector('.btn-outline-success')?.remove();

            // Update notification count in header
            updateNotificationCount();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove unread styling from all notifications
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                item.querySelector('.badge')?.remove();
                item.querySelector('.btn-outline-success')?.remove();
            });

            // Update notification count in header
            updateNotificationCount();
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
                document.querySelector(`[data-id="${notificationId}"]`).remove();
                updateNotificationCount();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function updateNotificationCount() {
    // This would update the notification count in the header
    // Implementation depends on your header structure
}
</script>

@endsection
