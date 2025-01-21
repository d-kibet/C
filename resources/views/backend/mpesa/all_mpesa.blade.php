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
                         <h4 class="page-title">All Mpesa Data</h4>
                         <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('add.mpesa') }}" class="btn btn-primary rounded-pill waves-effect waves-light">Add Mpesa </a>
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
                                        <th>Date</th>
                                        <th>Cash</th>
                                        <th>Float</th>
                                        <th>Working</th>
                                        <th>Account</th>

                                    </tr>
                                </thead>


                                <tbody>
                                    <tbody>
                                        @foreach($mpesa as $key=> $item)
                                        <tr>
                                            <td>{{ $item->date }}</td>
                                            <td>{{ $item->cash }}</td>
                                            <td>{{ $item->float }}</td>
                                            <td>{{ $item->account }}</td>

                                            <td>

                                                <a href="{{ route('edit.mpesa',$item->id) }}"  class="btn btn-blue rounded-pill waves-effect waves-light" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                                <a href="{{ route('delete.mpesa',$item->id) }}" class="btn btn-danger rounded-pill waves-effect waves-light" id="delete" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>

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
