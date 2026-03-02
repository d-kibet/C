@extends('admin_master')
@section('admin')
<div class="content">
    <div class="container-fluid">

        <!-- Page Title / Filter Row -->
        <div class="row mb-3 align-items-center">
            <div class="col">
                <h4 class="page-title mb-0">Laundry Orders for {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</h4>
            </div>
            <div class="col-auto">
                <form method="GET" action="{{ route('reports.laundry.viewMonth') }}" class="d-flex align-items-center gap-2">
                    <select name="month" class="form-select form-select-sm" style="width:auto;">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                    <select name="year" class="form-select form-select-sm" style="width:auto;">
                        @for($y = date('Y') - 5; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                </form>
            </div>
        </div>

        <!-- Stats Table with Filter Buttons -->
        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Total Paid (KES)</th>
                                <th class="text-center">Total Unpaid (KES)</th>
                                <th class="text-center">Grand Total (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-success btn-lg fw-bold laundry-stat-btn" data-filter="Paid">
                                        {{ number_format($totalPaid, 2) }}
                                    </button>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-lg fw-bold laundry-stat-btn" data-filter="Not Paid">
                                        {{ number_format($totalUnpaid, 2) }}
                                    </button>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-primary btn-lg fw-bold laundry-stat-btn" data-filter="">
                                        {{ number_format($grandTotal, 2) }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Download Buttons -->
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('reports.laundry.downloadMonth', ['month' => $month, 'year' => $year]) }}"
               class="btn btn-secondary rounded-pill me-2">
                <i class="mdi mdi-download"></i> Download CSV
            </a>
            @can('admin.all')
            <a href="{{ route('reports.laundry.downloadNewMonth', ['month' => $month, 'year' => $year]) }}"
               class="btn btn-info rounded-pill">
                <i class="mdi mdi-account-plus"></i> Download New Clients CSV
            </a>
            @endcan
        </div>

        <!-- All Laundry Orders Table -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Laundry Orders ({{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }})</h5>
                <span id="activeFilter" class="badge bg-secondary" style="display:none;"></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="allLaundryTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Items</th>
                                <th>Total (KES)</th>
                                <th>Payment Status</th>
                                <th>Date Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>{{ $order->name }}</td>
                                    <td>{{ $order->phone }}</td>
                                    <td>
                                        @foreach($order->items as $item)
                                            <span class="d-block">{{ $item->description }} &times; {{ $item->quantity }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ number_format($order->total, 2) }}</td>
                                    <td data-filter="{{ $order->payment_status }}">
                                        @if($order->payment_status == 'Paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($order->payment_status == 'Partial')
                                            <span class="badge bg-warning text-dark">Partial</span>
                                        @else
                                            <span class="badge bg-danger">Not Paid</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($order->date_received)->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No laundry orders found for this month.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- New Clients Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">New Clients This Month</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="newLaundryTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Items</th>
                                <th>Total (KES)</th>
                                <th>Payment Status</th>
                                <th>Date Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($newOrders as $order)
                                <tr>
                                    <td>{{ $order->name }}</td>
                                    <td>{{ $order->phone }}</td>
                                    <td>
                                        @foreach($order->items as $item)
                                            <span class="d-block">{{ $item->description }} &times; {{ $item->quantity }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ number_format($order->total, 2) }}</td>
                                    <td>
                                        @if($order->payment_status == 'Paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($order->payment_status == 'Partial')
                                            <span class="badge bg-warning text-dark">Partial</span>
                                        @else
                                            <span class="badge bg-danger">Not Paid</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($order->date_received)->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No new clients found for this month.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var allTable = $('#allLaundryTable').DataTable({
        order: [[5, 'desc']],
        autoWidth: false,
        pageLength: 25
    });

    var newTable = $('#newLaundryTable').DataTable({
        order: [[5, 'desc']],
        autoWidth: false,
        pageLength: 25
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'allLaundryTable') return true;
        if (!window.laundryPaymentFilter) return true;

        var cellNode = allTable.cell(dataIndex, 4).node();
        var cellFilter = $(cellNode).attr('data-filter');
        return cellFilter === window.laundryPaymentFilter;
    });

    window.laundryPaymentFilter = '';

    $('.laundry-stat-btn').on('click', function() {
        var filter = $(this).data('filter');

        if (window.laundryPaymentFilter === filter && filter !== '') {
            filter = '';
        }
        window.laundryPaymentFilter = filter;

        $('.laundry-stat-btn').each(function() {
            var f = $(this).data('filter');
            $(this).removeClass('btn-success btn-danger btn-primary btn-outline-success btn-outline-danger btn-outline-primary');
            if (f === 'Paid') {
                $(this).addClass(window.laundryPaymentFilter === 'Paid' ? 'btn-success' : 'btn-outline-success');
            } else if (f === 'Not Paid') {
                $(this).addClass(window.laundryPaymentFilter === 'Not Paid' ? 'btn-danger' : 'btn-outline-danger');
            } else {
                $(this).addClass(window.laundryPaymentFilter === '' ? 'btn-primary' : 'btn-outline-primary');
            }
        });

        var badge = $('#activeFilter');
        if (filter) {
            badge.text('Showing: ' + filter).show();
        } else {
            badge.hide();
        }

        allTable.draw();
    });
});
</script>
@endpush
