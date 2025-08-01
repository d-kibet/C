@extends('admin_master')
@section('admin')
<div class="content">
    <div class="container-fluid">
        <h4>MPesa Transactions Report</h4>

        <!-- Date Filter Form -->
        <form method="GET" action="{{ route('reports.mpesa.today') }}" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input
                        type="date"
                        name="date"
                        class="form-control"
                        value="{{ old('date', $selectedDate ?? \Carbon\Carbon::today()->toDateString()) }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <div class="mb-3">
            <p><strong>Total MPesa (Today):</strong> {{ $totalMPesa }}</p>
            <p><strong>Difference (Yesterday - Today):</strong> {{ $totalDifference }}</p>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Cash</th>
                    <th>Float</th>
                    <th>Working</th>
                    <th>Account</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mpesaRecords as $record)
                <tr>
                    <td>{{ $record->date }}</td>
                    <td>{{ $record->cash }}</td>
                    <td>{{ $record->float }}</td>
                    <td>{{ $record->working }}</td>
                    <td>{{ $record->account }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
