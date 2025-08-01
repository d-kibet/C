@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">All Laundry Data</h4>
                    <div class="page-title-right d-flex align-items-center">
                        <!-- Add Carpet Button -->
                        <a
                            href="{{ route('add.laundry') }}"
                            class="btn btn-primary rounded-pill waves-effect waves-light me-2"
                        >
                            Add Laundry
                        </a>

                        <!-- CSV Download Button (shown only if user has permission) -->
                        @can('mpesa.compare')
                            <a
                                href="{{ route('reports.laundry.downloadAll') }}"
                                class="btn btn-secondary rounded-pill waves-effect waves-light"
                            >
                                <i class="mdi mdi-download"></i> Download Laundry CSV
                            </a>
                        @endcan
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("download-csv").addEventListener("click", function(){
            // Reference the table
            var table = document.getElementById("myTable");
            if (!table) {
                console.error("Table with ID 'myTable' not found.");
                return;
            }
            var rows = table.querySelectorAll("tr");
            var csv = [];

            // Loop through each row and collect cell text, skipping the last cell (action column)
            rows.forEach(function(row) {
                var cols = row.querySelectorAll("td, th");
                var rowData = [];
                // Loop through columns, excluding the last one
                for (var i = 0; i < cols.length - 1; i++) {
                    // Escape double quotes and wrap text in quotes
                    rowData.push('"' + cols[i].innerText.replace(/"/g, '""') + '"');
                }
                csv.push(rowData.join(","));
            });

            // Create a CSV file blob
            var csvFile = new Blob([csv.join("\n")], { type: "text/csv" });

            // Create a temporary download link and trigger a download
            var downloadLink = document.createElement("a");
            downloadLink.download = "laundry_data.csv";
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        });
    });
    </script>



@endsection
