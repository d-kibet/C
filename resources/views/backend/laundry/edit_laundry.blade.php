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
                                     <h4 class="page-title mb-0">Edit Laundry</h4>
                                     <nav aria-label="breadcrumb">
                                         <ol class="breadcrumb mb-0">
                                             <li class="breadcrumb-item active" aria-current="page">
                                                 <a href="javascript:void(0);">Edit Laundry</a>
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





    <!-- end timeline content-->

    <div class="tab-pane" id="settings">
        <form method="post" action="{{ route('laundry.update') }}">
        	@csrf

            <input type="hidden" name="id" value="{{ $laundry->id }}">

            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> Edit Laundry</h5>

            <div class="row">


    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Customer Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $laundry->name }}"  >
             @error('name')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>


              <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Customer Phone Number</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $laundry->phone }}"  >
             @error('phone')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>


              <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Customer Location  </label>
            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ $laundry->location }}"  >
             @error('location')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Customer Unique ID   </label>
            <input type="text" name="unique_id" class="form-control @error('unique_id') is-invalid @enderror" value="{{ $laundry->unique_id }}"  >
             @error('unique_id')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Date Received   </label>
            <input type="date" name="date_received" class="form-control @error('date_received') is-invalid @enderror" value="{{ $laundry->date_received }}"  >
             @error('date_received')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Date Delivered   </label>
            <input type="date" name="date_delivered" class="form-control @error('date_delivered') is-invalid @enderror" value="{{ $laundry->date_delivered }}"  >
             @error('date_delivered')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Quantity  </label>
            <input type="text" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ $laundry->quantity }}"  >
             @error('quantity')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Item Description    </label>
            <textarea required=""  name="item_description" class="form-control"  @error('item_description') is-invalid @enderror rows="5"> {{ old('item_description', $laundry->item_description) }} </textarea>
             @error('item_description')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Weight </label>
            <input type="text" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ $laundry->weight }}"  >
             @error('weight')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Price </label>
            <input type="text" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ $laundry->price }}"  >
             @error('price')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="discount" class="form-label">Discount (KES)</label>
            <input type="number" name="discount" id="discount" class="form-control @error('discount') is-invalid @enderror" step="any" min="0" value="{{ old('discount', $laundry->discount ?? 0) }}">
            @error('discount')
                <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Total Amount    </label>
            <input type="text" name="total" class="form-control @error('total') is-invalid @enderror" value="{{ $laundry->total }}"  >
             @error('total')
      <span class="text-danger"> {{ $message }} </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Delivery Status </label>
           <select name="delivered" class="form-select" @error('delivered') is-invalid @enderror id="example-select">
                    <option selected disabled >Select Status </option>
                    <option value="Delivered" {{ $laundry->delivered == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="Not Delivered" {{ $laundry->delivered == 'Not Delivered' ? 'selected' : '' }}>Not Delivered</option>

                </select>
                @error('delivered')
      <span class="text-danger"> {{ $message }} </span>
            @enderror

        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="firstname" class="form-label">Payment Status </label>
           <select name="payment_status" class="form-select" @error('payment_status') is-invalid @enderror id="example-select">
                    <option selected disabled >Select Status </option>
                    <option value="Paid" {{ $laundry->payment_status == 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Partial" {{ $laundry->payment_status == 'Partial' ? 'selected' : '' }}>Partial</option>
                    <option value="Not Paid" {{ $laundry->payment_status == 'Not Paid' ? 'selected' : '' }}>Not Paid</option>

                </select>
                @error('payment_status')
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




@endsection
