@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid" style="margin-top: 5px;">

        <div class="row mb-2">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">Edit Order — {{ $order->order_number }}</h4>
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-secondary btn-sm rounded-pill">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Details
                    </a>
                </div>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('orders.update', $order->id) }}" id="orderForm">
            @csrf
            @method('PUT')

            {{-- Order type display (read-only on edit) --}}
            <input type="hidden" name="type" value="{{ $order->type }}">

            {{-- ── CUSTOMER INFO ── --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="mb-3 text-uppercase">
                        <i class="mdi mdi-account-circle me-1"></i> Customer Info
                        <span class="ms-2 badge {{ $order->type === 'carpet' ? 'bg-primary' : 'bg-info' }}">{{ ucfirst($order->type) }} Order</span>
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control bg-light @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $order->phone) }}" readonly>
                                @error('phone')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Customer Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $order->name) }}">
                                @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control"
                                    value="{{ old('location', $order->location) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Date Received</label>
                                <input type="date" name="date_received" class="form-control"
                                    value="{{ old('date_received', $order->date_received?->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Expected Delivery Date</label>
                                <input type="date" name="date_delivered" class="form-control"
                                    value="{{ old('date_delivered', $order->date_delivered?->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" id="payment_status" class="form-select">
                                    @foreach(['Not Paid','Partial','Paid'] as $status)
                                    <option value="{{ $status }}" {{ old('payment_status', $order->payment_status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Transaction Code</label>
                                <input type="text" name="transaction_code" id="transaction_code" class="form-control"
                                    value="{{ old('transaction_code', $order->transaction_code) }}" list="tx_codes">
                                <datalist id="tx_codes"><option value="Cash"></datalist>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control"
                                    value="{{ old('notes', $order->notes) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── ITEMS ── --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-uppercase"><i class="mdi mdi-format-list-numbered me-1"></i> Items</h5>
                    </div>

                    <div id="items-container">
                        @foreach($order->items as $i => $item)
                        <div class="item-row card border mb-3" data-index="{{ $i }}">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    @if($order->type === 'carpet')
                                    <span class="badge bg-primary"><i class="mdi mdi-layers-outline me-1"></i> Carpet #<span class="item-num">{{ $i + 1 }}</span></span>
                                    @else
                                    <span class="badge bg-info"><i class="fa-solid fa-shirt me-1"></i> Laundry #<span class="item-num">{{ $i + 1 }}</span></span>
                                    @endif
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="mdi mdi-close"></i></button>
                                </div>
                                @if($order->type === 'carpet')
                                <div class="row">
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm">Unique ID</label>
                                        <input type="text" name="items[{{ $i }}][unique_id]" class="form-control form-control-sm bg-light" value="{{ $item->unique_id }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm">Size</label>
                                        <input type="text" name="items[{{ $i }}][size]" class="form-control form-control-sm item-size bg-light" value="{{ $item->size }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm">Rate (KES/m²)</label>
                                        <input type="number" name="items[{{ $i }}][multiplier]" class="form-control form-control-sm item-multiplier" value="{{ $item->multiplier ?? 30 }}" step="any" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm">Price (KES)</label>
                                        <input type="number" name="items[{{ $i }}][price]" class="form-control form-control-sm item-price" value="{{ $item->price }}" readonly step="any" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm">Discount (KES)</label>
                                        <input type="number" name="items[{{ $i }}][discount]" class="form-control form-control-sm item-discount" value="{{ $item->discount }}" step="any" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm">Total (KES)</label>
                                        <input type="text" class="form-control form-control-sm item-total-display bg-light fw-bold" readonly value="KES {{ number_format($item->item_total, 2) }}">
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label class="form-label form-label-sm">Delivery Status</label>
                                        <select name="items[{{ $i }}][delivered]" class="form-select form-select-sm">
                                            <option value="Not Delivered" {{ $item->delivered === 'Not Delivered' ? 'selected' : '' }}>Not Delivered</option>
                                            <option value="Delivered" {{ $item->delivered === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label form-label-sm">Date Delivered</label>
                                        <input type="date" name="items[{{ $i }}][date_delivered]" class="form-control form-control-sm" value="{{ $item->date_delivered?->format('Y-m-d') }}">
                                    </div>
                                </div>
                                @else
                                <div class="row">
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm">Unique ID</label>
                                        <input type="text" name="items[{{ $i }}][unique_id]" class="form-control form-control-sm bg-light" value="{{ $item->unique_id }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label form-label-sm">Description</label>
                                        <input type="text" name="items[{{ $i }}][item_description]" class="form-control form-control-sm" value="{{ $item->item_description }}">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label form-label-sm">Qty</label>
                                        <input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm" value="{{ $item->quantity }}" min="1">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label form-label-sm">Wt (kg)</label>
                                        <input type="number" name="items[{{ $i }}][weight]" class="form-control form-control-sm" value="{{ $item->weight }}" step="0.1" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm">Price (KES)</label>
                                        <input type="number" name="items[{{ $i }}][price]" class="form-control form-control-sm item-price" value="{{ $item->price }}" step="any" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm">Discount (KES)</label>
                                        <input type="number" name="items[{{ $i }}][discount]" class="form-control form-control-sm item-discount" value="{{ $item->discount }}" step="any" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label form-label-sm">Total (KES)</label>
                                        <input type="text" class="form-control form-control-sm item-total-display bg-light fw-bold" readonly value="KES {{ number_format($item->item_total, 2) }}">
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label class="form-label form-label-sm">Delivery Status</label>
                                        <select name="items[{{ $i }}][delivered]" class="form-select form-select-sm">
                                            <option value="Not Delivered" {{ $item->delivered === 'Not Delivered' ? 'selected' : '' }}>Not Delivered</option>
                                            <option value="Delivered" {{ $item->delivered === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label form-label-sm">Date Delivered</label>
                                        <input type="date" name="items[{{ $i }}][date_delivered]" class="form-control form-control-sm" value="{{ $item->date_delivered?->format('Y-m-d') }}">
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill" id="addItemBtn">
                            <i class="mdi mdi-plus me-1"></i> Add Item
                        </button>
                    </div>

                    <div class="row mt-3 justify-content-end">
                        <div class="col-md-4">
                            <table class="table table-sm mb-0">
                                <tr><td class="text-end fw-semibold">Subtotal:</td><td class="text-end" id="display-subtotal">KES 0.00</td></tr>
                                <tr><td class="text-end fw-semibold">Total Discount:</td><td class="text-end text-danger" id="display-discount">- KES 0.00</td></tr>
                                <tr class="table-success"><td class="text-end fw-bold">Total:</td><td class="text-end fw-bold" id="display-total">KES 0.00</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-light me-2">Cancel</a>
                <button type="submit" class="btn btn-success waves-effect waves-light">
                    <i class="mdi mdi-content-save me-1"></i> Save Changes
                </button>
            </div>
        </form>

    </div>
</div>

{{-- Templates for adding new items --}}
<template id="carpet-item-template">
    <div class="item-row card border mb-3" data-index="__INDEX__">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-primary"><i class="mdi mdi-layers-outline me-1"></i> Carpet #<span class="item-num"></span></span>
                <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="mdi mdi-close"></i></button>
            </div>
            <div class="row">
                <div class="col-md-2"><label class="form-label form-label-sm">Unique ID</label><input type="text" name="items[__INDEX__][unique_id]" class="form-control form-control-sm bg-light" readonly></div>
                <div class="col-md-2"><label class="form-label form-label-sm">Size</label><input type="text" name="items[__INDEX__][size]" class="form-control form-control-sm item-size bg-light" readonly placeholder="e.g. 3x4"></div>
                <div class="col-md-2"><label class="form-label form-label-sm">Rate (KES/m²)</label><input type="number" name="items[__INDEX__][multiplier]" class="form-control form-control-sm item-multiplier" value="30" step="any" min="0"></div>
                <div class="col-md-2"><label class="form-label form-label-sm">Price (KES)</label><input type="number" name="items[__INDEX__][price]" class="form-control form-control-sm item-price" readonly step="any" min="0"></div>
                <div class="col-md-2"><label class="form-label form-label-sm">Discount (KES)</label><input type="number" name="items[__INDEX__][discount]" class="form-control form-control-sm item-discount" value="0" step="any" min="0"></div>
                <div class="col-md-2"><label class="form-label form-label-sm">Total (KES)</label><input type="text" class="form-control form-control-sm item-total-display bg-light fw-bold" readonly></div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3"><label class="form-label form-label-sm">Delivery Status</label><select name="items[__INDEX__][delivered]" class="form-select form-select-sm"><option value="Not Delivered" selected>Not Delivered</option><option value="Delivered">Delivered</option></select></div>
                <div class="col-md-3"><label class="form-label form-label-sm">Date Delivered</label><input type="date" name="items[__INDEX__][date_delivered]" class="form-control form-control-sm"></div>
            </div>
        </div>
    </div>
</template>

<template id="laundry-item-template">
    <div class="item-row card border mb-3" data-index="__INDEX__">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-info"><i class="fa-solid fa-shirt me-1"></i> Laundry #<span class="item-num"></span></span>
                <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="mdi mdi-close"></i></button>
            </div>
            <div class="row">
                <div class="col-md-2"><label class="form-label form-label-sm">Unique ID</label><input type="text" name="items[__INDEX__][unique_id]" class="form-control form-control-sm bg-light" readonly></div>
                <div class="col-md-3"><label class="form-label form-label-sm">Description</label><input type="text" name="items[__INDEX__][item_description]" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label form-label-sm">Qty</label><input type="number" name="items[__INDEX__][quantity]" class="form-control form-control-sm" value="1" min="1"></div>
                <div class="col-md-1"><label class="form-label form-label-sm">Wt (kg)</label><input type="number" name="items[__INDEX__][weight]" class="form-control form-control-sm" step="0.1" min="0"></div>
                <div class="col-md-2"><label class="form-label form-label-sm">Price (KES)</label><input type="number" name="items[__INDEX__][price]" class="form-control form-control-sm item-price" step="any" min="0"></div>
                <div class="col-md-2"><label class="form-label form-label-sm">Discount (KES)</label><input type="number" name="items[__INDEX__][discount]" class="form-control form-control-sm item-discount" value="0" step="any" min="0"></div>
                <div class="col-md-2"><label class="form-label form-label-sm">Total (KES)</label><input type="text" class="form-control form-control-sm item-total-display bg-light fw-bold" readonly></div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3"><label class="form-label form-label-sm">Delivery Status</label><select name="items[__INDEX__][delivered]" class="form-select form-select-sm"><option value="Not Delivered" selected>Not Delivered</option><option value="Delivered">Delivered</option></select></div>
                <div class="col-md-3"><label class="form-label form-label-sm">Date Delivered</label><input type="date" name="items[__INDEX__][date_delivered]" class="form-control form-control-sm"></div>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
(function() {
    var orderType  = '{{ $order->type }}';
    var itemIndex  = {{ $order->items->count() }};

    function recalcTotals() {
        var subtotal = 0, disc = 0;
        document.querySelectorAll('#items-container .item-row').forEach(function(row) {
            subtotal += parseFloat(row.querySelector('.item-price')?.value) || 0;
            disc     += parseFloat(row.querySelector('.item-discount')?.value) || 0;
        });
        var total = Math.max(0, subtotal - disc);
        document.getElementById('display-subtotal').textContent = 'KES ' + subtotal.toFixed(2);
        document.getElementById('display-discount').textContent = '- KES ' + disc.toFixed(2);
        document.getElementById('display-total').textContent    = 'KES ' + total.toFixed(2);
    }

    function bindRowEvents(row) {
        row.querySelector('.remove-item-btn').addEventListener('click', function() {
            row.remove();
            updateItemNumbers();
            recalcTotals();
        });

        if (orderType === 'carpet') {
            var sizeEl  = row.querySelector('.item-size');
            var multEl  = row.querySelector('.item-multiplier');
            var priceEl = row.querySelector('.item-price');
            var discEl  = row.querySelector('.item-discount');
            var totEl   = row.querySelector('.item-total-display');

            function calcCarpet() {
                var sizeVal = sizeEl ? sizeEl.value.trim() : '';
                var mult    = parseFloat(multEl ? multEl.value : 30) || 30;
                var size    = 0;
                if (/[x*]/i.test(sizeVal)) {
                    var p = sizeVal.split(/[*x]/i);
                    if (p.length === 2) { var a = parseFloat(p[0]), b = parseFloat(p[1]); if (!isNaN(a) && !isNaN(b)) size = a * b; }
                } else { size = parseFloat(sizeVal) || 0; }
                var base = size * mult;
                var disc = parseFloat(discEl ? discEl.value : 0) || 0;
                if (priceEl) priceEl.value = base.toFixed(1);
                if (totEl)   totEl.value   = 'KES ' + Math.max(0, base - disc).toFixed(2);
                recalcTotals();
            }

            if (sizeEl)  sizeEl.addEventListener('input', calcCarpet);
            if (multEl)  multEl.addEventListener('input', calcCarpet);
            if (discEl)  discEl.addEventListener('input', calcCarpet);
        } else {
            var priceEl = row.querySelector('.item-price');
            var discEl  = row.querySelector('.item-discount');
            var totEl   = row.querySelector('.item-total-display');

            function calcLaundry() {
                var price = parseFloat(priceEl ? priceEl.value : 0) || 0;
                var disc  = parseFloat(discEl ? discEl.value : 0) || 0;
                if (totEl) totEl.value = 'KES ' + Math.max(0, price - disc).toFixed(2);
                recalcTotals();
            }

            if (priceEl) priceEl.addEventListener('input', calcLaundry);
            if (discEl)  discEl.addEventListener('input', calcLaundry);
        }
    }

    function updateItemNumbers() {
        document.querySelectorAll('#items-container .item-row').forEach(function(row, i) {
            var el = row.querySelector('.item-num');
            if (el) el.textContent = i + 1;
        });
    }

    function addItem() {
        var tplId  = orderType === 'carpet' ? 'carpet-item-template' : 'laundry-item-template';
        var tpl    = document.getElementById(tplId);
        var html   = tpl.content.cloneNode(true).querySelector('.item-row').outerHTML;
        html = html.replace(/__INDEX__/g, itemIndex);

        var container = document.getElementById('items-container');
        var wrapper   = document.createElement('div');
        wrapper.innerHTML = html;
        var row = wrapper.firstElementChild;

        var numEl = row.querySelector('.item-num');
        if (numEl) numEl.textContent = container.querySelectorAll('.item-row').length + 1;

        bindRowEvents(row);
        container.appendChild(row);
        itemIndex++;
        recalcTotals();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Bind events to existing pre-populated rows
        document.querySelectorAll('#items-container .item-row').forEach(bindRowEvents);
        recalcTotals();

        document.getElementById('addItemBtn').addEventListener('click', addItem);

        // Transaction code toggle
        var payEl = document.getElementById('payment_status');
        var txEl  = document.getElementById('transaction_code');
        function toggleTx() { txEl.disabled = payEl.value === 'Not Paid'; }
        toggleTx();
        payEl.addEventListener('change', toggleTx);
    });
})();
</script>
@endpush
