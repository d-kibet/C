@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid" style="margin-top: 20px;">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">Deleted Records</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Trash</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Title -->

        <!-- Trashed Carpets -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="mdi mdi-delete-outline me-1"></i> Deleted Carpets
                            <span class="badge bg-danger ms-2">{{ $trashedCarpets->count() }}</span>
                        </h4>

                        @if($trashedCarpets->isEmpty())
                            <div class="alert alert-info">No deleted carpet records.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="trashedCarpetsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Unique ID</th>
                                            <th>Name</th>
                                            <th>Size</th>
                                            <th>Price</th>
                                            <th>Phone</th>
                                            <th>Payment Status</th>
                                            <th>Date Received</th>
                                            <th>Deleted At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trashedCarpets as $carpet)
                                            <tr>
                                                <td>{{ $carpet->uniqueid }}</td>
                                                <td>{{ $carpet->name }}</td>
                                                <td>{{ $carpet->size }}</td>
                                                <td>{{ number_format($carpet->price ?? 0, 2) }}</td>
                                                <td>{{ $carpet->phone }}</td>
                                                <td>{{ $carpet->payment_status }}</td>
                                                <td>{{ $carpet->date_received }}</td>
                                                <td>{{ $carpet->deleted_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <form action="{{ route('restore.carpet', $carpet->id) }}" method="POST" style="display:inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm rounded-pill" title="Restore">
                                                            <i class="mdi mdi-backup-restore"></i> Restore
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('force.delete.carpet', $carpet->id) }}" method="POST" style="display:inline" class="delete-form">
                                                        @csrf
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="btn btn-danger btn-sm rounded-pill" id="delete" title="Permanently Delete">
                                                            <i class="mdi mdi-delete-forever"></i> Permanent
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div><!-- end col -->
        </div>

        <!-- Trashed Laundry -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="mdi mdi-delete-outline me-1"></i> Deleted Laundry
                            <span class="badge bg-danger ms-2">{{ $trashedLaundry->count() }}</span>
                        </h4>

                        @if($trashedLaundry->isEmpty())
                            <div class="alert alert-info">No deleted laundry records.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="trashedLaundryTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Unique ID</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Total</th>
                                            <th>Payment Status</th>
                                            <th>Date Received</th>
                                            <th>Deleted At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trashedLaundry as $laundry)
                                            <tr>
                                                <td>{{ $laundry->unique_id }}</td>
                                                <td>{{ $laundry->name }}</td>
                                                <td>{{ $laundry->phone }}</td>
                                                <td>{{ number_format($laundry->total ?? 0, 2) }}</td>
                                                <td>{{ $laundry->payment_status }}</td>
                                                <td>{{ $laundry->date_received }}</td>
                                                <td>{{ $laundry->deleted_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <form action="{{ route('restore.laundry', $laundry->id) }}" method="POST" style="display:inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm rounded-pill" title="Restore">
                                                            <i class="mdi mdi-backup-restore"></i> Restore
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('force.delete.laundry', $laundry->id) }}" method="POST" style="display:inline" class="delete-form">
                                                        @csrf
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="btn btn-danger btn-sm rounded-pill" id="delete" title="Permanently Delete">
                                                            <i class="mdi mdi-delete-forever"></i> Permanent
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div><!-- end col -->
        </div>

    </div> <!-- container -->
</div> <!-- content -->

@endsection
