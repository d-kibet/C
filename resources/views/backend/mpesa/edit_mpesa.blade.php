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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Edit Mpesa Record</a></li>

                                        </ol>
                                    </div>
                                    <h4 class="page-title">Edit Mpesa Record</h4>
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
        <form method="post" action="{{ route('mpesa.update') }}">
        	@csrf

            <input type="hidden" name="id" value="{{ $mpesa->id }}">

            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> Edit Mpesa Record</h5>

            <div class="row">


    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Date</label>
            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ $mpesa->date }}" >
             @error('date')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>


              <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Cash Amount</label>
            <input type="text" name="cash" class="form-control @error('cash') is-invalid @enderror" value="{{ $mpesa->cash }}"  >
             @error('cash')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>


              <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Float Amount</label>
            <input type="text" name="float" class="form-control @error('float') is-invalid @enderror" value="{{ $mpesa->float }}"  >
             @error('float')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Working Amount</label>
            <input type="text" name="working" class="form-control @error('working') is-invalid @enderror" value="{{ $mpesa->working }}"  >
             @error('working')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Account Balance   </label>
            <input type="text" name="account" class="form-control @error('account') is-invalid @enderror" value="{{ $mpesa->account }}"  >
             @error('account')
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
