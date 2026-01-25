@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <!-- Start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">All Carpet Data</h4>
                    <div class="page-title-right d-flex align-items-center">
                        <!-- Add Carpet Button -->
                        <a
                            href="{{ route('add.carpet') }}"
                            class="btn btn-primary rounded-pill waves-effect waves-light me-2"
                        >
                            Add Carpet
                        </a>

                        <!-- CSV Download Button (shown only if user has permission) -->
                        @can('admin.all')
                            <a
                                href="{{ route('reports.carpets.downloadAll') }}"
                                class="btn btn-secondary rounded-pill waves-effect waves-light"
                            >
                                <i class="mdi mdi-download"></i> Download All Carpets CSV
                            </a>
                        @endcan
                    </div>
                </div>

            </div>
        </div>
        <!-- End page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                        <table id="carpetsTable" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Date Received</th>
                                    <th>Unique ID</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Phone Number</th>
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
            </div><!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->
</div> <!-- content -->

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#carpetsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('carpets.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'date_received', name: 'date_received' },
            { data: 'uniqueid', name: 'uniqueid' },
            { data: 'size', name: 'size' },
            { data: 'price', name: 'price' },
            { data: 'phone', name: 'phone' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'delivered', name: 'delivered' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']], // Sort by date_received descending
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No carpet records found",
            zeroRecords: "No matching records found"
        }
    });
});
</script>
@endpush
