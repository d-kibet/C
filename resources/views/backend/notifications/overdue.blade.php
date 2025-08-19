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
                                            <a href="{{ $data['action_url'] }}" class="btn btn-primary btn-sm">
                                                <i class="bx bx-show"></i> View Details
                                            </a>
                                            
                                            <!-- Quick Edit Button -->
                                            <button class="btn btn-warning btn-sm"
                                                    onclick="openQuickEdit('{{ $notification->id }}', '{{ $data['service_type'] }}', '{{ $data['service_uniqueid'] }}', '{{ $data['customer_phone'] }}', {{ json_encode($serviceData[$notification->id] ?? []) }})"
                                                    title="Quick update status">
                                                <i class="bx bx-edit"></i> Quick Update
                                            </button>
                                            
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

                    {{-- Pagination --}}
                    @if($notifications->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $notifications->appends(request()->query())->links('custom.pagination') }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Edit Modal -->
<div class="modal fade" id="quickEditModal" tabindex="-1" aria-labelledby="quickEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="quickEditModalLabel">
                    <i class="bx bx-edit-alt me-2"></i>Quick Status Update
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickEditForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bx bx-info-circle me-2"></i>Service Information</h6>
                                    <p class="mb-1"><strong>Service Type:</strong> <span id="edit-service-type"></span></p>
                                    <p class="mb-1"><strong>Service ID:</strong> <span id="edit-service-id"></span></p>
                                    <p class="mb-0"><strong>Customer Phone:</strong> <span id="edit-customer-phone"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Carpet Service Fields -->
                            <div id="carpetFields" style="display: none;">
                                <div class="form-group mb-3">
                                    <label for="payment_status" class="form-label">Payment Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="payment_status" name="payment_status">
                                        <option value="">Select Payment Status</option>
                                        <option value="Not Paid">Not Paid</option>
                                        <option value="Paid">Paid</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="transaction_code" class="form-label">Transaction Code</label>
                                    <input type="text" class="form-control" id="transaction_code" name="transaction_code" placeholder="e.g., MP12345678">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="delivered" class="form-label">Delivery Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="delivered" name="delivered">
                                        <option value="">Select Delivery Status</option>
                                        <option value="Not Delivered">Not Delivered</option>
                                        <option value="Delivered">Delivered</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="date_delivered" class="form-label">Date Delivered</label>
                                    <input type="date" class="form-control" id="date_delivered" name="date_delivered" min="{{ date('Y-m-d') }}">
                                </div>
                            </div>

                            <!-- Laundry Service Fields (Keep original) -->
                            <div id="laundryFields" style="display: none;">
                                <div class="form-group mb-3">
                                    <label for="status" class="form-label">Update Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Select Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Ready for Delivery">Ready for Delivery</option>
                                        <option value="Delivered">Delivered</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="delivery_date" class="form-label">Delivery Date (Optional)</label>
                                    <input type="date" class="form-control" id="delivery_date" name="delivery_date" min="{{ date('Y-m-d') }}">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="notes" class="form-label">Additional Notes (Optional)</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Add any additional notes..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Note:</strong> Setting delivery status to "Delivered" or "Cancelled" will automatically remove this item from overdue alerts.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning" onclick="submitQuickEdit()" id="submitQuickEdit">
                    <i class="bx bx-check"></i> Update Status
                </button>
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

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    border-bottom: none;
}

.card {
    border-radius: 10px;
}

.alert {
    border-radius: 10px;
}
</style>

<script>
let currentNotificationId = null;

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

function openQuickEdit(notificationId, serviceType, serviceId, customerPhone, currentData = {}) {
    currentNotificationId = notificationId;
    
    // Update modal content
    document.getElementById('edit-service-type').textContent = serviceType.charAt(0).toUpperCase() + serviceType.slice(1);
    document.getElementById('edit-service-id').textContent = serviceId;
    document.getElementById('edit-customer-phone').textContent = customerPhone;
    
    // Reset form
    document.getElementById('quickEditForm').reset();
    
    // Show/hide appropriate fields based on service type
    const carpetFields = document.getElementById('carpetFields');
    const laundryFields = document.getElementById('laundryFields');
    
    if (serviceType === 'carpet') {
        carpetFields.style.display = 'block';
        laundryFields.style.display = 'none';
        
        // Pre-populate carpet fields with existing data
        if (currentData.payment_status) {
            document.getElementById('payment_status').value = currentData.payment_status;
        }
        if (currentData.transaction_code) {
            document.getElementById('transaction_code').value = currentData.transaction_code;
        }
        if (currentData.delivered) {
            document.getElementById('delivered').value = currentData.delivered;
        }
        if (currentData.date_delivered) {
            document.getElementById('date_delivered').value = currentData.date_delivered;
        }
    } else if (serviceType === 'laundry') {
        carpetFields.style.display = 'none';
        laundryFields.style.display = 'block';
        
        // Pre-populate laundry fields with existing data
        if (currentData.status) {
            document.getElementById('status').value = currentData.status;
        }
        if (currentData.delivery_date) {
            document.getElementById('delivery_date').value = currentData.delivery_date;
        }
        if (currentData.notes) {
            document.getElementById('notes').value = currentData.notes;
        }
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('quickEditModal'));
    modal.show();
}

function submitQuickEdit() {
    if (!currentNotificationId) return;
    
    const form = document.getElementById('quickEditForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitQuickEdit');
    
    // Validate required fields based on service type
    const serviceType = document.getElementById('edit-service-type').textContent.toLowerCase();
    let isValid = true;
    let errorMessage = '';
    
    if (serviceType === 'carpet') {
        const paymentStatus = formData.get('payment_status');
        const delivered = formData.get('delivered');
        
        if (!paymentStatus) {
            errorMessage = 'Please select a payment status';
            isValid = false;
        } else if (!delivered) {
            errorMessage = 'Please select a delivery status';
            isValid = false;
        }
    } else if (serviceType === 'laundry') {
        const status = formData.get('status');
        if (!status) {
            errorMessage = 'Please select a status';
            isValid = false;
        }
    }
    
    if (!isValid) {
        alert(errorMessage);
        return;
    }
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Updating...';
    submitBtn.disabled = true;
    
    // Convert FormData to JSON
    const data = {};
    formData.forEach((value, key) => {
        if (value) data[key] = value;
    });
    
    fetch(`/notifications/${currentNotificationId}/quick-update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('success', data.message);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('quickEditModal'));
            modal.hide();
            
            // Reload page after short delay to show success message
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            // Show error message
            showNotification('error', data.message || 'Failed to update service');
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'An error occurred while updating');
        
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showNotification(type, message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="bx ${type === 'success' ? 'bx-check-circle' : 'bx-error-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Auto-set delivery date when status is changed to "Delivered" for carpet services
document.addEventListener('change', function(e) {
    // For carpet delivery status
    if (e.target.id === 'delivered') {
        const dateDeliveredInput = document.getElementById('date_delivered');
        if (e.target.value === 'Delivered' && !dateDeliveredInput.value) {
            dateDeliveredInput.value = new Date().toISOString().split('T')[0];
        }
    }
    
    // For laundry status (keep original functionality)
    if (e.target.id === 'status') {
        const deliveryDateInput = document.getElementById('delivery_date');
        if (e.target.value === 'Delivered' && deliveryDateInput && !deliveryDateInput.value) {
            deliveryDateInput.value = new Date().toISOString().split('T')[0];
        }
    }
});
</script>

@endsection
