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
                         @if(Auth::user()->can('admin.all'))
                        <a
                            href="{{ route('add.laundry') }}"
                            class="btn btn-primary rounded-pill waves-effect waves-light me-2"
                        >
                            Add Laundry
                        </a>
                        @endif

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

<!-- M-Pesa Quick Pay Modal -->
<div class="modal fade" id="mpesaModal" tabindex="-1" aria-labelledby="mpesaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="mpesaModalLabel"><i class="mdi mdi-cellphone me-1"></i> M-Pesa Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="mpesa-modal-info">
                    <p class="mb-3"><strong>Customer:</strong> <span id="mpesa-modal-name"></span></p>
                    <div class="mb-3">
                        <label class="form-label" for="mpesa-input-phone"><strong>Phone Number</strong></label>
                        <input type="text" class="form-control" id="mpesa-input-phone" placeholder="e.g. 0712345678">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="mpesa-input-amount"><strong>Amount (KES)</strong></label>
                        <input type="number" class="form-control" id="mpesa-input-amount" min="1" step="1">
                    </div>
                </div>
                <div id="mpesa-modal-pending" style="display:none;">
                    <div class="text-center py-3">
                        <div class="spinner-border text-success mb-2" role="status"></div>
                        <p class="mb-0" id="mpesa-modal-status-text">Sending prompt...</p>
                    </div>
                </div>
                <div id="mpesa-modal-success" style="display:none;">
                    <div class="alert alert-success mb-0">
                        <i class="mdi mdi-check-circle me-1"></i> Payment received! Receipt: <strong id="mpesa-modal-receipt"></strong>
                    </div>
                </div>
                <div id="mpesa-modal-error" style="display:none;">
                    <div class="alert alert-danger mb-0">
                        <i class="mdi mdi-alert-circle me-1"></i> <span id="mpesa-modal-error-text"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="mpesa-modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="mpesa-modal-send">
                    <i class="mdi mdi-send me-1"></i> Send Prompt
                </button>
            </div>
        </div>
    </div>
</div>

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

    // M-Pesa Quick Pay
    var mpesaPollingTimer = null;
    var currentMpesaData = {};

    $(document).on('click', '.mpesa-btn', function() {
        currentMpesaData = {
            service_type: $(this).data('service-type'),
            service_id: $(this).data('service-id'),
            phone: $(this).data('phone'),
            amount: $(this).data('amount'),
            name: $(this).data('name')
        };

        // Reset modal state
        $('#mpesa-modal-info').show();
        $('#mpesa-modal-pending, #mpesa-modal-success, #mpesa-modal-error').hide();
        $('#mpesa-modal-send').show().prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Send Prompt');
        $('#mpesa-modal-name').text(currentMpesaData.name);
        $('#mpesa-input-phone').val(currentMpesaData.phone);
        $('#mpesa-input-amount').val(Math.ceil(currentMpesaData.amount));

        $('#mpesaModal').modal('show');
    });

    $('#mpesa-modal-send').on('click', function() {
        var phone = $('#mpesa-input-phone').val().trim();
        var amount = $('#mpesa-input-amount').val();

        if (!phone) { $('#mpesa-input-phone').focus(); return; }
        if (!amount || amount < 1) { $('#mpesa-input-amount').focus(); return; }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin me-1"></i> Sending...');
        $('#mpesa-modal-pending').show();
        $('#mpesa-modal-error').hide();
        $('#mpesa-modal-status-text').text('Sending prompt to ' + phone + '...');

        $.ajax({
            url: '{{ route("mpesa.pay") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                service_type: currentMpesaData.service_type,
                service_id: currentMpesaData.service_id,
                phone: phone,
                amount: amount
            },
            success: function(response) {
                if (response.success) {
                    $('#mpesa-modal-status-text').text('Prompt sent! Waiting for customer to enter PIN...');
                    btn.hide();
                    pollMpesaStatus(response.transaction_id);
                } else {
                    showMpesaError(response.message);
                    btn.prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Retry');
                }
            },
            error: function() {
                showMpesaError('Failed to send M-Pesa prompt. Please try again.');
                btn.prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Retry');
            }
        });
    });

    function pollMpesaStatus(transactionId) {
        var attempts = 0;
        mpesaPollingTimer = setInterval(function() {
            attempts++;
            $.get('/mpesa/status/' + transactionId, function(response) {
                if (response.status === 'completed') {
                    clearInterval(mpesaPollingTimer);
                    $('#mpesa-modal-pending').hide();
                    $('#mpesa-modal-success').show();
                    $('#mpesa-modal-receipt').text(response.mpesa_receipt_number);
                    setTimeout(function() {
                        $('#mpesaModal').modal('hide');
                        $('#laundryTable').DataTable().ajax.reload(null, false);
                    }, 2500);
                } else if (response.status === 'failed' || response.status === 'cancelled') {
                    clearInterval(mpesaPollingTimer);
                    showMpesaError(response.result_desc || 'Payment was ' + response.status + '.');
                    $('#mpesa-modal-send').show().prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Retry');
                }
            });
            if (attempts >= 24) {
                clearInterval(mpesaPollingTimer);
                showMpesaError('Payment timed out. The customer may not have responded.');
                $('#mpesa-modal-send').show().prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Retry');
            }
        }, 5000);
    }

    function showMpesaError(message) {
        $('#mpesa-modal-pending').hide();
        $('#mpesa-modal-error').show();
        $('#mpesa-modal-error-text').text(message);
    }

    // Clear polling when modal is closed
    $('#mpesaModal').on('hidden.bs.modal', function() {
        if (mpesaPollingTimer) clearInterval(mpesaPollingTimer);
    });
});
</script>
@endpush
