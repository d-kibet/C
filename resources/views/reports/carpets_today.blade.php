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
                <p class="mb-0"><strong>Total Paid Amount:</strong> {{ $totalPaidCarpets }}</p>
                <p class="mb-0"><strong>Total Unpaid Amount:</strong> {{ $totalUnpaidCarpets }}</p>
                <p class="mb-0"><strong>Grand Total:</strong> {{ $totalPaidCarpets + $totalUnpaidCarpets }}</p>
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

        @php
            function renderCarpetTable($carpets) {
                $html = '<div class="table-responsive"><table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Unique ID</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Payment Status</th>
                                    <th>Date Received</th>
                                </tr>
                            </thead>
                            <tbody>';
                foreach ($carpets as $carpet) {
                    $html .= '<tr>
                                <td>' . $carpet->uniqueid . '</td>
                                <td>' . $carpet->size . '</td>
                                <td>' . $carpet->price . '</td>
                                <td>' . $carpet->payment_status . '</td>
                                <td>' . $carpet->date_received . '</td>
                              </tr>';
                }
                $html .= '</tbody></table></div>';
                return $html;
            }
        @endphp

        <!-- Paid Carpets Section -->
        <h5>Paid Carpets</h5>
        {!! renderCarpetTable($paidCarpets) !!}

        <!-- Unpaid Carpets Section -->
        <h5>Unpaid Carpets</h5>
        {!! renderCarpetTable($unpaidCarpets) !!}
    </div>
</div>
@endsection
