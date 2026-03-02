@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid" style="margin-top: 20px;">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">M-Pesa Payment Transactions</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">M-Pesa Transactions</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="myTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Service</th>
                                        <th>Phone</th>
                                        <th>Amount</th>
                                        <th>Receipt No.</th>
                                        <th>Status</th>
                                        <th>Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $txn)
                                        <tr>
                                            <td>{{ $txn->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ ucfirst($txn->service_type) }} #{{ $txn->service_id }}</td>
                                            <td>{{ $txn->phone }}</td>
                                            <td>{{ number_format($txn->amount, 2) }}</td>
                                            <td>{{ $txn->mpesa_receipt_number ?? '-' }}</td>
                                            <td>
                                                @if($txn->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($txn->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($txn->status === 'cancelled')
                                                    <span class="badge bg-secondary">Cancelled</span>
                                                @else
                                                    <span class="badge bg-danger">Failed</span>
                                                @endif
                                            </td>
                                            <td>{{ $txn->result_desc ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
