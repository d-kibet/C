@extends('admin_master')
@section('admin')

<div class="content">
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- Start Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="page-title mb-0">
                        All Admin
                        <span class="badge bg-danger ms-2">{{ count($alladminuser) }}</span>
                    </h4>
                    <div class="page-title-right">
                        <a href="{{ route('add.admin') }}" class="btn btn-primary rounded-pill waves-effect waves-light">
                            Add Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <table id="myTable" class="table dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>S1</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alladminuser as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <img src="{{ (!empty($item->photo)) ? url('upload/admin_images/' . $item->photo) : url('upload/no_image.jpg') }}"
                                                 alt="{{ $item->name }}"
                                                 style="width:50px; height:40px">
                                        </td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>{{ $item->phone }}</td>
                                        <td>
                                            @foreach($item->roles as $role)
                                                <span class="badge badge-pill bg-danger">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if(isset($item->status) && $item->status === 'suspended')
                                                <span class="badge bg-danger">Suspended</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('edit.admin', $item->id) }}" class="btn btn-secondary btn-sm rounded-pill waves-effect" title="Edit">Edit</a>

                                            @can('admin.all')
                                                <!-- Change Password Button -->
                                                <a href="{{ route('change.admin.password', $item->id) }}" class="btn btn-info btn-sm rounded-pill waves-effect" title="Change Password">
                                                    <i class="mdi mdi-key"></i>
                                                </a>

                                                <!-- Suspend/Activate Button -->
                                                @if(isset($item->status) && $item->status === 'suspended')
                                                    <a href="{{ route('activate.admin', $item->id) }}" class="btn btn-success btn-sm rounded-pill waves-effect" title="Activate">
                                                        <i class="mdi mdi-account-check"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('suspend.admin', $item->id) }}" class="btn btn-warning btn-sm rounded-pill waves-effect" title="Suspend">
                                                        <i class="mdi mdi-account-off"></i>
                                                    </a>
                                                @endif
                                            @endcan

                                            <a href="{{ route('delete.admin', $item->id) }}" class="btn btn-danger btn-sm rounded-pill waves-effect waves-light" id="delete" title="Delete">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
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
