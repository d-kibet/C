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
                                            <label for="uniqueid" class="form-label">Unique ID</label>
                                            <input
                                                type="text"
                                                name="uniqueid"
                                                id="uniqueid"
                                                class="form-control @error('uniqueid') is-invalid @enderror"
                                            >
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

                                    <!-- Customer Phone Number -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Customer Phone Number</label>
                                            <input
                                                type="text"
                                                name="phone"
                                                id="phone"
                                                class="form-control @error('phone') is-invalid @enderror"
                                            >
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
                                                class="form-control @error('date_received') is-invalid @enderror"
                                            >
                                            @error('date_received')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Date Delivered -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_delivered" class="form-label">Date Delivered</label>
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
                                            <select
                                                name="payment_status"
                                                id="payment_status"
                                                class="form-select @error('payment_status') is-invalid @enderror"
                                            >
                                                <option selected disabled>Select Status</option>
                                                <option value="Paid">Paid</option>
                                                <option value="Not Paid">Not Paid</option>
                                            </select>
                                            @error('payment_status')
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
                                                <option selected disabled>Select Status</option>
                                                <option value="Delivered">Delivered</option>
                                                <option value="Not Delivered">Not Delivered</option>
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

{{-- JavaScript for Automatic Price Calculation --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sizeInput = document.getElementById('size');
    const multiplierInput = document.getElementById('multiplier');
    const priceInput = document.getElementById('price');

    function calculatePrice() {
        let sizeValue = sizeInput.value.trim();
        let computedSize = 0;

        // Check if the size input contains '*' or 'x' (case-insensitive)
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
            // Treat the input as a single number
            computedSize = parseFloat(sizeValue) || 0;
        }

        const multiplier = parseFloat(multiplierInput.value) || 0;
        const finalPrice = computedSize * multiplier;

        // Round to one decimal place if it's a valid number
        if (!isNaN(finalPrice)) {
            priceInput.value = finalPrice.toFixed(1);
        } else {
            priceInput.value = '';
        }
    }

    // Update price whenever size or multiplier changes
    sizeInput.addEventListener('input', calculatePrice);
    multiplierInput.addEventListener('input', calculatePrice);
});
</script>

{{-- If you have an image upload (not used in this form, but left if needed) --}}
<script type="text/javascript">
$(document).ready(function(){
    $('#image').change(function(e){
        var reader = new FileReader();
        reader.onload = function(e){
            $('#showImage').attr('src', e.target.result);
        }
        reader.readAsDataURL(e.target.files[0]);
    });
});
</script>

@endsection
