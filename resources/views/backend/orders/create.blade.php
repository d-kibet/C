@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid" style="margin-top: 5px;">

        <div class="row mb-2">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">New Order</h4>
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary btn-sm rounded-pill">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
            @csrf

            {{-- ── ORDER TYPE ── --}}
            @php
                $canCarpet  = auth()->user()->can('carpet.all');
                $canLaundry = auth()->user()->can('laundry.all');
                // Determine default: if old value exists use it, else user's permitted type
                $defaultType = old('type', $canCarpet ? 'carpet' : 'laundry');
            @endphp

            <div class="card mb-3">
                <div class="card-body pb-2">
                    <h5 class="mb-3 text-uppercase"><i class="mdi mdi-format-list-bulleted me-1"></i> Order Type</h5>
                    <div class="d-flex gap-4">
                        @if($canCarpet)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="type_carpet" value="carpet" {{ $defaultType === 'carpet' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="type_carpet"><i class="mdi mdi-layers-outline me-1"></i> Carpet</label>
                        </div>
                        @endif
                        @if($canLaundry)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="type_laundry" value="laundry" {{ $defaultType === 'laundry' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="type_laundry"><i class="fa-solid fa-shirt me-1"></i> Laundry</label>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── CUSTOMER INFO ── --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="mb-3 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> Customer Info</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone Number
                                    <span id="customer-status" class="badge badge-soft-info ms-2" style="display:none;">
                                        <i class="ri-user-star-line"></i> Returning Customer
                                    </span>
                                </label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}" placeholder="e.g. 0712345678">
                                <small id="phone-loading" class="text-muted" style="display:none;"><i class="ri-loader-4-line"></i> Looking up customer...</small>
                                @error('phone')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Customer Name</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" id="location" class="form-control" value="{{ old('location') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Date Received</label>
                                <input type="date" name="date_received" class="form-control @error('date_received') is-invalid @enderror"
                                    value="{{ old('date_received', date('Y-m-d')) }}">
                                @error('date_received')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Expected Delivery Date</label>
                                <input type="date" name="date_delivered" class="form-control" value="{{ old('date_delivered') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" id="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
                                    <option value="Not Paid" {{ old('payment_status','Not Paid') === 'Not Paid' ? 'selected' : '' }}>Not Paid</option>
                                    <option value="Partial"  {{ old('payment_status') === 'Partial'  ? 'selected' : '' }}>Partial</option>
                                    <option value="Paid"     {{ old('payment_status') === 'Paid'     ? 'selected' : '' }}>Paid</option>
                                </select>
                                @error('payment_status')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Transaction Code</label>
                                <input type="text" name="transaction_code" id="transaction_code" class="form-control"
                                    value="{{ old('transaction_code') }}" list="transaction_codes_list">
                                <datalist id="transaction_codes_list"><option value="Cash"></datalist>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Any additional notes">
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
                        <div id="carpet-base-container" style="display:none;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="mdi mdi-tag-outline me-1"></i> Base ID</span>
                                <input type="text" id="carpet-base-id" class="form-control" placeholder="e.g. RA12" style="width:110px;">
                            </div>
                        </div>
                    </div>

                    <div id="items-container"></div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill" id="addItemBtn">
                            <i class="mdi mdi-plus me-1"></i> Add Item
                        </button>
                    </div>

                    {{-- Totals --}}
                    <div class="row mt-3 justify-content-end">
                        <div class="col-md-4">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-end fw-semibold">Subtotal:</td>
                                    <td class="text-end" id="display-subtotal">KES 0.00</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-semibold">Total Discount:</td>
                                    <td class="text-end text-danger" id="display-discount">- KES 0.00</td>
                                </tr>
                                <tr class="table-success">
                                    <td class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold" id="display-total">KES 0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="{{ route('orders.index') }}" class="btn btn-light me-2">Cancel</a>
                <button type="submit" class="btn btn-success waves-effect waves-light">
                    <i class="mdi mdi-content-save me-1"></i> Save Order
                </button>
            </div>
        </form>

    </div>
</div>

{{-- Item row templates (hidden) --}}
<template id="carpet-item-template">
    <div class="item-row card border mb-3" data-index="__INDEX__">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-primary"><i class="mdi mdi-layers-outline me-1"></i> Carpet #<span class="item-num">1</span></span>
                <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="mdi mdi-close"></i></button>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Unique ID</label>
                    <input type="text" name="items[__INDEX__][unique_id]" class="form-control form-control-sm" placeholder="e.g. RA1A">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Size</label>
                    <input type="text" name="items[__INDEX__][size]" class="form-control form-control-sm item-size" placeholder="e.g. 3x4">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Rate (KES/m²)</label>
                    <input type="number" name="items[__INDEX__][multiplier]" class="form-control form-control-sm item-multiplier" value="30" step="any" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Price (KES)</label>
                    <input type="number" name="items[__INDEX__][price]" class="form-control form-control-sm item-price" readonly step="any" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Discount (KES)</label>
                    <input type="number" name="items[__INDEX__][discount]" class="form-control form-control-sm item-discount" value="0" step="any" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Total (KES)</label>
                    <input type="text" class="form-control form-control-sm item-total-display bg-light fw-bold" readonly>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="laundry-item-template">
    <div class="item-row card border mb-3" data-index="__INDEX__">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-info"><i class="fa-solid fa-shirt me-1"></i> Laundry #<span class="item-num">1</span></span>
                <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="mdi mdi-close"></i></button>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Unique ID</label>
                    <input type="text" name="items[__INDEX__][unique_id]" class="form-control form-control-sm" placeholder="e.g. L-001">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Item Description</label>
                    <input type="text" name="items[__INDEX__][item_description]" class="form-control form-control-sm" placeholder="e.g. Curtains">
                </div>
                <div class="col-md-1">
                    <label class="form-label form-label-sm">Qty</label>
                    <input type="number" name="items[__INDEX__][quantity]" class="form-control form-control-sm" value="1" min="1">
                </div>
                <div class="col-md-1">
                    <label class="form-label form-label-sm">Wt (kg)</label>
                    <input type="number" name="items[__INDEX__][weight]" class="form-control form-control-sm" step="0.1" min="0" placeholder="0.0">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Price (KES)</label>
                    <input type="number" name="items[__INDEX__][price]" class="form-control form-control-sm item-price" step="any" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Discount (KES)</label>
                    <input type="number" name="items[__INDEX__][discount]" class="form-control form-control-sm item-discount" value="0" step="any" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Total (KES)</label>
                    <input type="text" class="form-control form-control-sm item-total-display bg-light fw-bold" readonly>
                </div>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
(function() {
    var itemIndex = 0;

    function getType() {
        return document.querySelector('input[name="type"]:checked')?.value || 'carpet';
    }

    function getTemplate() {
        return getType() === 'carpet' ? 'carpet-item-template' : 'laundry-item-template';
    }

    function indexToLetter(n) {
        // 0=A, 1=B … 25=Z, 26=AA, 27=AB …
        var letters = '';
        n++;
        while (n > 0) {
            n--;
            letters = String.fromCharCode(65 + (n % 26)) + letters;
            n = Math.floor(n / 26);
        }
        return letters;
    }

    function getBase() {
        var el = document.getElementById('carpet-base-id');
        return el ? el.value.trim() : '';
    }

    function syncBaseContainer() {
        var el = document.getElementById('carpet-base-container');
        if (el) el.style.display = getType() === 'carpet' ? '' : 'none';
    }

    function addItem(prefill) {
        var template = document.getElementById(getTemplate());
        var clone    = template.content.cloneNode(true);
        var html     = clone.querySelector('.item-row').outerHTML;
        html = html.replace(/__INDEX__/g, itemIndex);

        var container = document.getElementById('items-container');
        var wrapper   = document.createElement('div');
        wrapper.innerHTML = html;
        var row = wrapper.firstElementChild;

        // Store creation index so base-change handler can derive the letter
        row.setAttribute('data-index', itemIndex);

        // Set item number label
        row.querySelector('.item-num').textContent = container.querySelectorAll('.item-row').length + 1;

        // Pre-fill values if provided
        if (prefill) {
            var fields = ['unique_id','size','multiplier','price','discount','item_description','quantity','weight'];
            fields.forEach(function(f) {
                var el = row.querySelector('[name="items[' + itemIndex + '][' + f + ']"]');
                if (el && prefill[f] != null) el.value = prefill[f];
            });
        }

        // Auto-assign unique_id for carpet when no prefill (or prefill has no unique_id)
        if (getType() === 'carpet' && !(prefill && prefill.unique_id)) {
            var base    = getBase();
            var uidEl   = row.querySelector('[name="items[' + itemIndex + '][unique_id]"]');
            if (uidEl && base) uidEl.value = base + indexToLetter(itemIndex);
        }

        // Remove button
        row.querySelector('.remove-item-btn').addEventListener('click', function() {
            row.remove();
            updateItemNumbers();
            recalcTotals();
        });

        // Carpet-specific: auto-calculate price
        if (getType() === 'carpet') {
            var sizeInput       = row.querySelector('.item-size');
            var multiplierInput = row.querySelector('.item-multiplier');
            var priceInput      = row.querySelector('.item-price');
            var discountInput   = row.querySelector('.item-discount');
            var totalDisplay    = row.querySelector('.item-total-display');

            function calcCarpetPrice() {
                var sizeVal    = sizeInput ? sizeInput.value.trim() : '';
                var multiplier = parseFloat(multiplierInput ? multiplierInput.value : 30) || 30;
                var size       = 0;

                if (/[x*]/i.test(sizeVal)) {
                    var parts = sizeVal.split(/[*x]/i);
                    if (parts.length === 2) {
                        var n1 = parseFloat(parts[0]), n2 = parseFloat(parts[1]);
                        if (!isNaN(n1) && !isNaN(n2)) size = n1 * n2;
                    }
                } else {
                    size = parseFloat(sizeVal) || 0;
                }

                var basePrice = size * multiplier;
                var discount  = parseFloat(discountInput ? discountInput.value : 0) || 0;
                var total     = Math.max(0, basePrice - discount);

                if (priceInput) priceInput.value = basePrice.toFixed(1);
                if (totalDisplay) totalDisplay.value = 'KES ' + total.toFixed(2);
                recalcTotals();
            }

            if (sizeInput)       sizeInput.addEventListener('input', calcCarpetPrice);
            if (multiplierInput) multiplierInput.addEventListener('input', calcCarpetPrice);
            if (discountInput)   discountInput.addEventListener('input', calcCarpetPrice);
            if (prefill)         calcCarpetPrice();
        } else {
            // Laundry: price is manually entered, discount reduces total
            var priceInput   = row.querySelector('.item-price');
            var discountInput = row.querySelector('.item-discount');
            var totalDisplay  = row.querySelector('.item-total-display');

            function calcLaundryTotal() {
                var price    = parseFloat(priceInput ? priceInput.value : 0) || 0;
                var discount = parseFloat(discountInput ? discountInput.value : 0) || 0;
                var total    = Math.max(0, price - discount);
                if (totalDisplay) totalDisplay.value = 'KES ' + total.toFixed(2);
                recalcTotals();
            }

            if (priceInput)   priceInput.addEventListener('input', calcLaundryTotal);
            if (discountInput) discountInput.addEventListener('input', calcLaundryTotal);
            if (prefill)      calcLaundryTotal();
        }

        container.appendChild(row);
        itemIndex++;
        recalcTotals();
    }

    function updateItemNumbers() {
        var rows = document.querySelectorAll('#items-container .item-row');
        rows.forEach(function(row, i) {
            var numEl = row.querySelector('.item-num');
            if (numEl) numEl.textContent = i + 1;
        });
    }

    function recalcTotals() {
        var subtotal = 0, discountTotal = 0;
        document.querySelectorAll('#items-container .item-row').forEach(function(row) {
            var price    = parseFloat(row.querySelector('.item-price')?.value) || 0;
            var discount = parseFloat(row.querySelector('.item-discount')?.value) || 0;
            subtotal     += price;
            discountTotal += discount;
        });
        var total = Math.max(0, subtotal - discountTotal);

        document.getElementById('display-subtotal').textContent = 'KES ' + subtotal.toFixed(2);
        document.getElementById('display-discount').textContent = '- KES ' + discountTotal.toFixed(2);
        document.getElementById('display-total').textContent    = 'KES ' + total.toFixed(2);
    }

    function resetItems() {
        // Clear all existing items and add one fresh item of the current type
        document.getElementById('items-container').innerHTML = '';
        itemIndex = 0;
        addItem();
    }

    // Add first item on page load
    document.addEventListener('DOMContentLoaded', function() {
        syncBaseContainer();
        addItem();

        document.getElementById('addItemBtn').addEventListener('click', function() { addItem(); });

        // When type radio changes → clear items and add the correct type
        document.querySelectorAll('input[name="type"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                syncBaseContainer();
                resetItems();
            });
        });

        // Base ID change → update all existing carpet unique_id fields
        var baseInput = document.getElementById('carpet-base-id');
        if (baseInput) {
            baseInput.addEventListener('input', function() {
                var base = this.value.trim();
                document.querySelectorAll('#items-container .item-row').forEach(function(row) {
                    var idx   = parseInt(row.getAttribute('data-index')) || 0;
                    var uidEl = row.querySelector('[name*="[unique_id]"]');
                    if (uidEl) uidEl.value = base ? base + indexToLetter(idx) : '';
                });
            });
        }

        // Toggle transaction code
        var payStatus = document.getElementById('payment_status');
        var txCode    = document.getElementById('transaction_code');
        function toggleTxCode() {
            txCode.disabled = payStatus.value === 'Not Paid';
        }
        toggleTxCode();
        payStatus.addEventListener('change', toggleTxCode);

        // Autofill by phone
        var phoneInput    = document.getElementById('phone');
        var nameInput     = document.getElementById('name');
        var locationInput = document.getElementById('location');
        var phoneLoading  = document.getElementById('phone-loading');
        var custStatus    = document.getElementById('customer-status');
        var debounce;

        function clearNotice() {
            var existing = document.getElementById('previous-items-notice');
            if (existing) existing.remove();
        }

        function resetItemsToBlank() {
            clearNotice();
            document.getElementById('items-container').innerHTML = '';
            itemIndex = 0;
            addItem();
        }

        function loadPreviousItems(phone) {
            var type = getType();
            fetch('{{ route("orders.previousItems") }}?phone=' + encodeURIComponent(phone) + '&type=' + type)
            .then(r => r.json())
            .then(data => {
                if (!data.found) {
                    // No previous items — reset to a single blank row
                    resetItemsToBlank();
                    return;
                }

                // Clear and reload with previous items
                clearNotice();
                document.getElementById('items-container').innerHTML = '';
                itemIndex = 0;

                var notice = document.createElement('div');
                notice.id = 'previous-items-notice';
                notice.className = 'alert alert-info alert-dismissible fade show mb-3';
                notice.innerHTML = '<i class="mdi mdi-information-outline me-1"></i>'
                    + '<strong>' + data.items.length + ' item(s) loaded from order ' + data.order_number + '.</strong>'
                    + ' Remove any that were <strong>not</strong> brought in today.'
                    + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.getElementById('items-container').before(notice);

                data.items.forEach(function(item) {
                    addItem(item);
                });
            })
            .catch(function() {});
        }

        phoneInput.addEventListener('input', function() {
            clearTimeout(debounce);
            debounce = setTimeout(function() {
                var phone = phoneInput.value.trim();

                // Phone cleared or too short — reset everything
                if (phone.length < 10) {
                    nameInput.value     = '';
                    locationInput.value = '';
                    custStatus.style.display = 'none';
                    resetItemsToBlank();
                    return;
                }

                phoneLoading.style.display = 'inline-block';
                custStatus.style.display   = 'none';

                fetch('{{ route("customer.byPhone") }}?phone=' + encodeURIComponent(phone), {
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    phoneLoading.style.display = 'none';
                    if (data.found) {
                        // Always overwrite — we're looking up a new customer
                        nameInput.value     = data.name || '';
                        locationInput.value = data.location || '';
                        custStatus.style.display = 'inline-block';
                    } else {
                        // New/unknown customer — clear the fields
                        nameInput.value     = '';
                        locationInput.value = '';
                        custStatus.style.display = 'none';
                    }
                    // Always refresh items for the new phone
                    loadPreviousItems(phone);
                })
                .catch(() => { phoneLoading.style.display = 'none'; });
            }, 700);
        });
    });
})();
</script>
@endpush
