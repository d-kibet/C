@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">All Laundry Data</h4>
                    <div class="page-title-right d-flex align-items-center">
                        <!-- Add Laundry Button -->
                        <a
                            href="{{ route('add.laundry') }}"
                            class="btn btn-primary rounded-pill waves-effect waves-light me-2"
                        >
                            Add Laundry
                        </a>

                        <!-- CSV Download Button (shown only if user has permission) -->
                        @can('admin.all')
                            <a
                                href="{{ route('reports.laundry.downloadAll') }}"
                                class="btn btn-secondary rounded-pill waves-effect waves-light"
                            >
                                <i class="mdi mdi-download"></i> Download Laundry CSV
                            </a>
                        @endcan
                    </div>
                </div>

            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="laundryTable" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Date Received</th>
                                        <th>Date Delivered</th>
                                        <th>Total</th>
                                        <th>Payment Status</th>
                                        <th>Delivered</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div> <!-- end container-fluid -->
</div> <!-- end content -->

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#laundryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('laundries.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'row_number', name: 'row_number', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'phone', name: 'phone' },
            { data: 'date_received', name: 'date_received' },
            { data: 'date_delivered', name: 'date_delivered' },
            { data: 'total', name: 'total' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'delivered', name: 'delivered' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']], // Sort by date_received descending
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No laundry records found",
            zeroRecords: "No matching records found"
        }
    });
});
</script>
@endpush
