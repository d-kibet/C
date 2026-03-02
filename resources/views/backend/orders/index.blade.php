@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">All Orders</h4>
                    <div class="page-title-right d-flex align-items-center gap-2">
                        @can('carpet.add')
                        <a href="{{ route('orders.create') }}" class="btn btn-primary rounded-pill waves-effect waves-light">
                            <i class="mdi mdi-plus me-1"></i> New Order
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive" style="overflow: visible;">
                            <table id="ordersTable" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Unique ID(s)</th>
                                        <th>Phone</th>
                                        <th>Date Received</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Payment Date</th>
                                        <th>Delivery</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- M-Pesa Quick Pay Modal -->
<div class="modal fade" id="mpesaModal" tabindex="-1" aria-labelledby="mpesaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="mdi mdi-cellphone me-1"></i> M-Pesa Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="mpesa-modal-info">
                    <p class="mb-3"><strong>Order:</strong> <span id="mpesa-modal-name"></span></p>
                    <div class="mb-3">
                        <label class="form-label"><strong>Phone Number</strong></label>
                        <input type="text" class="form-control" id="mpesa-input-phone" placeholder="e.g. 0712345678">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Amount (KES)</strong></label>
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
            <div class="modal-footer">
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
    var table = $('#ordersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: "{{ route('orders.data') }}", type: 'GET' },
        columns: [
            { data: 'unique_ids',    name: 'unique_ids',    orderable: false, searchable: false },
            { data: 'phone',         name: 'phone' },
            { data: 'date_received', name: 'date_received' },
            { data: 'items_count',   name: 'items_count',   orderable: false, searchable: false },
            { data: 'total',         name: 'total',         orderable: false, searchable: false },
            { data: 'payment_status',name: 'payment_status',orderable: false },
            { data: 'payment_date',  name: 'payment_date',  orderable: false, searchable: false },
            { data: 'delivery',      name: 'delivery',      orderable: false, searchable: false },
            { data: 'actions',       name: 'actions',       orderable: false, searchable: false }
        ],
        order: [[2, 'desc']],
        pageLength: 25,
        responsive: true,
        drawCallback: function() {
            // Re-init tooltips after every DataTable redraw
            $('[data-bs-toggle="tooltip"]').each(function() {
                new bootstrap.Tooltip(this, { trigger: 'hover' });
            });
        },
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No orders found",
            zeroRecords: "No matching orders found"
        }
    });

    // Delete order
    $(document).on('click', '.delete-order-btn', function(e) {
        e.preventDefault();
        var id    = $(this).data('id');
        var label = $(this).data('label');
        if (confirm('Delete order ' + label + '? This cannot be undone.')) {
            $('#del-' + id).submit();
        }
    });

    // M-Pesa Quick Pay
    var mpesaPollingTimer = null;
    var currentMpesaData = {};

    $(document).on('click', '.mpesa-btn', function() {
        currentMpesaData = {
            service_type: $(this).data('service-type'),
            service_id:   $(this).data('service-id'),
            phone:        $(this).data('phone'),
            amount:       $(this).data('amount'),
            name:         $(this).data('name')
        };
        $('#mpesa-modal-info').show();
        $('#mpesa-modal-pending, #mpesa-modal-success, #mpesa-modal-error').hide();
        $('#mpesa-modal-send').show().prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Send Prompt');
        $('#mpesa-modal-name').text(currentMpesaData.name);
        $('#mpesa-input-phone').val(currentMpesaData.phone);
        $('#mpesa-input-amount').val(Math.ceil(currentMpesaData.amount));
        $('#mpesaModal').modal('show');
    });

    $('#mpesa-modal-send').on('click', function() {
        var phone  = $('#mpesa-input-phone').val().trim();
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
                service_id:   currentMpesaData.service_id,
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
                showMpesaError('Failed to send prompt. Please try again.');
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
                        table.ajax.reload(null, false);
                    }, 2500);
                } else if (response.status === 'failed' || response.status === 'cancelled') {
                    clearInterval(mpesaPollingTimer);
                    showMpesaError(response.result_desc || 'Payment ' + response.status + '.');
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

    $('#mpesaModal').on('hidden.bs.modal', function() {
        if (mpesaPollingTimer) clearInterval(mpesaPollingTimer);
    });
});
</script>
@endpush
