@extends('admin_master')
@section('admin')
<div class="content">
    <div class="container-fluid">
        <!-- Header with Title and Totals -->
        <div class="row mb-3 align-items-center flex-wrap">
            <div class="col-md-6">
                <h4>Laundry Records Report</h4>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-0"><strong>Total Paid:</strong> {{ $totalLaundryPaid }}</p>
                <p class="mb-0"><strong>Total Unpaid:</strong> {{ $totalLaundryUnpaid }}</p>
                <p class="mb-0"><strong>Grand Total:</strong> {{ $grandTotal }}</p>
            </div>
        </div>

        <!-- Date Filter Form -->
        <form method="GET" action="{{ route('reports.laundry.today') }}" class="mb-3">
            <div class="row flex-wrap">
                <div class="col-md-4 col-12 mb-2">
                    <input type="date" name="date" class="form-control"
                           value="{{ old('date', $selectedDate ?? \Carbon\Carbon::today()->toDateString()) }}">
                </div>
                <div class="col-md-2 col-12 mb-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        <!-- Paid Laundry Section -->
        <h5>Paid Laundry</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Price</th>
                        <th>Total Paid</th>
                        <th>Date Received</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paidLaundry as $laundry)
                    <tr>
                        <td>{{ $laundry->name }}</td>
                        <td>{{ $laundry->phone }}</td>
                        <td>{{ $laundry->price }}</td>
                        <td>{{ $laundry->total }}</td>
                        <td>{{ $laundry->date_received }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">No paid laundry records found for the selected date.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Unpaid Laundry Section -->
        <h5>Unpaid Laundry</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Price</th>
                        <th>Date Received</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unpaidLaundry as $laundry)
                    <tr>
                        <td>{{ $laundry->name }}</td>
                        <td>{{ $laundry->phone }}</td>
                        <td>{{ $laundry->price }}</td>
                        <td>{{ $laundry->date_received }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">No unpaid laundry records found for the selected date.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
