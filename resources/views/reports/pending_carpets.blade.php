@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <!-- Start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">Pending & Aging Carpet Orders</h4>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="text-muted mb-2">Pending Delivery</h5>
                        <h2 class="text-primary mb-0">{{ $pendingCount }}</h2>
                        <small class="text-muted">orders awaiting delivery</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="text-muted mb-2">Unpaid Orders</h5>
                        <h2 class="text-warning mb-0">{{ $unpaidCount }}</h2>
                        <small class="text-muted">carpet orders not paid</small>
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
                                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                    Pending Delivery <span class="badge bg-primary">{{ $pendingCount }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="unpaid-tab" data-bs-toggle="tab" data-bs-target="#unpaid" type="button" role="tab">
                                    Unpaid Orders <span class="badge bg-warning text-dark">{{ $unpaidCount }}</span>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="reportTabsContent">
                            <!-- Pending Delivery Tab -->
                            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="pendingTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Phone</th>
                                                <th>Location</th>
                                                <th>Unique IDs</th>
                                                <th>Total (KES)</th>
                                                <th>Date Received</th>
                                                <th>Aging</th>
                                                <th>Payment Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingDelivery as $order)
                                                <tr>
                                                    <td>{{ $order->name }}</td>
                                                    <td>{{ $order->phone }}</td>
                                                    <td>{{ $order->location }}</td>
                                                    <td>{{ $order->items->pluck('unique_id')->implode(', ') }}</td>
                                                    <td>{{ number_format($order->total, 2) }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($order->date_received)->format('d M Y') }}</td>
                                                    <td>
                                                        @if($order->aging_days <= 7)
                                                            <span class="badge bg-success">{{ $order->aging_days }} days</span>
                                                        @elseif($order->aging_days <= 14)
                                                            <span class="badge bg-warning text-dark">{{ $order->aging_days }} days</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ $order->aging_days }} days</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($order->payment_status == 'Paid')
                                                            <span class="badge bg-success">Paid</span>
                                                        @elseif($order->payment_status == 'Partial')
                                                            <span class="badge bg-warning text-dark">Partial</span>
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

                            <!-- Unpaid Orders Tab -->
                            <div class="tab-pane fade" id="unpaid" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="unpaidTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Phone</th>
                                                <th>Location</th>
                                                <th>Unique IDs</th>
                                                <th>Total (KES)</th>
                                                <th>Date Received</th>
                                                <th>Delivery Status</th>
                                                <th>Aging</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($unpaidOrders as $order)
                                                <tr>
                                                    <td>{{ $order->name }}</td>
                                                    <td>{{ $order->phone }}</td>
                                                    <td>{{ $order->location }}</td>
                                                    <td>{{ $order->items->pluck('unique_id')->implode(', ') }}</td>
                                                    <td>{{ number_format($order->total, 2) }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($order->date_received)->format('d M Y') }}</td>
                                                    <td>
                                                        @if($order->isFullyDelivered())
                                                            <span class="badge bg-success">Delivered</span>
                                                        @else
                                                            <span class="badge bg-secondary">Not Delivered</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($order->aging_days <= 7)
                                                            <span class="badge bg-success">{{ $order->aging_days }} days</span>
                                                        @elseif($order->aging_days <= 14)
                                                            <span class="badge bg-warning text-dark">{{ $order->aging_days }} days</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ $order->aging_days }} days</span>
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
    $('#pendingTable').DataTable({
        order: [[6, 'desc']],
        responsive: true,
        pageLength: 25
    });

    $('#unpaidTable').DataTable({
        order: [[7, 'desc']],
        responsive: true,
        pageLength: 25
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
    });
});
</script>

@endsection
