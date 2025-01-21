@extends('admin_master')
@section('admin')

<link rel="stylesheet" href="cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css">

<script src="cdn.datatables.net/2.2.1/js/dataTables.min.js"></script>



    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

                <!-- start page title -->
                 <div class="row">
                      <div class="col-12">
                          <div class="page-title-box d-flex justify-content-between align-items-center">
                         <h4 class="page-title">All Laundry Data</h4>
                         <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('add.laundry') }}" class="btn btn-primary rounded-pill waves-effect waves-light">Add Laundry </a>
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
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Location</th>
                                        <th>Unique Id</th>
                                        <th>Date Received</th>
                                        <th>total</th>
                                        <th>payment_status</th>
                                        <th>Delivered</th>
                                    </tr>
                                </thead>


                                <tbody>
                                    <tbody>
                                        @foreach($laundry as $key=> $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->phone }}</td>
                                            <td>{{ $item->location }}</td>
                                            <td>{{ $item->unique_id }}</td>
                                            <td>{{ $item->date_received }}</td>
                                            <td>{{ $item->total }}</td>
                                            <td>{{ $item->payment_status }}</td>
                                            <td>{{ $item->delivered }}</td>
                                            <td>

                                        <a href="{{ route('edit.laundry',$item->id) }}"  class="btn btn-blue rounded-pill waves-effect waves-light" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                        <a href="{{ route('delete.laundry',$item->id) }}" class="btn btn-danger rounded-pill waves-effect waves-light" id="delete" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                        <a href="{{ route('details.laundry',$item->id) }}" class="btn btn-info rounded-pill waves-effect waves-light" title="Details"><i class="fa fa-eye" aria-hidden="true"></i></a>

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
