@extends('admin_master')
@section('admin')



    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

                <!-- start page title -->
                 <div class="row">
                      <div class="col-12">
                          <div class="page-title-box d-flex justify-content-between align-items-center">
                         <h4 class="page-title">All Carpet Data</h4>
                         <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('add.carpet') }}" class="btn btn-primary rounded-pill waves-effect waves-light">Add Carpet </a>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">



                            <table id="basic-datatable" class="table dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Unique ID</th>
                                        <th>Size</th>
                                        <th>Price</th>
                                        <th>Phone Number</th>
                                        <th>Location</th>
                                        <th>payment_status</th>
                                        <th>Delivered</th>
                                    </tr>
                                </thead>


                                <tbody>
                                    <tbody>
                                        @foreach($carpet as $key=> $item)
                                        <tr>
                                            <td>{{ $item->uniqueid }}</td>
                                            <td>{{ $item->size }}</td>
                                            <td>{{ $item->price }}</td>
                                            <td>{{ $item->phone }}</td>
                                            <td>{{ $item->location }}</td>
                                            <td>{{ $item->payment_status }}</td>
                                            <td>{{ $item->delivered }}</td>
                                            <td>

                                                <a href="{{ route('history.client',$item->id) }}" class="btn btn-info">History</a>
                                        <a href="{{ route('edit.carpet',$item->id) }}" class="btn btn-secondary rounded-pill waves-effect">Edit</a>
                                        <a href="{{ route('delete.carpet',$item->id) }}" class="btn btn-danger rounded-pill waves-effect waves-light" id="delete">Delete</a>

                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>

                                </tbody>
                            </table>

                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
            <!-- end row-->




        </div> <!-- container -->

    </div> <!-- content -->





@endsection
