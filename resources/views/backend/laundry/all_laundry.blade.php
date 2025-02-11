@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">All Laundry Data</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li>
                                <a href="{{ route('add.laundry') }}" class="btn btn-primary rounded-pill waves-effect waves-light">
                                    Add Laundry
                                </a>
                            </li>
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
                        <table id="myTable" class="table dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <!-- Removed Location and Unique Id columns -->
                                    <th>Date Received</th>
                                    <th>Date Delivered</th>
                                    <th>Total</th>
                                    <th>Payment Status</th>
                                    <th>Delivered</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($laundry as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->phone }}</td>
                                        <!-- Removed location and unique_id columns from the body -->
                                        <td>{{ $item->date_received }}</td>
                                        <td>{{ $item->date_delivered }}</td>
                                        <td>{{ $item->total }}</td>
                                        <td>{{ $item->payment_status }}</td>
                                        <td>{{ $item->delivered }}</td>
                                        <td>
                                            @can('laundry.edit')
                                                <a href="{{ route('edit.laundry', $item->id) }}" class="btn btn-secondary rounded-pill waves-effect" title="Edit">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            @endcan

                                            @can('laundry.delete')
                                                <a href="{{ route('delete.laundry', $item->id) }}" class="btn btn-danger rounded-pill waves-effect waves-light delete" title="Delete">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </a>
                                            @endcan

                                            @can('laundry.details')
                                                <a href="{{ route('details.laundry', $item->id) }}" class="btn btn-info rounded-pill waves-effect waves-light" title="Details">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No laundry data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div> <!-- end container-fluid -->
</div> <!-- end content -->

@endsection
