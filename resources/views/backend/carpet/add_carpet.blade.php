@extends('admin_master')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


 <div class="content">

                    <!-- Start Content-->
                    <div class="container-fluid" style="margin-top: 20px;">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Add Carpet</a></li>

                                        </ol>
                                    </div>
                                    <h4 class="page-title">Add Carpet</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

<div class="row">


  <div class="col-lg-8 col-xl-12">
<div class="card">
    <div class="card-body">





    <!-- end timeline content-->

    <div class="tab-pane" id="settings">
        <form method="post" action="{{ route('carpet.store') }}">
        	@csrf

            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> Add Carpet</h5>

            <div class="row">


    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Unique ID</label>
            <input type="text" name="uniqueid" class="form-control @error('uniqueid') is-invalid @enderror"   >
             @error('uniqueid')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>


              <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Carpet Size</label>
            <input type="text" name="size" class="form-control @error('size') is-invalid @enderror"   >
             @error('size')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>




              <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Carpet Price  </label>
            <input type="text" name="price" class="form-control @error('price') is-invalid @enderror"   >
             @error('price')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>


      <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Customer Phone Number   </label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"   >
             @error('phone')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Customer's Location    </label>
            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"   >
             @error('location')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>


      <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Payment Status </label>
           <select name="payment_status" class="form-select" @error('payment_status') is-invalid @enderror id="example-select">
                    <option selected disabled >Select Status </option>
                    <option value="Paid">Paid</option>
                    <option value="Not Paid">Not Paid</option>

                </select>
                @error('payment_status')
      <span class="text-danger"> {{ $message }} </span>
            @enderror

        </div>
    </div>



     <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Delivery Status    </label>
            <input type="text" name="delivered" class="form-control @error('delivered') is-invalid @enderror"   >
             @error('delivered')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>




            </div> <!-- end row -->



            <div class="text-end">
                <button type="submit" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> Save</button>
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
				$('#showImage').attr('src',e.target.result);
			}
			reader.readAsDataURL(e.target.files['0']);
		});
	});
</script>

@endsection
