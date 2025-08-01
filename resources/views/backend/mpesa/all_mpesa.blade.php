@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <!-- Start Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">All Mpesa Data</h4>
                    <div class="page-title-right d-flex align-items-center">
                        <!-- Add Mpesa Button -->
                        <a
                            href="{{ route('add.mpesa') }}"
                            class="btn btn-primary rounded-pill waves-effect waves-light me-2"
                        >
                            Add Mpesa
                        </a>

                        <!-- CSV Download Button (shown only if user has permission) -->
                        @can('mpesa.compare')
                            <a
                                href="{{ route('reports.mpesa.downloadAll') }}"
                                class="btn btn-secondary rounded-pill waves-effect waves-light"
                            >
                                <i class="mdi mdi-download"></i> Download Mpesa CSV
                            </a>
                        @endcan
                    </div>
                </div>

            </div>
        </div>
        <!-- End Page Title -->

        <!-- Summary Section -->
        @can('mpesa.compare')

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Today's Total</h5>
                        <p>{{ $todayTotal }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Yesterday's Total</h5>
                        <p>{{ $yesterdayTotal }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Difference</h5>
                        <p>{{ $summaryDifference }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endcan
        <!-- End Summary Section -->

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="myTable" class="table dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Cash</th>
                                    <th>Float</th>
                                    <th>Working</th>
                                    <th>Account</th>
                                    <th>Total</th>
                                    <th>Difference</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mpesaData as $item)
                                    <tr>
                                        <td>{{ $item->date }}</td>
                                        <td>{{ $item->cash }}</td>
                                        <td>{{ $item->float }}</td>
                                        <td>{{ $item->working }}</td>
                                        <td>{{ $item->account }}</td>
                                        <td>{{ $item->total }}</td>
                                        <td>{{ $item->difference }}</td>
                                        <td>
                                            @can('mpesa.edit')
                                                <a href="{{ route('edit.mpesa', $item->id) }}" class="btn btn-secondary rounded-pill waves-effect" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            @endcan

                                            @can('mpesa.delete')
                                                <a href="{{ route('delete.mpesa', $item->id) }}" class="btn btn-danger rounded-pill waves-effect waves-light" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div> <!-- end container-fluid -->


</div> <!-- end content -->

<!-- CSV Download Script -->
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
            downloadLink.download = "mpesa_data.csv";
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        });
    });
    </script>


@endsection
