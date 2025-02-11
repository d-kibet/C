@extends('admin_master')
@section('admin')

<!-- Optional: jQuery (if you use image preview or other jQuery functionality) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<div class="content">
    <!-- Page Title -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center">
                <h4 class="page-title mb-0">Add Mpesa Record</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item active" aria-current="page">
                            <a href="javascript:void(0);">Add Mpesa Record</a>
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
                    <!-- Add Mpesa Record Form -->
                    <form method="post" action="{{ route('mpesa.store') }}">
                        @csrf
                        <h5 class="mb-4 text-uppercase">
                            <i class="mdi mdi-account-circle me-1"></i> Add Mpesa Record
                        </h5>

                        <div class="row">
                            <!-- Date Field -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" id="date" name="date" class="form-control @error('date') is-invalid @enderror">
                                    @error('date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Cash Amount Field -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cash" class="form-label">Cash Amount</label>
                                    <input type="text" id="cash" name="cash" class="form-control @error('cash') is-invalid @enderror">
                                    @error('cash')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Float Amount Field -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="float" class="form-label">Float Amount</label>
                                    <input type="text" id="float" name="float" class="form-control @error('float') is-invalid @enderror">
                                    @error('float')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Working Amount Field -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="working" class="form-label">Working Amount</label>
                                    <input type="text" id="working" name="working" class="form-control @error('working') is-invalid @enderror">
                                    @error('working')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Account Balance Field -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="account" class="form-label">Account Balance</label>
                                    <input type="text" id="account" name="account" class="form-control @error('account') is-invalid @enderror">
                                    @error('account')
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
                    <!-- End Form -->
                </div>
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div> <!-- end row -->
</div> <!-- end container-fluid -->

<!-- Optional: Script for image preview (if you use an image input) -->
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
