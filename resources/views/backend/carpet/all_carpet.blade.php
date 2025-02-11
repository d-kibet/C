@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <!-- Start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title">All Carpet Data</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a
                                href="{{ route('add.carpet') }}"
                                class="btn btn-primary rounded-pill waves-effect waves-light"
                            >
                                Add Carpet
                            </a>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- End page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="myTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date Received</th>
                                    <th>Unique ID</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Phone Number</th>

                                    <th>Payment Status</th>
                                    <th>Delivered</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($carpet as $item)
                                    <tr>
                                        <td>{{ $item->date_received }}</td>
                                        <td>{{ $item->uniqueid }}</td>
                                        <td>{{ $item->size }}</td>
                                        <td>{{ $item->price }}</td>
                                        <td>{{ $item->phone }}</td>

                                        <td>{{ $item->payment_status }}</td>
                                        <td>{{ $item->delivered }}</td>
                                        <td>
                                            {{-- History button (commented out in your code)
                                            <a
                                                href="{{ route('history.client', $item->id) }}"
                                                class="btn btn-info"
                                            >
                                                History
                                            </a>
                                            --}}

                                            @if(Auth::user()->can('carpet.edit'))
                                                <a
                                                    href="{{ route('edit.carpet', $item->id) }}"
                                                    class="btn btn-secondary rounded-pill waves-effect"
                                                >
                                                    Edit
                                                </a>
                                            @endif

                                            @if(Auth::user()->can('carpet.delete'))
                                                <a
                                                    href="{{ route('delete.carpet', $item->id) }}"
                                                    class="btn btn-danger rounded-pill waves-effect waves-light"
                                                    id="delete"
                                                >
                                                    Delete
                                                </a>
                                            @endif

                                            @if(Auth::user()->can('carpet.details'))
                                            <a
                                                href="{{ route('details.carpet', $item->id) }}"
                                                class="btn btn-info btn-rounded waves-effect waves-light"
                                            >
                                               Info
                                            </a>
                                        @endif


                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div><!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->
</div> <!-- content -->

@endsection
