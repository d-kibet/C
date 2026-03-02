@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid" style="margin-top: 5px;">

        <div class="row mb-2">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">Order Details — {{ $order->order_number }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('orders.index') }}" class="btn btn-light btn-sm rounded-pill">
                            <i class="mdi mdi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            {{-- Order Info --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-uppercase mb-3"><i class="mdi mdi-account-circle me-1"></i> Order Info</h5>
                        <table class="table table-sm table-borderless">
                            <tr><td class="fw-semibold" style="width:40%">Order #</td><td>{{ $order->order_number }}</td></tr>
                            <tr><td class="fw-semibold">Type</td>
                                <td>
                                    @if($order->type === 'carpet')
                                        <span class="badge bg-primary">Carpet</span>
                                    @else
                                        <span class="badge bg-info">Laundry</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><td class="fw-semibold">Customer</td><td>{{ $order->name }}</td></tr>
                            <tr><td class="fw-semibold">Phone</td><td>{{ $order->phone }}</td></tr>
                            <tr><td class="fw-semibold">Location</td><td>{{ $order->location ?? '—' }}</td></tr>
                            <tr><td class="fw-semibold">Date Received</td><td>{{ $order->date_received?->format('d M Y') }}</td></tr>
                            <tr><td class="fw-semibold">Expected Delivery</td><td>{{ $order->date_delivered?->format('d M Y') ?? '—' }}</td></tr>
                            @if($order->notes)
                            <tr><td class="fw-semibold">Notes</td><td>{{ $order->notes }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- Payment Info --}}
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-uppercase mb-3"><i class="mdi mdi-cash me-1"></i> Payment</h5>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-semibold" style="width:40%">Status</td>
                                <td>
                                    @if($order->payment_status === 'Paid')
                                        <span class="badge bg-success fs-6">Paid</span>
                                    @elseif($order->payment_status === 'Partial')
                                        <span class="badge bg-warning text-dark fs-6">Partial</span>
                                    @else
                                        <span class="badge bg-danger fs-6">Not Paid</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><td class="fw-semibold">Transaction Code</td><td>{{ $order->transaction_code ?? '—' }}</td></tr>
                            <tr>
                                <td class="fw-semibold">Payment Date</td>
                                <td>
                                    @if($order->payment_date)
                                        {{ $order->payment_date->format('d M Y') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><td class="fw-semibold">Subtotal</td><td>KES {{ number_format($order->subtotal, 2) }}</td></tr>
                            <tr><td class="fw-semibold">Total Discount</td><td class="text-danger">- KES {{ number_format($order->items->sum('discount'), 2) }}</td></tr>
                            <tr class="table-success"><td class="fw-bold">Total</td><td class="fw-bold">KES {{ number_format($order->total, 2) }}</td></tr>
                        </table>

                        @if($order->payment_status !== 'Paid')
                        <hr>
                        <h6 class="text-uppercase mb-2"><i class="mdi mdi-cellphone me-1"></i> M-Pesa STK Push</h6>
                        <div id="mpesa-section">
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Phone</label>
                                <input type="text" id="mpesa-phone" class="form-control form-control-sm" value="{{ $order->phone }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Amount (KES)</label>
                                <input type="number" id="mpesa-amount" class="form-control form-control-sm" value="{{ (int) ceil($order->total) }}" min="1">
                            </div>
                            <button class="btn btn-success btn-sm w-100" id="mpesa-send-btn">
                                <i class="mdi mdi-send me-1"></i> Send M-Pesa Prompt
                            </button>
                            <div id="mpesa-feedback" class="mt-2"></div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title text-uppercase mb-0">
                                <i class="mdi mdi-format-list-numbered me-1"></i>
                                Items ({{ $order->deliveredCount() }}/{{ $order->itemCount() }} delivered)
                            </h5>
                        </div>

                        @if($order->items->isEmpty())
                        <p class="text-muted">No items found for this order.</p>
                        @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Unique ID</th>
                                        @if($order->type === 'carpet')
                                        <th>Size</th>
                                        <th>Rate</th>
                                        @else
                                        <th>Description</th>
                                        <th>Qty / Weight</th>
                                        @endif
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Total</th>
                                        <th>Delivery</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $i => $item)
                                    <tr id="item-row-{{ $item->id }}">
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $item->unique_id ?? '—' }}</td>
                                        @if($order->type === 'carpet')
                                        <td>{{ $item->size ?? '—' }}</td>
                                        <td>{{ $item->multiplier ?? '—' }}</td>
                                        @else
                                        <td>{{ $item->item_description ?? '—' }}</td>
                                        <td>{{ $item->quantity ?? '—' }} / {{ $item->weight ? $item->weight . 'kg' : '—' }}</td>
                                        @endif
                                        <td>{{ number_format($item->price, 2) }}</td>
                                        <td>{{ number_format($item->discount, 2) }}</td>
                                        <td>{{ number_format($item->item_total, 2) }}</td>
                                        <td>
                                            @if($item->delivered === 'Delivered')
                                                <span class="badge bg-success">Delivered</span>
                                                @if($item->date_delivered)
                                                <small class="d-block text-muted">{{ $item->date_delivered->format('d M Y') }}</small>
                                                @endif
                                            @else
                                                <span class="badge bg-warning text-dark">Not Delivered</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->delivered !== 'Delivered')
                                            <button type="button" class="btn btn-success btn-sm rounded-pill deliver-btn"
                                                data-item-id="{{ $item->id }}">
                                                <i class="mdi mdi-check me-1"></i> Deliver
                                            </button>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // Mark item as delivered
    $(document).on('click', '.deliver-btn', function() {
        var btn    = $(this);
        var itemId = btn.data('item-id');
        btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i>');

        $.ajax({
            url: '/order-items/' + itemId + '/deliver',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    var row = $('#item-row-' + itemId);
                    row.find('.badge').removeClass('bg-warning text-dark').addClass('bg-success').text('Delivered');
                    btn.replaceWith('<span class="text-muted">—</span>');

                    // Update counter
                    $('h5.card-title').filter(function() {
                        return $(this).text().includes('delivered');
                    }).text('Items (' + response.delivered_count + '/' + response.item_count + ' delivered)');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="mdi mdi-check me-1"></i> Deliver');
                alert('Failed to mark as delivered. Please try again.');
            }
        });
    });

    // M-Pesa send
    var pollingTimer = null;

    $('#mpesa-send-btn').on('click', function() {
        var phone  = $('#mpesa-phone').val().trim();
        var amount = $('#mpesa-amount').val();
        if (!phone || !amount) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin me-1"></i> Sending...');
        $('#mpesa-feedback').html('');

        $.ajax({
            url: '{{ route("mpesa.pay") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                service_type: 'order',
                service_id: {{ $order->id }},
                phone: phone,
                amount: amount
            },
            success: function(response) {
                if (response.success) {
                    $('#mpesa-feedback').html('<div class="alert alert-info py-2"><i class="mdi mdi-loading mdi-spin me-1"></i> Prompt sent! Waiting for payment...</div>');
                    btn.html('<i class="mdi mdi-clock-outline me-1"></i> Waiting...').prop('disabled', true);
                    pollStatus(response.transaction_id);
                } else {
                    $('#mpesa-feedback').html('<div class="alert alert-danger py-2">' + response.message + '</div>');
                    btn.prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Send M-Pesa Prompt');
                }
            },
            error: function() {
                $('#mpesa-feedback').html('<div class="alert alert-danger py-2">Request failed. Please try again.</div>');
                btn.prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Send M-Pesa Prompt');
            }
        });
    });

    function pollStatus(txId) {
        var attempts = 0;
        pollingTimer = setInterval(function() {
            attempts++;
            $.get('/mpesa/status/' + txId, function(res) {
                if (res.status === 'completed') {
                    clearInterval(pollingTimer);
                    $('#mpesa-section').html('<div class="alert alert-success"><i class="mdi mdi-check-circle me-1"></i> Payment received! Receipt: <strong>' + res.mpesa_receipt_number + '</strong>. Refresh to see updated status.</div>');
                } else if (res.status === 'failed' || res.status === 'cancelled') {
                    clearInterval(pollingTimer);
                    $('#mpesa-feedback').html('<div class="alert alert-danger py-2">' + (res.result_desc || 'Payment ' + res.status) + '</div>');
                    $('#mpesa-send-btn').prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Send M-Pesa Prompt');
                }
            });
            if (attempts >= 24) {
                clearInterval(pollingTimer);
                $('#mpesa-feedback').html('<div class="alert alert-warning py-2">Timed out waiting for payment. Customer may not have responded.</div>');
                $('#mpesa-send-btn').prop('disabled', false).html('<i class="mdi mdi-send me-1"></i> Retry');
            }
        }, 5000);
    }
});
</script>
@endpush
