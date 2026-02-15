@extends('admin_master')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<div class="content">
    <!-- Start Content-->
    <div class="container-fluid" style="margin-top: 20px;">

        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">Add Carpet</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="javascript:void(0);">Add Carpet</a>
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
                        <!-- Form to Add a Carpet -->
                        <div class="tab-pane" id="settings">
                            <form method="post" action="{{ route('carpet.store') }}">
                                @csrf

                                <h5 class="mb-4 text-uppercase">
                                    <i class="mdi mdi-account-circle me-1"></i> Add Carpet
                                </h5>

                                <div class="row">
                                    <!-- Unique ID -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="uniqueid" class="form-label">
                                                Unique ID
                                                <span id="uniqueid-status" class="badge badge-soft-info ms-2" style="display: none;">
                                                    <i class="ri-user-star-line"></i> Returning Customer
                                                </span>
                                            </label>
                                            <input
                                                type="text"
                                                name="uniqueid"
                                                id="uniqueid"
                                                class="form-control @error('uniqueid') is-invalid @enderror"
                                                placeholder="Enter unique ID"
                                            >
                                            <small id="uniqueid-loading" class="text-muted" style="display: none;">
                                                <i class="ri-loader-4-line spin"></i> Checking customer...
                                            </small>
                                            @error('uniqueid')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Carpet Size -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="size" class="form-label">Carpet Size</label>
                                            <input
                                                type="text"
                                                name="size"
                                                id="size"
                                                class="form-control @error('size') is-invalid @enderror"
                                                step="any"
                                            >
                                            @error('size')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Price per Unit Size (Multiplier) -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="multiplier" class="form-label">Price per Unit Size</label>
                                            <input
                                                type="number"
                                                name="multiplier"
                                                id="multiplier"
                                                class="form-control @error('multiplier') is-invalid @enderror"
                                                step="any"
                                                value="30"
                                            >
                                            @error('multiplier')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Carpet Price (Automatically Calculated) -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="price" class="form-label">Carpet Price</label>
                                            <input
                                                type="number"
                                                name="price"
                                                id="price"
                                                class="form-control @error('price') is-invalid @enderror"
                                                readonly
                                                step="any"
                                            >
                                            @error('price')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Discount (KES) -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="discount" class="form-label">Discount (KES)</label>
                                            <input
                                                type="number"
                                                name="discount"
                                                id="discount"
                                                class="form-control @error('discount') is-invalid @enderror"
                                                step="any"
                                                min="0"
                                                value="0"
                                            >
                                            @error('discount')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Discount Warning Banner -->
                                    <div class="col-12" id="discount-warning" style="display: none;">
                                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                            <strong><i class="mdi mdi-alert-circle-outline me-1"></i> Previous Discount Alert:</strong>
                                            <span id="discount-warning-text"></span>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Customer Name </label>
                                            <input
                                                type="text"
                                                name="name"
                                                id="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                            >
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Customer Phone Number -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">
                                                Customer Phone Number
                                                <span id="customer-status" class="badge badge-soft-info ms-2" style="display: none;">
                                                    <i class="ri-user-star-line"></i> Returning Customer
                                                </span>
                                            </label>
                                            <input
                                                type="text"
                                                name="phone"
                                                id="phone"
                                                class="form-control @error('phone') is-invalid @enderror"
                                                placeholder="Enter phone number"
                                            >
                                            <small id="phone-loading" class="text-muted" style="display: none;">
                                                <i class="ri-loader-4-line spin"></i> Checking customer...
                                            </small>
                                            @error('phone')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Customer's Location -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="location" class="form-label">Customer's Location</label>
                                            <input
                                                type="text"
                                                name="location"
                                                id="location"
                                                class="form-control @error('location') is-invalid @enderror"
                                            >
                                            @error('location')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Date Received -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_received" class="form-label">Date Received</label>
                                            <input
                                                type="date"
                                                name="date_received"
                                                id="date_received"
                                                class="form-control @error('date_received') is-invalid @enderror"
                                                value="{{ old('date_received', date('Y-m-d')) }}"
                                            >
                                            @error('date_received')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Date Delivered -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_delivered" class="form-label">Date To Deliver</label>
                                            <input
                                                type="date"
                                                name="date_delivered"
                                                class="form-control @error('date_delivered') is-invalid @enderror"
                                            >
                                            @error('date_delivered')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Payment Status -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_status" class="form-label">Payment Status</label>
                                            <select name="payment_status" id="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
                                                <option value="Paid" {{ old('payment_status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                                                <option value="Not Paid" {{ old('payment_status', 'Not Paid') == 'Not Paid' ? 'selected' : '' }}>Not Paid</option>
                                            </select>
                                            @error('payment_status')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Transaction Code -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="transaction_code" class="form-label">Transaction Code</label>
                                            <input
                                                type="text"
                                                name="transaction_code"
                                                id="transaction_code"
                                                class="form-control @error('transaction_code') is-invalid @enderror"
                                                value="{{ old('transaction_code') }}"
                                                list="transaction_codes"
                                            >
                                            <datalist id="transaction_codes">
                                                <option value="Cash">
                                                <!-- Add additional options if needed -->
                                            </datalist>
                                            @error('transaction_code')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Delivery Status -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="delivered" class="form-label">Delivery Status</label>
                                            <select
                                                name="delivered"
                                                id="delivered"
                                                class="form-select @error('delivered') is-invalid @enderror"
                                            >
                                                <option value="Delivered" {{ old('delivered') == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                                <option value="Not Delivered" {{ old('delivered', 'Not Delivered') == 'Not Delivered' ? 'selected' : '' }}>Not Delivered</option>
                                            </select>
                                            @error('delivered')
                                                <span class="text-danger">{{ $message }}</span>
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

{{-- CSS for Loading Spinner --}}
<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.border-success {
    border-color: #28a745 !important;
    border-width: 2px !important;
    transition: border-color 0.3s ease;
}
</style>

{{-- JavaScript for Automatic Price Calculation with Discount --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sizeInput = document.getElementById('size');
    const multiplierInput = document.getElementById('multiplier');
    const priceInput = document.getElementById('price');
    const discountInput = document.getElementById('discount');

    function getBasePrice() {
        let sizeValue = sizeInput.value.trim();
        let computedSize = 0;

        if (/[x\*]/i.test(sizeValue)) {
            const parts = sizeValue.split(/[*x]/i);
            if (parts.length === 2) {
                const num1 = parseFloat(parts[0]);
                const num2 = parseFloat(parts[1]);
                if (!isNaN(num1) && !isNaN(num2)) {
                    computedSize = num1 * num2;
                }
            }
        } else {
            computedSize = parseFloat(sizeValue) || 0;
        }

        const multiplier = parseFloat(multiplierInput.value) || 0;
        return computedSize * multiplier;
    }

    function calculatePrice() {
        const basePrice = getBasePrice();
        const discount = parseFloat(discountInput.value) || 0;
        const finalPrice = basePrice - discount;

        if (!isNaN(finalPrice)) {
            priceInput.value = Math.max(0, finalPrice).toFixed(1);
        } else {
            priceInput.value = '';
        }
    }

    sizeInput.addEventListener('input', calculatePrice);
    multiplierInput.addEventListener('input', calculatePrice);
    discountInput.addEventListener('input', calculatePrice);
});


document.addEventListener("DOMContentLoaded", function() {
    var paymentStatus = document.getElementById("payment_status");
    var transactionCode = document.getElementById("transaction_code");

    function toggleTransactionCode() {
        // Enable the Transaction Code input only if Payment Status is "Paid"
        if (paymentStatus.value === "Paid") {
            transactionCode.disabled = false;
        } else {
            transactionCode.disabled = true;
        }
    }

    // Run on page load to set the correct state based on the current value
    toggleTransactionCode();

    // Update the state when the payment status is changed
    paymentStatus.addEventListener("change", toggleTransactionCode);
});

// Autofill Customer Details for Returning Customers
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    const nameInput = document.getElementById('name');
    const locationInput = document.getElementById('location');
    const sizeInput = document.getElementById('size');
    const customerStatus = document.getElementById('customer-status');
    const phoneLoading = document.getElementById('phone-loading');

    let debounceTimer;

    // Function to fetch customer details
    function fetchCustomerDetails(phone) {
        // Remove any spaces or special characters
        phone = phone.trim();

        if (phone.length < 10) {
            return; // Only fetch if phone has at least 10 digits
        }

        // Show loading indicator
        phoneLoading.style.display = 'inline-block';
        customerStatus.style.display = 'none';

        // Fetch customer data
        fetch(`{{ route('customer.byPhone') }}?phone=${encodeURIComponent(phone)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            phoneLoading.style.display = 'none';

            if (data.found) {
                // Autofill customer details
                nameInput.value = data.name || '';
                locationInput.value = data.location || '';
                sizeInput.value = data.size || '';

                // Trigger price calculation after setting size
                sizeInput.dispatchEvent(new Event('input'));

                // Show returning customer badge
                customerStatus.style.display = 'inline-block';

                // Check for previous carpet discount
                showDiscountWarning(data);

                // Add visual feedback with animation
                nameInput.classList.add('border-success');
                locationInput.classList.add('border-success');
                sizeInput.classList.add('border-success');

                // Show success toast notification
                toastr.success('Customer details loaded! Please fill in the remaining fields.', 'Returning Customer Found', {
                    timeOut: 3000,
                    progressBar: true
                });

                // Remove border color after 3 seconds
                setTimeout(() => {
                    nameInput.classList.remove('border-success');
                    locationInput.classList.remove('border-success');
                    sizeInput.classList.remove('border-success');
                }, 3000);
            } else {
                // New customer - clear the badge
                customerStatus.style.display = 'none';
                document.getElementById('discount-warning').style.display = 'none';
            }
        })
        .catch(error => {
            phoneLoading.style.display = 'none';
            console.error('Error fetching customer details:', error);
        });
    }

    // Event listener with debounce
    phoneInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchCustomerDetails(this.value);
        }, 800); // Wait 800ms after user stops typing
    });

    // Also fetch on blur (when user leaves the field)
    phoneInput.addEventListener('blur', function() {
        clearTimeout(debounceTimer);
        fetchCustomerDetails(this.value);
    });
});

// Autofill Customer Details by Unique ID
document.addEventListener('DOMContentLoaded', function() {
    const uniqueIdInput = document.getElementById('uniqueid');
    const nameInput = document.getElementById('name');
    const locationInput = document.getElementById('location');
    const phoneInput = document.getElementById('phone');
    const sizeInput = document.getElementById('size');
    const uniqueIdStatus = document.getElementById('uniqueid-status');
    const uniqueIdLoading = document.getElementById('uniqueid-loading');
    const customerStatus = document.getElementById('customer-status');

    let debounceTimer;

    // Function to fetch customer details by unique ID
    function fetchCustomerByUniqueId(uniqueId) {
        // Trim whitespace
        uniqueId = uniqueId.trim();

        if (uniqueId.length < 3) {
            return; // Only fetch if unique ID has at least 3 characters
        }

        // Show loading indicator
        uniqueIdLoading.style.display = 'inline-block';
        uniqueIdStatus.style.display = 'none';
        customerStatus.style.display = 'none';

        // Fetch customer data
        fetch(`{{ route('customer.byUniqueId') }}?uniqueid=${encodeURIComponent(uniqueId)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            uniqueIdLoading.style.display = 'none';

            if (data.found) {
                // Autofill customer details
                nameInput.value = data.name || '';
                locationInput.value = data.location || '';
                phoneInput.value = data.phone || '';
                sizeInput.value = data.size || '';

                // Trigger price calculation after setting size
                sizeInput.dispatchEvent(new Event('input'));

                // Show returning customer badge
                uniqueIdStatus.style.display = 'inline-block';
                customerStatus.style.display = 'inline-block';

                // Check for previous carpet discount
                showDiscountWarning(data);

                // Add visual feedback with animation
                nameInput.classList.add('border-success');
                locationInput.classList.add('border-success');
                phoneInput.classList.add('border-success');
                sizeInput.classList.add('border-success');

                // Show success toast notification
                toastr.success('Customer details loaded from existing record! Please fill in the remaining fields.', 'Returning Customer Found', {
                    timeOut: 3000,
                    progressBar: true
                });

                // Remove border color after 3 seconds
                setTimeout(() => {
                    nameInput.classList.remove('border-success');
                    locationInput.classList.remove('border-success');
                    phoneInput.classList.remove('border-success');
                    sizeInput.classList.remove('border-success');
                }, 3000);
            } else {
                // New customer or ID not found - clear the badge
                uniqueIdStatus.style.display = 'none';
                document.getElementById('discount-warning').style.display = 'none';
            }
        })
        .catch(error => {
            uniqueIdLoading.style.display = 'none';
            console.error('Error fetching customer details by unique ID:', error);
        });
    }

    // Event listener with debounce
    uniqueIdInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchCustomerByUniqueId(this.value);
        }, 800); // Wait 800ms after user stops typing
    });

    // Also fetch on blur (when user leaves the field)
    uniqueIdInput.addEventListener('blur', function() {
        clearTimeout(debounceTimer);
        fetchCustomerByUniqueId(this.value);
    });
});

// Show discount warning banner for returning customers
function showDiscountWarning(data) {
    var warningDiv = document.getElementById('discount-warning');
    var warningText = document.getElementById('discount-warning-text');
    var discountInput = document.getElementById('discount');

    var lastDiscount = parseFloat(data.last_carpet_discount) || 0;
    var lastPrice = parseFloat(data.last_carpet_price) || 0;

    if (lastDiscount > 0) {
        var fullPrice = lastPrice + lastDiscount;
        warningText.textContent = 'Previous visit had a KES ' + lastDiscount.toLocaleString() +
            ' discount (charged KES ' + lastPrice.toLocaleString() + '). Full price was KES ' + fullPrice.toLocaleString() + '.';
        warningDiv.style.display = 'block';

        // Reset discount to 0 for this visit
        discountInput.value = 0;
        discountInput.dispatchEvent(new Event('input'));
    } else {
        warningDiv.style.display = 'none';
    }
}

</script>



@endsection
