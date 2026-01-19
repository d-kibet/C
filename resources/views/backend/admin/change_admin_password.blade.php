@extends('admin_master')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

<div class="content">

    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('all.admin') }}">All Admins</a></li>
                            <li class="breadcrumb-item active">Change Password</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Change Admin Password</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-8 col-xl-8">
                <div class="card">
                    <div class="card-body">

                        <div class="alert alert-info" role="alert">
                            <i class="mdi mdi-information-outline me-2"></i>
                            You are changing the password for: <strong>{{ $adminuser->name }}</strong> ({{ $adminuser->email }})
                        </div>

                        <form id="myForm" method="post" action="{{ route('update.admin.password') }}">
                            @csrf

                            <input type="hidden" name="user_id" value="{{ $adminuser->id }}">

                            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-key me-1"></i> New Password</h5>

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Confirm new password">
                                    </div>
                                </div>

                            </div> <!-- end row -->

                            <div class="text-end">
                                <a href="{{ route('all.admin') }}" class="btn btn-secondary waves-effect waves-light mt-2 me-2">
                                    <i class="mdi mdi-arrow-left"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success waves-effect waves-light mt-2">
                                    <i class="mdi mdi-content-save"></i> Change Password
                                </button>
                            </div>
                        </form>

                    </div>
                </div> <!-- end card-->

            </div> <!-- end col -->
        </div>
        <!-- end row-->

    </div> <!-- container -->

</div> <!-- content -->


<script type="text/javascript">
$(document).ready(function (){
    $('#myForm').validate({
        rules: {
            new_password: {
                required: true,
                minlength: 8,
            },
            new_password_confirmation: {
                required: true,
                minlength: 8,
                equalTo: "#new_password"
            },
        },
        messages: {
            new_password: {
                required: 'Please enter a new password',
                minlength: 'Password must be at least 8 characters',
            },
            new_password_confirmation: {
                required: 'Please confirm the new password',
                minlength: 'Password must be at least 8 characters',
                equalTo: 'Passwords do not match'
            },
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function(element, errorClass, validClass){
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element, errorClass, validClass){
            $(element).removeClass('is-invalid');
        },
    });
});
</script>

@endsection
