@extends('admin_master')
@section('admin')
<div class="content">
    <div class="container-fluid">
        <!-- Header with Title and Totals -->
        <div class="row mb-3 align-items-center flex-wrap">
            <div class="col-md-6">
                <h4>Carpets Report</h4>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-0"><strong>Total Paid Amount:</strong> KES {{ number_format($totalPaidCarpets, 2) }}</p>
                <p class="mb-0"><strong>Total Unpaid Amount:</strong> KES {{ number_format($totalUnpaidCarpets, 2) }}</p>
                <p class="mb-0"><strong>Grand Total:</strong> KES {{ number_format($totalPaidCarpets + $totalUnpaidCarpets, 2) }}</p>
            </div>
        </div>

        <!-- Date Filter Form -->
        <form method="GET" action="{{ route('reports.carpets.today') }}" class="mb-3">
            <div class="row flex-wrap">
                <div class="col-md-4 col-12 mb-2">
                    <input type="date" name="date" class="form-control" value="{{ $selectedDate ?? \Carbon\Carbon::today()->toDateString() }}">
                </div>
                <div class="col-md-2 col-12 mb-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        <!-- Paid Orders -->
        <h5 class="mt-3">Paid Carpet Orders</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Unique IDs</th>
                        <th>Total (KES)</th>
                        <th>Date Received</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paidOrders as $order)
                        <tr>
                            <td>{{ $order->name }}</td>
                            <td>{{ $order->phone }}</td>
                            <td>{{ $order->items->pluck('unique_id')->implode(', ') }}</td>
                            <td>{{ number_format($order->total, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->date_received)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No paid carpet orders for this date.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Unpaid / Partial Orders -->
        <h5>Unpaid / Partial Carpet Orders</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Unique IDs</th>
                        <th>Total (KES)</th>
                        <th>Payment Status</th>
                        <th>Date Received</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unpaidOrders as $order)
                        <tr>
                            <td>{{ $order->name }}</td>
                            <td>{{ $order->phone }}</td>
                            <td>{{ $order->items->pluck('unique_id')->implode(', ') }}</td>
                            <td>{{ number_format($order->total, 2) }}</td>
                            <td>
                                @if($order->payment_status === 'Partial')
                                    <span class="badge bg-warning text-dark">Partial</span>
                                @else
                                    <span class="badge bg-danger">Not Paid</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($order->date_received)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No unpaid carpet orders for this date.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
