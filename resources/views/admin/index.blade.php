@extends('admin_master')
@section('admin')

@php
    use Carbon\Carbon;

    // Count carpets washed today using the date_received field.
    // Assuming $carpet is a Collection passed from the controller.
    $todayCarpetCount = $carpet->where('date_received', Carbon::today()->toDateString())->count();

    // Count today's clients.
    // (This example reuses the Carpet model; adjust if you have a dedicated Client model.)
    $todayClientCount = \App\Models\Carpet::whereDate('created_at', Carbon::today()->toDateString())->count();
@endphp



<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Dashboard</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Raha</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Summary Cards -->
        <div class="row">
            <!-- Carpets washed today (dynamic) -->
            <div class="col-xl-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Carpets washed today</p>
                                <h4 class="mb-2">{{ $todayCarpetCount }}</h4>

                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-primary rounded-3">
                                    <i class="fa-solid fa-water"></i>
                                </span>
                            </div>
                        </div>
                    </div><!-- end card-body -->
                </div><!-- end card -->
            </div><!-- end col -->

            <!-- Today's Clients (dynamic) -->
            <div class="col-xl-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Recent Clients</p>
                                <h4 class="mb-2">{{ $todayClientCount }}</h4>

                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-primary rounded-3">
                                    <i class="ri-user-3-line font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div><!-- end card-body -->
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->

        <!-- Carpet Data Table -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dropdown float-end">
                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical"></i>
                            </a>
                        </div>
                        <h4 class="card-title mb-4">Carpets Recently Washed</h4>
                        <div class="table-responsive">
                            <table class="table table-centered mb-0 align-middle table-hover table-nowrap" id="myTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Unique ID</th>
                                        <th>Size</th>
                                        <th>Price</th>
                                        <th>Payment Status</th>
                                        <th>Date Received</th>
                                        <th>Delivered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($carpet as $item)
                                        <tr>
                                            <td>{{ $item->uniqueid }}</td>
                                            <td>{{ $item->size }}</td>
                                            <td>{{ $item->price }}</td>
                                            <td>{{ $item->payment_status }}</td>
                                            <td>{{ $item->date_received }}</td>
                                            <td>{{ $item->delivered }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div><!-- end table-responsive -->
                    </div><!-- end card-body -->
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->
    </div><!-- end container-fluid -->
</div><!-- end page-content -->

{{-- Optional: DataTables initialization to preserve server-side ordering --}}
@push('scripts')
<script>
$(document).ready(function(){
    $('#myTable').DataTable({
        order: [], // This preserves the server-side ordering
        responsive: true
    });
});
</script>
@endpush

@endsection
