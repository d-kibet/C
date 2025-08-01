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
                                    <!-- Customer Name -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Name</label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror">
                                            @error('name')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Customer Phone Number -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Phone Number</label>
                                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror">
                                            @error('phone')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Customer Location -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Location</label>
                                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror">
                                            @error('location')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Customer Unique ID -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Unique ID</label>
                                            <input type="text" name="unique_id" class="form-control @error('unique_id') is-invalid @enderror">
                                            @error('unique_id')
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
                                            <input type="text" name="price" class="form-control @error('price') is-invalid @enderror">
                                            @error('price')
                                                <span class="text-danger"> {{ $message }} </span>
                                            @enderror
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

<script type="text/javascript">
    $(document).ready(function(){
        $('#image').change(function(e){
            var reader = new FileReader();
            reader.onload =  function(e){
                $('#showImage').attr('src', e.target.result);
            }
            reader.readAsDataURL(e.target.files['0']);
        });
    });
</script>

@endsection
