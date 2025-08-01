@extends('admin_master')
@section('admin')

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Reports</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Performance Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Service Toggle -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Performance Dashboard</h5>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="serviceType" id="carpet" value="carpet" checked>
                            <label class="btn btn-outline-primary" for="carpet">
                                <i class="bx bx-home"></i> Carpet Cleaning
                            </label>
                            <input type="radio" class="btn-check" name="serviceType" id="laundry" value="laundry">
                            <label class="btn btn-outline-primary" for="laundry">
                                <i class="bx bx-water"></i> Laundry Service
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" id="fromDate" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" id="toDate" value="{{ date('Y-m-t') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quick Select</label>
                            <select class="form-select" id="quickSelect">
                                <option value="custom">Custom Range</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="this_week">This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="this_month" selected>This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="last_3_months">Last 3 Months</option>
                                <option value="this_year">This Year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" id="refreshData">
                                <i class="bx bx-refresh"></i> Refresh Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4" id="metricsCards">
        <!-- Dynamic content loaded here -->
    </div>

    <!-- Charts Layout -->
    <div class="row mb-4">
        <!-- Revenue Trends -->
        <div class="col-xl-8 col-lg-7 mb-3">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0">Revenue Trends</h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 300px; position: relative;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="col-xl-4 col-lg-5 mb-3">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0">Payment Status</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <div style="height: 250px; position: relative;">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Volume Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0">Service Volume</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-lg-9">
                            <div style="height: 250px; position: relative;">
                                <canvas id="volumeChart"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="h-100 d-flex flex-column justify-content-center">
                                <div class="row text-center g-2">
                                    <div class="col-12 mb-3">
                                        <div class="p-3 bg-primary bg-opacity-10 rounded">
                                            <i class="bx bx-bar-chart-alt-2 text-primary mb-2" style="font-size: 1.5rem;"></i>
                                            <h4 class="text-primary mb-0" id="totalVolume">0</h4>
                                            <small class="text-muted">Total Carpets</small>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="p-3 bg-success bg-opacity-10 rounded">
                                            <i class="bx bx-trending-up text-success mb-2" style="font-size: 1.5rem;"></i>
                                            <h4 class="text-success mb-0" id="peakDay">0</h4>
                                            <small class="text-muted">Peak Day</small>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 bg-info bg-opacity-10 rounded">
                                            <i class="bx bx-calendar text-info mb-2" style="font-size: 1.5rem;"></i>
                                            <h4 class="text-info mb-0" id="avgDaily">0</h4>
                                            <small class="text-muted">Daily Average</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Analytics Row -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0">Customer Analytics</h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 280px; position: relative;">
                        <canvas id="customerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0">Customer Summary</h6>
                </div>
                <div class="card-body p-3">
                    <div class="h-100 d-flex flex-column justify-content-center">
                        <div class="row text-center g-3">
                            <div class="col-12">
                                <div class="p-4 bg-success bg-opacity-10 rounded">
                                    <i class="bx bx-user-plus text-success mb-3" style="font-size: 2rem;"></i>
                                    <h3 class="text-success mb-1" id="newCustomerCount">0</h3>
                                    <p class="text-muted mb-0">New Customers</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-4 bg-primary bg-opacity-10 rounded">
                                    <i class="bx bx-user text-primary mb-3" style="font-size: 2rem;"></i>
                                    <h3 class="text-primary mb-1" id="returningCustomerCount">0</h3>
                                    <p class="text-muted mb-0">Returning Customers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Operational Performance -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0">Operational Performance</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3" id="operationalMetrics">
                        <!-- Dynamic content loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Global variables for charts
let revenueChart, paymentChart, volumeChart, customerChart;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadDashboardData();

    // Event listeners
    document.querySelectorAll('input[name="serviceType"]').forEach(radio => {
        radio.addEventListener('change', loadDashboardData);
    });

    document.getElementById('refreshData').addEventListener('click', loadDashboardData);
    document.getElementById('quickSelect').addEventListener('change', handleQuickSelect);
});

function handleQuickSelect() {
    const quickSelect = document.getElementById('quickSelect').value;
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');
    const today = new Date();

    switch(quickSelect) {
        case 'today':
            fromDate.value = toDate.value = today.toISOString().split('T')[0];
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            fromDate.value = toDate.value = yesterday.toISOString().split('T')[0];
            break;
        case 'this_week':
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay());
            fromDate.value = startOfWeek.toISOString().split('T')[0];
            toDate.value = today.toISOString().split('T')[0];
            break;
        case 'last_week':
            const lastWeekEnd = new Date(today);
            lastWeekEnd.setDate(today.getDate() - today.getDay() - 1);
            const lastWeekStart = new Date(lastWeekEnd);
            lastWeekStart.setDate(lastWeekEnd.getDate() - 6);
            fromDate.value = lastWeekStart.toISOString().split('T')[0];
            toDate.value = lastWeekEnd.toISOString().split('T')[0];
            break;
        case 'this_month':
            fromDate.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            toDate.value = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            break;
        case 'last_month':
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            fromDate.value = lastMonth.toISOString().split('T')[0];
            toDate.value = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
            break;
        case 'last_3_months':
            fromDate.value = new Date(today.getFullYear(), today.getMonth() - 2, 1).toISOString().split('T')[0];
            toDate.value = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            break;
        case 'this_year':
            fromDate.value = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            toDate.value = today.toISOString().split('T')[0];
            break;
    }

    if (quickSelect !== 'custom') {
        loadDashboardData();
    }
}

function initializeCharts() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Total Revenue',
                data: [],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Paid Revenue',
                data: [],
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4,
                fill: false
            }, {
                label: 'Unpaid Revenue',
                data: [],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 10
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': KSh ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: {
                            size: 9
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 9
                        },
                        callback: function(value) {
                            return 'KSh ' + (value/1000) + 'k';
                        }
                    }
                }
            }
        }
    });

    // Payment Status Chart
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    paymentChart = new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Unpaid'],
            datasets: [{
                data: [0, 0],
                backgroundColor: ['#198754', '#dc3545'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 9
                        },
                        boxWidth: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((sum, val) => sum + val, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': KSh ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Volume Chart
    const volumeCtx = document.getElementById('volumeChart').getContext('2d');
    volumeChart = new Chart(volumeCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Orders Count',
                data: [],
                backgroundColor: '#0d6efd',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: {
                            size: 8
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 8
                        }
                    }
                }
            }
        }
    });

    // Customer Chart
    const customerCtx = document.getElementById('customerChart').getContext('2d');
    customerChart = new Chart(customerCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'New Customers',
                data: [],
                backgroundColor: '#198754'
            }, {
                label: 'Returning Customers',
                data: [],
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 9
                        },
                        boxWidth: 10
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    ticks: {
                        font: {
                            size: 8
                        }
                    }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 8
                        }
                    }
                }
            }
        }
    });
}

function loadDashboardData() {
    const serviceType = document.querySelector('input[name="serviceType"]:checked').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;

    // Show loading state
    document.getElementById('metricsCards').innerHTML = '<div class="col-12 text-center"><div class="spinner-border" role="status"></div></div>';

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const token = csrfToken ? csrfToken.getAttribute('content') : '';

    // Fetch data from backend
    fetch(`{{ route('api.performance.data') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
            service_type: serviceType,
            from_date: fromDate,
            to_date: toDate
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Dashboard data loaded:', data);
        updateMetricsCards(data.metrics, serviceType);
        updateCharts(data.charts, serviceType);
        updateOperationalMetrics(data.operational, serviceType);
        updateVolumeAnalytics(data.charts.volume, data.metrics);
        updateCustomerAnalytics(data.charts.customers);
    })
    .catch(error => {
        console.error('Error loading dashboard data:', error);
        document.getElementById('metricsCards').innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading data: ' + error.message + '. Please check the console for details.</div></div>';
    });
}

function updateMetricsCards(metrics, serviceType) {
    if (!metrics) {
        console.error('No metrics data provided');
        return;
    }

    const cards = `
        <div class="col-xl-3 col-md-6">
            <div class="card gradient-deepblue">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Total Revenue</p>
                            <h4 class="my-1 text-white">KSh ${(metrics.total_revenue || 0).toLocaleString()}</h4>
                            <p class="mb-0 font-13 text-white"><i class="bx bxs-up-arrow align-middle"></i>Since ${metrics.period_start || 'N/A'}</p>
                        </div>
                        <div class="widgets-icons bg-white text-primary ms-auto"><i class="bx bxs-wallet"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card gradient-orange">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Total Orders</p>
                            <h4 class="my-1 text-white">${metrics.total_orders || 0}</h4>
                            <p class="mb-0 font-13 text-white">${(metrics.avg_daily_orders || 0).toFixed(1)} avg/day</p>
                        </div>
                        <div class="widgets-icons bg-white text-warning ms-auto"><i class="bx bxs-shopping-bag"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card gradient-ohhappiness">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Paid Orders</p>
                            <h4 class="my-1 text-white">KSh ${(metrics.paid_revenue || 0).toLocaleString()}</h4>
                            <p class="mb-0 font-13 text-white">${(metrics.payment_rate || 0).toFixed(1)}% payment rate</p>
                        </div>
                        <div class="widgets-icons bg-white text-success ms-auto"><i class="bx bxs-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card gradient-ibiza">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Unpaid Orders</p>
                            <h4 class="my-1 text-white">${metrics.unpaid_orders || 0}</h4>
                            <p class="mb-0 font-13 text-white">KSh ${(metrics.unpaid_revenue || 0).toLocaleString()}</p>
                        </div>
                        <div class="widgets-icons bg-white text-danger ms-auto"><i class="bx bxs-x-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.getElementById('metricsCards').innerHTML = cards;
}

function updateCharts(chartData, serviceType) {
    if (!chartData) {
        console.error('No chart data provided');
        return;
    }

    try {
        // Update Revenue Chart
        if (chartData.revenue && revenueChart) {
            revenueChart.data.labels = chartData.revenue.labels || [];
            revenueChart.data.datasets[0].data = chartData.revenue.total || [];
            revenueChart.data.datasets[1].data = chartData.revenue.paid || [];
            revenueChart.data.datasets[2].data = chartData.revenue.unpaid || [];
            revenueChart.update();
        }

        // Update Payment Chart
        if (chartData.payment && paymentChart) {
            paymentChart.data.datasets[0].data = [
                chartData.payment.paid || 0,
                chartData.payment.unpaid || 0
            ];
            paymentChart.update();
        }

        // Update Volume Chart
        if (chartData.volume && volumeChart) {
            volumeChart.data.labels = chartData.volume.labels || [];
            volumeChart.data.datasets[0].data = chartData.volume.data || [];
            volumeChart.update();
        }

        // Update Customer Chart
        if (chartData.customers && customerChart) {
            customerChart.data.labels = chartData.customers.labels || [];
            customerChart.data.datasets[0].data = chartData.customers.new || [];
            customerChart.data.datasets[1].data = chartData.customers.returning || [];
            customerChart.update();
        }
    } catch (error) {
        console.error('Error updating charts:', error);
    }
}

function updateVolumeAnalytics(volumeData, metrics) {
    if (!volumeData || !metrics) return;

    const totalVolume = volumeData.data ? volumeData.data.reduce((sum, val) => sum + val, 0) : 0;
    const peakDay = volumeData.data ? Math.max(...volumeData.data) : 0;
    const avgDaily = metrics.avg_daily_orders || 0;

    document.getElementById('totalVolume').textContent = totalVolume;
    document.getElementById('peakDay').textContent = peakDay;
    document.getElementById('avgDaily').textContent = avgDaily.toFixed(1);
}

function updateCustomerAnalytics(customerData) {
    if (!customerData) return;

    const totalNew = customerData.new ? customerData.new.reduce((sum, val) => sum + val, 0) : 0;
    const totalReturning = customerData.returning ? customerData.returning.reduce((sum, val) => sum + val, 0) : 0;

    document.getElementById('newCustomerCount').textContent = totalNew;
    document.getElementById('returningCustomerCount').textContent = totalReturning;
}

function updateOperationalMetrics(operational, serviceType) {
    console.log('Operational data received:', operational);

    if (!operational) {
        console.error('No operational data provided');
        document.getElementById('operationalMetrics').innerHTML = '<div class="col-12"><p class="text-danger">No operational data available</p></div>';
        return;
    }

    const metrics = `
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending Deliveries</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${operational.pending_deliveries || 0}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-time-five fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${operational.completed_today || 0}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Processing Days</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${Math.round(operational.avg_processing_days || 0)}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-hourglass fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">New Customer Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${Math.round(operational.new_customers_rate || 0)}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-user-plus fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.getElementById('operationalMetrics').innerHTML = metrics;
}
</script>

<style>
.gradient-deepblue {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.gradient-orange {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.gradient-ohhappiness {
    background: linear-gradient(135deg, #00dbde 0%, #fc00ff 100%);
}
.gradient-ibiza {
    background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
}
.widgets-icons {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 24px;
}

/* Enhanced Operational Metrics */
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.text-xs {
    font-size: 0.7rem;
}
.font-weight-bold {
    font-weight: 700 !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}
.no-gutters {
    margin-right: 0;
    margin-left: 0;
}
.no-gutters > .col,
.no-gutters > [class*="col-"] {
    padding-right: 0;
    padding-left: 0;
}

/* Enhanced Analytics Sections */
.gap-3 {
    gap: 1rem !important;
}
.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}
.fw-bold {
    font-weight: 600 !important;
}
.fs-4 {
    font-size: 1.25rem !important;
}

/* Improved card spacing */
.card-body .row.align-items-center {
    min-height: 100px;
}

/* Better metric boxes */
.card-body .p-2.rounded {
    border: 1px solid rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.card-body .p-2.rounded:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Responsive chart containers */
@media (max-width: 768px) {
    .card-body div[style*="height"] {
        height: 200px !important;
    }

    .col-xl-8.col-lg-7 {
        order: 2;
    }

    .col-xl-4.col-lg-5 {
        order: 1;
        margin-bottom: 1rem;
    }
}

@media (max-width: 576px) {
    .card-body div[style*="height"] {
        height: 180px !important;
    }

    .card-body {
        padding: 1rem !important;
    }

    .card-header {
        padding: 0.75rem 1rem !important;
    }
}

/* Better chart responsiveness */
.chart-container {
    position: relative;
    width: 100%;
}

.chart-container canvas {
    width: 100% !important;
    height: auto !important;
}

/* Improved scrolling for mobile */
@media (max-width: 768px) {
    .page-content {
        padding: 1rem 0.5rem;
    }

    .row {
        margin: 0 -0.5rem;
    }

    .row > * {
        padding: 0 0.5rem;
    }
}
</style>

@endsection
