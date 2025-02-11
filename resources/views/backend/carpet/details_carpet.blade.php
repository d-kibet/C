
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
                                <a href="javascript:void(0);">Details Carpet</a>
                            </li>
                        </ol>
                    </div>
                    <h4 class="page-title">Details Carpet</h4>
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
                            <form method="post" action="{{ route('carpet.update') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $carpet->id }}">

                                <h5 class="mb-4 text-uppercase">
                                    <i class="mdi mdi-account-circle me-1"></i> Details Carpet
                                </h5>

                                <div class="row">
                                    <!-- Customer Name -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Name</label>
                                            <p class="text-danger">{{ $carpet->name }}</p>
                                        </div>
                                    </div>
                                    <!-- Customer Phone Number -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Phone Number</label>
                                            <p class="text-danger">{{ $carpet->phone }}</p>
                                        </div>
                                    </div>
                                    <!-- Customer Location -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Customer Location</label>
                                            <p class="text-danger">{{ $carpet->location }}</p>
                                        </div>
                                    </div>
                                    <!-- Customer Unique ID -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Carpet Unique ID</label>
                                            <p class="text-danger">{{ $carpet->uniqueid }}</p>
                                        </div>
                                    </div>
                                    <!-- Date Received -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date Received</label>
                                            <p class="text-danger">{{ $carpet->date_received }}</p>
                                        </div>
                                    </div>

                                     <!-- Date Delivered -->
                                     <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Date Delivered</label>
                                            <p class="text-danger">{{ $carpet->date_delivered }}</p>
                                        </div>
                                    </div>



                                    <!-- Price -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Price</label>
                                            <p class="text-danger">{{ $carpet->price }}</p>
                                        </div>
                                    </div>

                                    <!-- Delivery Status -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Delivery Status</label>
                                            <p class="text-danger">{{ $carpet->delivered }}</p>
                                        </div>
                                    </div>
                                    <!-- Payment Status -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Payment Status</label>
                                            <p class="text-danger">{{ $carpet->payment_status }}</p>
                                        </div>
                                    </div>
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
