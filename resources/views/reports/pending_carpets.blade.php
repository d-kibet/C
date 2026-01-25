@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <!-- Start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">Pending & Aging Carpets</h4>
                </div>
            </div>
        </div>
        <!-- End page title -->

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="text-muted mb-2">Pending Delivery</h5>
                        <h2 class="text-primary mb-0">{{ $pendingCount }}</h2>
                        <small class="text-muted">carpets awaiting delivery</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="text-muted mb-2">Unpaid Carpets</h5>
                        <h2 class="text-warning mb-0">{{ $unpaidCount }}</h2>
                        <small class="text-muted">carpets not paid</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="text-muted mb-2">Unpaid Value</h5>
                        <h2 class="text-danger mb-0">{{ number_format($unpaidValue, 2) }}</h2>
                        <small class="text-muted">KES outstanding</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="text-muted mb-2">Avg Aging</h5>
                        <h2 class="text-info mb-0">{{ $avgAgingDays }}</h2>
                        <small class="text-muted">days (pending delivery)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="true">
                                    Pending Delivery <span class="badge bg-primary">{{ $pendingCount }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="unpaid-tab" data-bs-toggle="tab" data-bs-target="#unpaid" type="button" role="tab" aria-controls="unpaid" aria-selected="false">
                                    Unpaid Carpets <span class="badge bg-warning text-dark">{{ $unpaidCount }}</span>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="reportTabsContent">
                            <!-- Pending Delivery Tab -->
                            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                                <div class="table-responsive">
                                    <table id="pendingTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Unique ID</th>
                                                <th>Name</th>
                                                <th>Size</th>
                                                <th>Price</th>
                                                <th>Phone</th>
                                                <th>Location</th>
                                                <th>Date Received</th>
                                                <th>Aging</th>
                                                <th>Payment Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingDelivery as $carpet)
                                                <tr>
                                                    <td>{{ $carpet->uniqueid }}</td>
                                                    <td>{{ $carpet->name }}</td>
                                                    <td>{{ $carpet->size }}</td>
                                                    <td>{{ number_format($carpet->price, 2) }}</td>
                                                    <td>{{ $carpet->phone }}</td>
                                                    <td>{{ $carpet->location }}</td>
                                                    <td>{{ $carpet->date_received }}</td>
                                                    <td>
                                                        @if($carpet->aging_days <= 7)
                                                            <span class="badge bg-success">{{ $carpet->aging_days }} days</span>
                                                        @elseif($carpet->aging_days <= 14)
                                                            <span class="badge bg-warning text-dark">{{ $carpet->aging_days }} days</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ $carpet->aging_days }} days</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($carpet->payment_status == 'Paid')
                                                            <span class="badge bg-success">Paid</span>
                                                        @else
                                                            <span class="badge bg-danger">Not Paid</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Unpaid Carpets Tab -->
                            <div class="tab-pane fade" id="unpaid" role="tabpanel" aria-labelledby="unpaid-tab">
                                <div class="table-responsive">
                                    <table id="unpaidTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Unique ID</th>
                                                <th>Name</th>
                                                <th>Size</th>
                                                <th>Price</th>
                                                <th>Phone</th>
                                                <th>Location</th>
                                                <th>Date Received</th>
                                                <th>Delivered Status</th>
                                                <th>Aging</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($unpaidCarpets as $carpet)
                                                <tr>
                                                    <td>{{ $carpet->uniqueid }}</td>
                                                    <td>{{ $carpet->name }}</td>
                                                    <td>{{ $carpet->size }}</td>
                                                    <td>{{ number_format($carpet->price, 2) }}</td>
                                                    <td>{{ $carpet->phone }}</td>
                                                    <td>{{ $carpet->location }}</td>
                                                    <td>{{ $carpet->date_received }}</td>
                                                    <td>
                                                        @if($carpet->delivered == 'Delivered')
                                                            <span class="badge bg-success">Delivered</span>
                                                        @else
                                                            <span class="badge bg-secondary">Not Delivered</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($carpet->aging_days <= 7)
                                                            <span class="badge bg-success">{{ $carpet->aging_days }} days</span>
                                                        @elseif($carpet->aging_days <= 14)
                                                            <span class="badge bg-warning text-dark">{{ $carpet->aging_days }} days</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ $carpet->aging_days }} days</span>
                                                        @endif
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize DataTables for both tables
    $('#pendingTable').DataTable({
        order: [[7, 'desc']], // Sort by aging days descending
        responsive: true,
        pageLength: 25
    });

    $('#unpaidTable').DataTable({
        order: [[8, 'desc']], // Sort by aging days descending
        responsive: true,
        pageLength: 25
    });

    // Re-adjust tables when tab is shown (fixes DataTables responsive issues with hidden tabs)
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
    });
});
</script>

@endsection
