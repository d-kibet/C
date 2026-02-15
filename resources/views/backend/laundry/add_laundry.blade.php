@extends('admin_master')
@section('admin')
<style>
    /* Ensure the content area is tall enough so the footer doesn't overlap */
    .content {
        min-height: calc(100vh - 200px); /* Adjust the value as needed */
    }
</style>

<div class="content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">Add Laundry</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="javascript:void(0);">Add Laundry</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-lg-8 col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Form to Add Laundry -->
                        <div class="tab-pane" id="settings">
                            <form method="post" action="{{ route('laundry.store') }}">
                                @csrf
                                <h5 class="mb-4 text-uppercase">
                                    <i class="mdi mdi-account-circle me-1"></i> Add Laundry
                                </h5>
                                <div class="row">
                                    <!-- Customer Phone Number -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Phone Number</label>
                                            <div class="input-group">
                                                <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Enter phone to auto-fill customer">
                                                <span class="input-group-text" id="phoneStatus" style="display:none;"></span>
                                            </div>
                                            @error('phone')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Customer Unique ID -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Unique ID</label>
                                            <div class="input-group">
                                                <input type="text" id="unique_id" name="unique_id" class="form-control @error('unique_id') is-invalid @enderror" placeholder="Enter ID to auto-fill customer">
                                                <span class="input-group-text" id="uniqueIdStatus" style="display:none;"></span>
                                            </div>
                                            @error('unique_id')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Customer Name -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Name</label>
                                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror">
                                            @error('name')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Customer Location -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Location</label>
                                            <input type="text" id="location" name="location" class="form-control @error('location') is-invalid @enderror">
                                            @error('location')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Date Received -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date Received</label>
                                            <input type="date" name="date_received" class="form-control @error('date_received') is-invalid @enderror">
                                            @error('date_received')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Date Delivered -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date Delivered</label>
                                            <input type="date" name="date_delivered" class="form-control @error('date_delivered') is-invalid @enderror">
                                            @error('date_delivered')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Quantity -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Quantity</label>
                                            <input type="text" name="quantity" class="form-control @error('quantity') is-invalid @enderror">
                                            @error('quantity')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Item Description -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Item Description</label>
                                            <textarea name="item_description" class="form-control @error('item_description') is-invalid @enderror" rows="5" required></textarea>
                                            @error('item_description')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Weight -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Weight</label>
                                            <input type="text" name="weight" class="form-control @error('weight') is-invalid @enderror">
                                            @error('weight')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Price -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Price</label>
                                            <input type="text" id="laundry_price" name="price" class="form-control @error('price') is-invalid @enderror">
                                            <small id="previous-rate-helper" class="text-muted" style="display: none;"></small>
                                            @error('price')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Discount (KES) -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Discount (KES)</label>
                                            <input type="number" id="laundry_discount" name="discount" class="form-control @error('discount') is-invalid @enderror" step="any" min="0" value="0">
                                            @error('discount')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Discount Warning Banner -->
                                    <div class="col-12" id="laundry-discount-warning" style="display: none;">
                                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                            <strong><i class="mdi mdi-alert-circle-outline me-1"></i> Previous Discount Alert:</strong>
                                            <span id="laundry-discount-warning-text"></span>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    </div>

                                    <!-- Total Amount -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Total Amount</label>
                                            <input type="text" name="total" class="form-control @error('total') is-invalid @enderror">
                                            @error('total')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Delivery Status -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Delivery Status</label>
                                            <select name="delivered" class="form-select @error('delivered') is-invalid @enderror">
                                                <option selected disabled>Select Status</option>
                                                <option value="Delivered">Delivered</option>
                                                <option value="Not Delivered">Not Delivered</option>
                                            </select>
                                            @error('delivered')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Payment Status -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Payment Status</label>
                                            <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
                                                <option selected disabled>Select Status</option>
                                                <option value="Paid">Paid</option>
                                                <option value="Partial">Partially Paid</option>
                                                <option value="Not Paid">Not Paid</option>
                                            </select>
                                            @error('payment_status')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div> <!-- end row -->

                                <div class="text-end">
                                    <button type="submit" class="btn btn-success waves-effect waves-light mt-2">
                                        <i class="mdi mdi-content-save"></i> Save
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- end settings content-->
                    </div>
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
        <!-- end row-->
    </div> <!-- container -->
</div> <!-- content -->


@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var phoneTimer = null;
    var uniqueIdTimer = null;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── Phone Number Lookup ──
    $('#phone').on('input', function() {
        clearTimeout(phoneTimer);
        var phone = $(this).val().trim();
        if (phone.length >= 6) {
            $('#phoneStatus').html('<i class="mdi mdi-loading mdi-spin"></i>').show();
            phoneTimer = setTimeout(function() {
                lookupByPhone(phone);
            }, 800);
        } else {
            $('#phoneStatus').hide();
        }
    });

    $('#phone').on('blur', function() {
        var phone = $(this).val().trim();
        if (phone.length >= 6) {
            lookupByPhone(phone);
        }
    });

    function lookupByPhone(phone) {
        fetch("{{ route('customer.byPhone') }}?phone=" + encodeURIComponent(phone), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.found) {
                $('#name').val(data.name);
                $('#location').val(data.location);
                if (data.uniqueid) {
                    $('#unique_id').val(data.uniqueid);
                }

                // Visual feedback
                highlightField('#name');
                highlightField('#location');
                if (data.uniqueid) highlightField('#unique_id');

                // Show discount warning for laundry
                showLaundryDiscountWarning(data);

                $('#phoneStatus').html('<span class="badge bg-success">Returning Customer</span>').show();
                toastr.success('Customer details loaded! Please fill in the remaining fields.');

                setTimeout(function() { $('#phoneStatus').fadeOut(); }, 3000);
            } else {
                $('#phoneStatus').hide();
                $('#laundry-discount-warning').hide();
                $('#previous-rate-helper').hide();
            }
        })
        .catch(function() {
            $('#phoneStatus').hide();
        });
    }

    // ── Unique ID Lookup ──
    $('#unique_id').on('input', function() {
        clearTimeout(uniqueIdTimer);
        var uid = $(this).val().trim();
        if (uid.length >= 2) {
            $('#uniqueIdStatus').html('<i class="mdi mdi-loading mdi-spin"></i>').show();
            uniqueIdTimer = setTimeout(function() {
                lookupByUniqueId(uid);
            }, 800);
        } else {
            $('#uniqueIdStatus').hide();
        }
    });

    $('#unique_id').on('blur', function() {
        var uid = $(this).val().trim();
        if (uid.length >= 2) {
            lookupByUniqueId(uid);
        }
    });

    function lookupByUniqueId(uid) {
        fetch("{{ route('customer.byUniqueId') }}?uniqueid=" + encodeURIComponent(uid), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.found) {
                $('#name').val(data.name);
                $('#location').val(data.location);
                if (data.phone) {
                    $('#phone').val(data.phone);
                    highlightField('#phone');
                }

                // Visual feedback
                highlightField('#name');
                highlightField('#location');

                // Show discount warning for laundry
                showLaundryDiscountWarning(data);

                $('#uniqueIdStatus').html('<span class="badge bg-success">Returning Customer</span>').show();
                $('#phoneStatus').html('<span class="badge bg-success">Returning Customer</span>').show();
                toastr.success('Customer details loaded from existing record!');

                setTimeout(function() {
                    $('#uniqueIdStatus').fadeOut();
                    $('#phoneStatus').fadeOut();
                }, 3000);
            } else {
                $('#uniqueIdStatus').hide();
                $('#laundry-discount-warning').hide();
                $('#previous-rate-helper').hide();
            }
        })
        .catch(function() {
            $('#uniqueIdStatus').hide();
        });
    }

    // Highlight auto-filled fields briefly
    function highlightField(selector) {
        $(selector).css('border-color', '#28a745');
        setTimeout(function() {
            $(selector).css('border-color', '');
        }, 3000);
    }

    // Show discount warning banner for returning laundry customers
    function showLaundryDiscountWarning(data) {
        var lastDiscount = parseFloat(data.last_laundry_discount) || 0;
        var lastPrice = parseFloat(data.last_laundry_price) || 0;
        var lastTotal = parseFloat(data.last_laundry_total) || 0;

        // Show previous rate helper text
        if (lastPrice > 0) {
            var helperText = 'Previous rate: KES ' + lastPrice.toLocaleString();
            if (lastDiscount > 0) {
                helperText += ' (Discount: KES ' + lastDiscount.toLocaleString() + ' applied)';
            }
            $('#previous-rate-helper').text(helperText).show();
        } else {
            $('#previous-rate-helper').hide();
        }

        if (lastDiscount > 0) {
            var fullPrice = lastPrice + lastDiscount;
            $('#laundry-discount-warning-text').text(
                'Previous visit had a KES ' + lastDiscount.toLocaleString() +
                ' discount (charged KES ' + lastPrice.toLocaleString() +
                '). Full price was KES ' + fullPrice.toLocaleString() + '.'
            );
            $('#laundry-discount-warning').show();

            // Reset discount to 0 for this visit
            $('#laundry_discount').val(0);
        } else {
            $('#laundry-discount-warning').hide();
        }
    }
});
</script>
@endpush
