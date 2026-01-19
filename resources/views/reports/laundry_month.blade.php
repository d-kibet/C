@extends('admin_master')
@section('admin')
<style>
    /* Adjust card layout for smaller screens */
    @media (max-width: 576px) {
        .info-cards .card {
            margin-bottom: 10px;
        }
    }
</style>
<div class="content">
    <div class="container-fluid">

        <!-- Page Title / Filter Row -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <h4 class="page-title mb-0">Laundry Records for {{ $year }}-{{ sprintf('%02d', $month) }}</h4>
            </div>
            <div class="col-md-6 text-end">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('reports.laundry.viewMonth') }}" class="d-inline-flex flex-wrap align-items-center justify-content-end">
                    <select name="month" class="form-select me-2 mb-2">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                    <select name="year" class="form-select me-2 mb-2">
                        @for($y = date('Y') - 5; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-primary mb-2">Filter</button>
                </form>
            </div>
        </div>
        <!-- End Filter Row -->

        <!-- Info Cards (Totals) -->
        <div class="row info-cards">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Total Paid</h5>
                        <p>{{ $totalPaid }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Total Unpaid</h5>
                        <p>{{ $totalUnpaid }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Grand Total</h5>
                        <p>{{ $grandTotal }}</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Info Cards -->

        <!-- Download Buttons -->
        <div class="d-flex justify-content-end mb-3">
            <!-- Download All CSV -->
            <a href="{{ route('reports.laundry.downloadMonth', ['month' => $month, 'year' => $year]) }}"
               class="btn btn-secondary rounded-pill me-2"
            >
                <i class="mdi mdi-download"></i> Download CSV
            </a>
            <!-- Download New Clients CSV -->
            @can('admin.all')
            <a href="{{ route('reports.laundry.downloadNewMonth', ['month' => $month, 'year' => $year]) }}"
               class="btn btn-info rounded-pill"
            >
                <i class="mdi mdi-account-plus"></i> Download New Clients CSV
            </a>
            @endcan
        </div>

        <!-- All Laundry Table -->
        <h5>All Laundry for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Unique ID</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Date Received</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laundry as $record)
                        <tr>
                            <td>{{ $record->unique_id }}</td>
                            <td>{{ $record->phone }}</td>
                            <td>KES {{ number_format($record->total, 2) }}</td>
                            <td>{{ $record->payment_status }}</td>
                            <td>{{ $record->date_received }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No laundry records found for this month.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- New Clients Table -->
        <h5>New Clients This Month</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Unique ID</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Date Received</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($newLaundry as $record)
                        <tr>
                            <td>{{ $record->unique_id }}</td>
                            <td>{{ $record->phone }}</td>
                            <td>KES {{ number_format($record->total, 2) }}</td>
                            <td>{{ $record->payment_status }}</td>
                            <td>{{ $record->date_received }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No new clients found for this month.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
