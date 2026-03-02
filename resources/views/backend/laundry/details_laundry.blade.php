@extends('admin_master')
@section('admin')

<!-- Optional: Include jQuery if not already loaded in your master layout -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<div class="content">
    <!-- Start Content -->
    <div class="container-fluid" style="margin-top: 20px;">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">Details Laundry</a>
                            </li>
                        </ol>
                    </div>
                    <h4 class="page-title">Details Laundry</h4>
                </div>
            </div>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-lg-8 col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Tab Content: Settings -->
                        <div class="tab-pane" id="settings">
                            <form method="post" action="{{ route('laundry.update') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $laundry->id }}">

                                <h5 class="mb-4 text-uppercase">
                                    <i class="mdi mdi-account-circle me-1"></i> Details Laundry
                                </h5>

                                <div class="row">
                                    <!-- Customer Name -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Name</label>
                                            <p class="text-danger">{{ $laundry->name }}</p>
                                        </div>
                                    </div>
                                    <!-- Customer Phone Number -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Phone Number</label>
                                            <p class="text-danger">{{ $laundry->phone }}</p>
                                        </div>
                                    </div>
                                    <!-- Customer Location -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Location</label>
                                            <p class="text-danger">{{ $laundry->location }}</p>
                                        </div>
                                    </div>
                                    <!-- Customer Unique ID -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Unique ID</label>
                                            <p class="text-danger">{{ $laundry->unique_id }}</p>
                                        </div>
                                    </div>
                                    <!-- Date Received -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date Received</label>
                                            <p class="text-danger">{{ $laundry->date_received }}</p>
                                        </div>
                                    </div>
                                    <!-- Date Delivered -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date Delivered</label>
                                            <p class="text-danger">{{ $laundry->date_delivered ?? 'Not set' }}</p>
                                        </div>
                                    </div>
                                    <!-- Quantity -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Quantity</label>
                                            <p class="text-danger">{{ $laundry->quantity }}</p>
                                        </div>
                                    </div>
                                    <!-- Item Description -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Item Description</label>
                                            <p class="text-danger">{{ $laundry->item_description }}</p>
                                        </div>
                                    </div>
                                    <!-- Weight -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Weight</label>
                                            <p class="text-danger">{{ $laundry->weight }}</p>
                                        </div>
                                    </div>
                                    <!-- Price -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Price</label>
                                            <p class="text-danger">{{ $laundry->price }}</p>
                                        </div>
                                    </div>
                                    <!-- Discount -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Discount (KES)</label>
                                            <p class="text-danger">{{ $laundry->discount ?? 0 }}</p>
                                        </div>
                                    </div>
                                    <!-- Total Amount -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Total Amount</label>
                                            <p class="text-danger">{{ $laundry->total }}</p>
                                        </div>
                                    </div>
                                    <!-- Delivery Status -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Delivery Status</label>
                                            <p class="text-danger">{{ $laundry->delivered }}</p>
                                        </div>
                                    </div>
                                    <!-- Payment Status -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Payment Status</label>
                                            <p class="text-danger">{{ $laundry->payment_status }}</p>
                                        </div>
                                    </div>
                                    <!-- Transaction Code -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Transaction Code</label>
                                            <p class="text-danger">{{ $laundry->transaction_code ?? '-' }}</p>
                                        </div>
                                    </div>

                                    @if($laundry->payment_status !== 'Paid')
                                    <!-- M-Pesa Payment -->
                                    <div class="col-12 mt-3">
                                        <div class="alert alert-light border d-flex align-items-center justify-content-between" id="mpesa-section">
                                            <div>
                                                <strong>Amount Due: KES {{ number_format(($laundry->total ?? 0) - ($laundry->discount ?? 0), 2) }}</strong>
                                            </div>
                                            <button type="button" class="btn btn-success rounded-pill" id="mpesaPayBtn">
                                                <i class="mdi mdi-cellphone me-1"></i> Send M-Pesa Prompt
                                            </button>
                                        </div>
                                        <div id="mpesa-status" style="display:none;">
                                            <div class="alert alert-info" id="mpesa-pending" style="display:none;">
                                                <i class="mdi mdi-loading mdi-spin me-1"></i> <span id="mpesa-status-text">Waiting for customer to complete payment...</span>
                                            </div>
                                            <div class="alert alert-success" id="mpesa-success" style="display:none;">
                                                <i class="mdi mdi-check-circle me-1"></i> Payment received! Receipt: <strong id="mpesa-receipt"></strong>
                                            </div>
                                            <div class="alert alert-danger" id="mpesa-error" style="display:none;">
                                                <i class="mdi mdi-alert-circle me-1"></i> <span id="mpesa-error-text"></span>
                                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="mpesaRetryBtn">Retry</button>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                </div> <!-- End Row -->
                            </form>
                        </div>
                        <!-- End Tab Content -->
                    </div>
                </div> <!-- End Card -->
            </div> <!-- End Col -->
        </div> <!-- End Row -->
    </div> <!-- End Container -->
</div> <!-- End Content -->

@endsection

@push('scripts')
@if($laundry->payment_status !== 'Paid')
<script>
$(document).ready(function() {
    var pollingTimer = null;

    function sendMpesaPrompt() {
        $('#mpesaPayBtn').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin me-1"></i> Sending...');
        $('#mpesa-status').show();
        $('#mpesa-pending').show();
        $('#mpesa-success, #mpesa-error').hide();
        $('#mpesa-status-text').text('Sending payment prompt to {{ $laundry->phone }}...');

        $.ajax({
            url: '{{ route("mpesa.pay") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                service_type: 'laundry',
                service_id: {{ $laundry->id }},
                phone: '{{ $laundry->phone }}',
                amount: {{ ($laundry->total ?? 0) - ($laundry->discount ?? 0) }}
            },
            success: function(response) {
                if (response.success) {
                    $('#mpesa-status-text').text('Prompt sent! Waiting for customer to enter PIN...');
                    pollStatus(response.transaction_id);
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr) {
                showError('Failed to send M-Pesa prompt. Please try again.');
            }
        });
    }

    function pollStatus(transactionId) {
        var attempts = 0;
        var maxAttempts = 24;

        pollingTimer = setInterval(function() {
            attempts++;

            $.get('/mpesa/status/' + transactionId, function(response) {
                if (response.status === 'completed') {
                    clearInterval(pollingTimer);
                    $('#mpesa-pending').hide();
                    $('#mpesa-success').show();
                    $('#mpesa-receipt').text(response.mpesa_receipt_number);
                    $('#mpesaPayBtn').hide();
                    setTimeout(function() { location.reload(); }, 3000);
                } else if (response.status === 'failed' || response.status === 'cancelled') {
                    clearInterval(pollingTimer);
                    showError(response.result_desc || 'Payment was ' + response.status + '.');
                }
            });

            if (attempts >= maxAttempts) {
                clearInterval(pollingTimer);
                showError('Payment timed out. The customer may not have responded.');
            }
        }, 5000);
    }

    function showError(message) {
        $('#mpesa-pending').hide();
        $('#mpesa-error').show();
        $('#mpesa-error-text').text(message);
        $('#mpesaPayBtn').prop('disabled', false).html('<i class="mdi mdi-cellphone me-1"></i> Send M-Pesa Prompt');
    }

    $('#mpesaPayBtn').on('click', sendMpesaPrompt);
    $('#mpesaRetryBtn').on('click', function() {
        $('#mpesa-error').hide();
        sendMpesaPrompt();
    });
});
</script>
@endif
@endpush
