@extends('admin_master')
@section('admin')

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
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Carpets Processed Today</p>
                                <h4 class="mb-2">{{ $todayCarpetCount }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-primary rounded-3">
                                    <i class="fa-solid fa-water"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Laundry Processed Today</p>
                                <h4 class="mb-2">{{ $todayLaundryCount }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-success rounded-3">
                                    <i class="fa-solid fa-shirt"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">New Clients Today</p>
                                <h4 class="mb-2">{{ $todayClientCount }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-primary rounded-3">
                                    <i class="ri-user-3-line font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->can('admin.all'))
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Today's Revenue</p>
                                <h4 class="mb-2">KES {{ number_format($todayTotalRevenue, 2) }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-warning rounded-3">
                                    <i class="fa-solid fa-coins"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div><!-- end row -->

        <!-- Weekly Charts -->
        <div class="row">
            <div class="{{ Auth::user()->can('admin.all') ? 'col-xl-6' : 'col-xl-12' }}">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Items Processed This Week</h4>
                        <div id="weeklyItemsChart"></div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->can('admin.all'))
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Weekly Revenue (KES)</h4>
                        <div id="weeklyRevenueChart"></div>
                    </div>
                </div>
            </div>
            @endif
        </div><!-- end row -->

        <!-- Carpet Data Table -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Laundry Data Table -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Laundry Recently Processed</h4>
                        <div class="table-responsive">
                            <table class="table table-centered mb-0 align-middle table-hover table-nowrap" id="laundryTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Unique ID</th>
                                        <th>Name</th>
                                        <th>Total</th>
                                        <th>Payment Status</th>
                                        <th>Date Received</th>
                                        <th>Delivered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLaundry as $item)
                                        <tr>
                                            <td>{{ $item->unique_id }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ number_format($item->total ?? 0, 2) }}</td>
                                            <td>{{ $item->payment_status }}</td>
                                            <td>{{ $item->date_received }}</td>
                                            <td>{{ $item->delivered }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- end container-fluid -->
</div><!-- end page-content -->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function() {
    var labels = @json($weekLabels);
    var carpets = @json($weeklyCarpets);
    var laundry = @json($weeklyLaundry);

    // Items Processed Bar Chart
    var itemsOptions = {
        chart: {
            type: 'bar',
            height: 350,
            toolbar: { show: false }
        },
        series: [
            { name: 'Carpets', data: carpets },
            { name: 'Laundry', data: laundry }
        ],
        colors: ['#3b7ddd', '#28a745'],
        plotOptions: {
            bar: {
                columnWidth: '50%',
                borderRadius: 4
            }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: labels
        },
        yaxis: {
            title: { text: 'Items' },
            forceNiceScale: true,
            min: 0
        },
        legend: {
            position: 'top'
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + ' items';
                }
            }
        }
    };

    var itemsChart = new ApexCharts(document.querySelector('#weeklyItemsChart'), itemsOptions);
    itemsChart.render();

    @if(Auth::user()->can('admin.all'))
    var revenue = @json($weeklyRevenue);

    // Revenue Line/Area Chart
    var revenueOptions = {
        chart: {
            type: 'area',
            height: 350,
            toolbar: { show: false }
        },
        series: [{
            name: 'Revenue',
            data: revenue
        }],
        colors: ['#ffc107'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: labels
        },
        yaxis: {
            title: { text: 'KES' },
            min: 0,
            labels: {
                formatter: function(val) {
                    return 'KES ' + val.toLocaleString();
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return 'KES ' + val.toLocaleString();
                }
            }
        },
        markers: {
            size: 5,
            colors: ['#ffc107'],
            strokeColors: '#fff',
            strokeWidth: 2
        }
    };

    var revenueChart = new ApexCharts(document.querySelector('#weeklyRevenueChart'), revenueOptions);
    revenueChart.render();
    @endif
})();
</script>
@endpush
