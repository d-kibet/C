@extends('admin_master')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Customer Retention Analysis</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Reports</a></li>
                            <li class="breadcrumb-item active">Customer Retention</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Filters Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Filter Options</h4>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('customer.retention.index') }}" class="row g-3">
                            <div class="col-md-2">
                                <label for="inactive_months" class="form-label">Inactive Period</label>
                                <select class="form-select" name="inactive_months" id="inactive_months">
                                    <option value="1" {{ $inactiveMonths == 1 ? 'selected' : '' }}>1 Month</option>
                                    <option value="2" {{ $inactiveMonths == 2 ? 'selected' : '' }}>2 Months</option>
                                    <option value="3" {{ $inactiveMonths == 3 ? 'selected' : '' }}>3 Months</option>
                                    <option value="6" {{ $inactiveMonths == 6 ? 'selected' : '' }}>6 Months</option>
                                    <option value="12" {{ $inactiveMonths == 12 ? 'selected' : '' }}>1 Year</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="service_type" class="form-label">Service Type</label>
                                <select class="form-select" name="service_type" id="service_type">
                                    <option value="all" {{ $serviceType == 'all' ? 'selected' : '' }}>All Services</option>
                                    <option value="carpet" {{ $serviceType == 'carpet' ? 'selected' : '' }}>Carpet Only</option>
                                    <option value="laundry" {{ $serviceType == 'laundry' ? 'selected' : '' }}>Laundry Only</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="phone_search" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="phone_search" id="phone_search"
                                       value="{{ $phoneSearch }}" placeholder="Search by phone">
                            </div>
                            <div class="col-md-2">
                                <label for="unique_id_search" class="form-label">Unique ID</label>
                                <input type="text" class="form-control" name="unique_id_search" id="unique_id_search"
                                       value="{{ $uniqueIdSearch }}" placeholder="Search by ID">
                            </div>
                            <div class="col-md-2">
                                <label for="min_value" class="form-label">Min Value (KES)</label>
                                <input type="number" class="form-control" name="min_value" id="min_value"
                                       value="{{ $minValue }}" placeholder="0">
                            </div>
                            <div class="col-md-2">
                                <label for="max_value" class="form-label">Max Value (KES)</label>
                                <input type="number" class="form-control" name="max_value" id="max_value"
                                       value="{{ $maxValue }}" placeholder="999999">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Apply Filters
                                </button>
                                <a href="{{ route('customer.retention.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Clear Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Total Customers</p>
                                <h4 class="mb-0">{{ number_format($stats['total_customers']['combined']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-primary-subtle mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                        <i class="fas fa-users font-size-20"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Active Customers</p>
                                <h4 class="mb-0">{{ number_format($stats['active_customers']['combined']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-success-subtle mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-success">
                                        <i class="fas fa-user-check font-size-20"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Inactive Customers</p>
                                <h4 class="mb-0 text-danger">{{ number_format($stats['inactive_customers']['combined']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-danger-subtle mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-danger">
                                        <i class="fas fa-user-times font-size-20"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Potential Revenue</p>
                                <h4 class="mb-0 text-warning">KES {{ number_format($stats['potential_revenue']['total'], 2) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-warning-subtle mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-warning">
                                        <i class="fas fa-money-bill-wave font-size-20"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Inactive Customers ({{ $inactiveCustomers->count() }} found)</h5>
                            <div class="d-flex gap-2">
                                <!-- Export Buttons -->
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-download me-2"></i>Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('customer.retention.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
                                            <i class="fas fa-file-csv me-2"></i>Export as CSV
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('customer.retention.export', array_merge(request()->query(), ['format' => 'excel'])) }}">
                                            <i class="fas fa-file-excel me-2"></i>Export as Excel
                                        </a></li>
                                    </ul>
                                </div>

                                <!-- Follow-up Buttons -->
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-phone me-2"></i>Follow-up
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="generateFollowUp('sms')">
                                            <i class="fas fa-sms me-2"></i>SMS Template
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="generateFollowUp('whatsapp')">
                                            <i class="fab fa-whatsapp me-2"></i>WhatsApp Template
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if($inactiveCustomers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>Phone</th>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Service Type</th>
                                        <th>Last Service</th>
                                        <th>Days Inactive</th>
                                        <th>Total Services</th>
                                        <th>Total Value</th>
                                        <th>Customer Tier</th>
                                        <th>Recent IDs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inactiveCustomers as $customer)
                                    @php
                                        $daysSinceLastService = \Carbon\Carbon::parse($customer->last_service_date)->diffInDays(now());
                                        $customerTier = $customer->total_value >= 10000 ? 'VIP' :
                                                       ($customer->total_value >= 5000 ? 'Premium' :
                                                       ($customer->total_value >= 2000 ? 'Regular' : 'Basic'));
                                        $tierColor = $customerTier == 'VIP' ? 'badge-soft-warning' :
                                                    ($customerTier == 'Premium' ? 'badge-soft-info' :
                                                    ($customerTier == 'Regular' ? 'badge-soft-success' : 'badge-soft-secondary'));
                                    @endphp
                                    <tr>
                                        <td><input type="checkbox" name="customer_ids[]" value="{{ $customer->phone }}" class="customer-checkbox"></td>
                                        <td>{{ $customer->phone }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->location }}</td>
                                        <td>
                                            <span class="badge badge-soft-primary">{{ $customer->service_type }}</span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($customer->last_service_date)->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge badge-soft-danger">{{ $daysSinceLastService }} days</span>
                                        </td>
                                        <td>{{ $customer->total_services }}</td>
                                        <td>KES {{ number_format($customer->total_value, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $tierColor }}">{{ $customerTier }}</span>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($customer->recent_unique_ids, 30) }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No inactive customers found</h5>
                            <p class="text-muted">Try adjusting your filter criteria to see more results.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Follow-up Modal -->
<div class="modal fade" id="followUpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Follow-up Templates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="followUpContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="copyToClipboard()">Copy All</button>
            </div>
        </div>
    </div>
</div>

<script>
// Select All functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.customer-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Generate follow-up templates
function generateFollowUp(format) {
    const selectedCustomers = [];
    document.querySelectorAll('.customer-checkbox:checked').forEach(checkbox => {
        selectedCustomers.push(checkbox.value);
    });

    if (selectedCustomers.length === 0) {
        alert('Please select customers for follow-up');
        return;
    }

    // Show loading
    document.getElementById('followUpContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Generating templates...</div>';

    fetch('{{ route("customer.retention.followup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            customer_ids: selectedCustomers,
            format: format
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let content = `<h6>${format.toUpperCase()} Template (${data.total_customers} customers):</h6>`;
            content += `<div class="alert alert-info"><strong>Template:</strong><br>${data.template}</div>`;
            content += '<div class="table-responsive"><table class="table table-sm">';
            content += '<thead><tr><th>Phone</th><th>Name</th>';
            if (format === 'whatsapp') {
                content += '<th>WhatsApp Link</th>';
            } else {
                content += '<th>Message</th>';
            }
            content += '</tr></thead><tbody>';

            data.messages.forEach(message => {
                content += `<tr><td>${message.phone}</td><td>${message.name}</td>`;
                if (format === 'whatsapp') {
                    content += `<td><a href="${message.whatsapp_url}" target="_blank" class="btn btn-sm btn-success">Open WhatsApp</a></td>`;
                } else {
                    content += `<td><small>${message.message}</small></td>`;
                }
                content += '</tr>';
            });

            content += '</tbody></table></div>';
            document.getElementById('followUpContent').innerHTML = content;

            // Show modal
            new bootstrap.Modal(document.getElementById('followUpModal')).show();
        } else {
            alert('Error generating follow-up templates');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating follow-up templates');
    });
}

function copyToClipboard() {
    const content = document.getElementById('followUpContent').innerText;
    navigator.clipboard.writeText(content).then(() => {
        alert('Content copied to clipboard!');
    });
}
</script>

@endsection