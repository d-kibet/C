@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <!-- Start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">All Carpet Data</h4>
                    <div class="page-title-right d-flex align-items-center">
                        <!-- Add Carpet Button -->
                        <a
                            href="{{ route('add.carpet') }}"
                            class="btn btn-primary rounded-pill waves-effect waves-light me-2"
                        >
                            Add Carpet
                        </a>

                        <!-- CSV Download Button (shown only if user has permission) -->
                        @can('admin.all')
                            <a
                                href="{{ route('reports.carpets.downloadAll') }}"
                                class="btn btn-secondary rounded-pill waves-effect waves-light"
                            >
                                <i class="mdi mdi-download"></i> Download All Carpets CSV
                            </a>
                        @endcan
                    </div>
                </div>

            </div>
        </div>
        <!-- End page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
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
                    </div>
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div><!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->
</div> <!-- content -->

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
        downloadLink.download = "carpet_data.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    });
});

    </script>


@endsection
