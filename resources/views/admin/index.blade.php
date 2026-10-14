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

        {{-- Branch context banner for SuperAdmin --}}
        @if(Auth::user()->isSuperAdmin())
        @php $activeBranchId = session('active_branch_id'); @endphp
        <div class="row mb-2">
            <div class="col-12">
                <div class="alert alert-{{ $activeBranchId ? 'primary' : 'warning' }} alert-dismissible py-2 mb-0 d-flex align-items-center gap-2" role="alert" style="font-size:13px;">
                    <i class="ri-eye-line me-1"></i>
                    @if($activeBranchId)
                        <strong>Branch view active.</strong> You are viewing data for <strong>{{ session()->get('active_branch_name', 'selected branch') }}</strong>.
                        <form method="POST" action="{{ route('branch.context.set') }}" class="d-inline ms-2">
                            @csrf<input type="hidden" name="branch_id" value="">
                            <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2">Switch to All Branches</button>
                        </form>
                    @else
                        <strong>Viewing all branches combined.</strong> Use the branch selector <kbd>&#9660;</kbd> in the header to filter by a specific branch.
                    @endif
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="font-size:11px;"></button>
                </div>
            </div>
        </div>
        @endif

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

        {{-- Branch breakdown table: only for SuperAdmin in "All Branches" mode --}}
        @if(Auth::user()->isSuperAdmin() && !session('active_branch_id') && $branchBreakdown && $branchBreakdown->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="card border-warning" style="border-width:2px!important;">
                    <div class="card-body py-3">
                        <h5 class="card-title mb-3">
                            <i class="ri-building-line me-1 text-warning"></i>
                            Today's Breakdown by Branch
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-centered table-hover align-middle mb-0" style="font-size:14px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Branch</th>
                                        <th>Location</th>
                                        <th class="text-center">Carpets Today</th>
                                        <th class="text-center">Laundry Today</th>
                                        <th class="text-end">Revenue Today (KES)</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branchBreakdown as $row)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary me-1">{{ $row['branch']->code }}</span>
                                            <strong>{{ $row['branch']->name }}</strong>
                                        </td>
                                        <td class="text-muted">{{ $row['branch']->location }}</td>
                                        <td class="text-center">{{ $row['carpets'] }}</td>
                                        <td class="text-center">{{ $row['laundry'] }}</td>
                                        <td class="text-end">{{ number_format($row['revenue'], 2) }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('branch.context.set') }}">
                                                @csrf
                                                <input type="hidden" name="branch_id" value="{{ $row['branch']->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2">
                                                    View Only
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-secondary fw-bold">
                                    <tr>
                                        <td colspan="2">Total (All Branches)</td>
                                        <td class="text-center">{{ $todayCarpetCount }}</td>
                                        <td class="text-center">{{ $todayLaundryCount }}</td>
                                        <td class="text-end">{{ number_format($todayTotalRevenue, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

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

        <!-- Recent Orders Table -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Recent Orders (Today &amp; Yesterday)</h4>
                        <div class="table-responsive">
                            <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Items</th>
                                        @if(Auth::user()->can('admin.all'))
                                        <th>Total (KES)</th>
                                        @endif
                                        @if(Auth::user()->isSuperAdmin() && !session('active_branch_id'))
                                        <th>Branch</th>
                                        @endif
                                        <th>Payment</th>
                                        <th>Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        <tr>
                                            <td>
                                                @if($order->type === 'carpet')
                                                    <span class="badge bg-primary">Carpet</span>
                                                @else
                                                    <span class="badge bg-success">Laundry</span>
                                                @endif
                                            </td>
                                            <td>{{ $order->name }}</td>
                                            <td>{{ $order->phone }}</td>
                                            <td>{{ $order->items_count ?? $order->items->count() }}</td>
                                            @if(Auth::user()->can('admin.all'))
                                            <td>{{ number_format($order->total, 2) }}</td>
                                            @endif
                                            @if(Auth::user()->isSuperAdmin() && !session('active_branch_id'))
                                            <td>
                                                @php $branchObj = $branchesById[$order->branch_id] ?? null; @endphp
                                                @if($branchObj)
                                                    <span class="badge bg-primary">{{ $branchObj->code }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            @endif
                                            <td>
                                                @if($order->payment_status === 'Paid')
                                                    <span class="badge bg-success">Paid</span>
                                                @elseif($order->payment_status === 'Partial')
                                                    <span class="badge bg-warning text-dark">Partial</span>
                                                @else
                                                    <span class="badge bg-danger">Not Paid</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($order->date_received)->format('d M Y') }}</td>
                                            <td>
                                                @if(empty($order->is_legacy))
                                                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info btn-sm rounded-pill waves-effect waves-light">View</a>
                                                @else
                                                <span class="badge bg-secondary">Archive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-3">No orders recorded today or yesterday.</td>
                                        </tr>
                                    @endforelse
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
