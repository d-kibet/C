<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carpet;
use App\Models\Mpesa;
use App\Models\Laundry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function CarpetsToday(Request $request){
        $date = $request->input('date', \Carbon\Carbon::today()->toDateString());
        $selectedDate = \Carbon\Carbon::parse($date)->toDateString();

        // Retrieve paid carpets for the selected date
        $paidCarpets = Carpet::whereDate('date_received', $selectedDate)
            ->where('payment_status', 'Paid')
            ->get();

        // Retrieve unpaid carpets for the selected date
        $unpaidCarpets = Carpet::whereDate('date_received', $selectedDate)
            ->where('payment_status', 'Not Paid')
            ->get();

        $totalPaidCarpets = $paidCarpets->sum('price');
        $totalUnpaidCarpets = $unpaidCarpets->sum('price');

        return view('reports.carpets_today', compact(
            'paidCarpets', 'unpaidCarpets',
            'totalPaidCarpets', 'totalUnpaidCarpets',
            'selectedDate'
        ));
    }

    public function LaundryToday(Request $request){
       // Get the selected date from the request or default to today.
    $date = $request->input('date', \Carbon\Carbon::today()->toDateString());
    $selectedDate = \Carbon\Carbon::parse($date)->toDateString();

    // Retrieve paid laundry records (assuming records with total > 0 are considered paid).
    $paidLaundry = Laundry::whereDate('date_received', $selectedDate)
        ->where('total', '>', 0)
        ->get();

    // Retrieve unpaid laundry records (where total is 0 or NULL).
    $unpaidLaundry = Laundry::whereDate('date_received', $selectedDate)
        ->where(function ($query) {
            $query->where('total', '=', 0)
                  ->orWhereNull('total');
        })
        ->get();

    // Calculate totals.
    $totalLaundryPaid = $paidLaundry->sum('total');
    $totalLaundryUnpaid = $unpaidLaundry->sum('price');
    $grandTotal = $totalLaundryPaid + $totalLaundryUnpaid;

    return view('reports.laundry_today', compact(
        'paidLaundry',
        'unpaidLaundry',
        'totalLaundryPaid',
        'totalLaundryUnpaid',
        'grandTotal',
        'selectedDate'
    ));
    }

    public function mpesaToday(Request $request)
    {

    $selectedDate = $request->input('date', \Carbon\Carbon::today()->toDateString());
    $today = \Carbon\Carbon::parse($selectedDate);

    // Calculate today's total
    $todayTotal = Mpesa::whereDate('date', $today)
        ->get()
        ->sum(function ($item) {
            return (float)$item->cash
                 + (float)$item->float
                 + (float)($item->working ?? 0)
                 + (float)$item->account;
        });


    $yesterday = $today->copy()->subDay();
    $yesterdayTotal = Mpesa::whereDate('date', $yesterday)
        ->get()
        ->sum(function ($item) {
            return (float)$item->cash
                 + (float)$item->float
                 + (float)($item->working ?? 0)
                 + (float)$item->account;
        });


    $summaryDifference = $yesterdayTotal - $todayTotal;


    $mpesaRecords = Mpesa::whereDate('date', $today)
        ->orderBy('date', 'desc')
        ->get();

    return view('reports.mpesa_today', [
        'mpesaRecords'     => $mpesaRecords,
        'totalMPesa'       => $todayTotal,
        'totalDifference'  => $summaryDifference,
        'selectedDate'     => $selectedDate,
    ]);
    }

    //Added for specific reports
    public function index()
    {
        // Set defaults to current month and year
        $currentMonth = Carbon::now()->format('m');
        $currentYear  = Carbon::now()->format('Y');
        return view('reports.specific_report', compact('currentMonth', 'currentYear'));
    }

    public function handle(Request $request)
    {
        $data = $request->validate([
            'type'  => 'required|in:carpet,laundry',
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer'
        ]);

        $type  = $data['type'];
        $month = $data['month'];
        $year  = $data['year'];

        // Redirect to the appropriate route with month/year as query parameters
        if ($type == 'carpet') {
            return redirect()->route('reports.carpets.viewMonth', ['month' => $month, 'year' => $year]);
        } else {
            return redirect()->route('reports.laundry.viewMonth', ['month' => $month, 'year' => $year]);
        }
    }

    public function performance()
    {
        return view('reports.performance');
    }

    public function performanceData(Request $request)
    {
        $serviceType = $request->input('service_type', 'carpet');
        $fromDate = Carbon::parse($request->input('from_date', Carbon::now()->startOfMonth()));
        $toDate = Carbon::parse($request->input('to_date', Carbon::now()->endOfMonth()));

        if ($serviceType === 'carpet') {
            return $this->getCarpetPerformanceData($fromDate, $toDate);
        } else {
            return $this->getLaundryPerformanceData($fromDate, $toDate);
        }
    }

    private function getCarpetPerformanceData($fromDate, $toDate)
    {
        // Get carpet data for the period
        $carpets = Carpet::whereBetween('date_received', [$fromDate, $toDate])
            ->orderBy('date_received', 'desc')
            ->get();

        // Calculate metrics
        $totalRevenue = $carpets->sum('price');
        $paidCarpets = $carpets->where('payment_status', 'Paid');
        $unpaidCarpets = $carpets->where('payment_status', 'Not Paid');
        $paidRevenue = $paidCarpets->sum('price');
        $unpaidRevenue = $unpaidCarpets->sum('price');
        $totalOrders = $carpets->count();
        $unpaidOrders = $unpaidCarpets->count();
        $paymentRate = $totalOrders > 0 ? ($paidCarpets->count() / $totalOrders) * 100 : 0;
        $avgDailyOrders = $totalOrders / max(1, $fromDate->diffInDays($toDate) + 1);

        // Daily revenue data for chart
        $dailyRevenue = $carpets->groupBy(function($item) {
            return Carbon::parse($item->date_received)->format('Y-m-d');
        })->map(function($dayCarpets) {
            $paidAmount = $dayCarpets->where('payment_status', 'Paid')->sum('price');
            $totalAmount = $dayCarpets->sum('price');
            return [
                'total' => $totalAmount,
                'paid' => $paidAmount,
                'unpaid' => $totalAmount - $paidAmount
            ];
        });

        // Fill missing dates with zero values
        $revenueLabels = [];
        $revenueTotal = [];
        $revenuePaid = [];
        $revenueUnpaid = [];
        $currentDate = $fromDate->copy();
        
        while ($currentDate <= $toDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $revenueLabels[] = $currentDate->format('M d');
            $revenueTotal[] = $dailyRevenue[$dateStr]['total'] ?? 0;
            $revenuePaid[] = $dailyRevenue[$dateStr]['paid'] ?? 0;
            $revenueUnpaid[] = $dailyRevenue[$dateStr]['unpaid'] ?? 0;
            $currentDate->addDay();
        }

        // Volume data (daily order count)
        $dailyVolume = $carpets->groupBy(function($item) {
            return Carbon::parse($item->date_received)->format('Y-m-d');
        })->map(function($dayCarpets) {
            return $dayCarpets->count();
        });

        $volumeData = [];
        $currentDate = $fromDate->copy();
        while ($currentDate <= $toDate) {
            $volumeData[] = $dailyVolume[$currentDate->format('Y-m-d')] ?? 0;
            $currentDate->addDay();
        }

        // Customer analytics (new vs returning)
        $customerData = $this->getCarpetCustomerAnalytics($carpets, $fromDate, $toDate);

        // Operational metrics
        $pendingDeliveries = Carpet::where('delivered', 'Not Delivered')->count();
        $completedToday = Carpet::whereDate('date_delivered', Carbon::today())
            ->where('delivered', 'Delivered')->count();
        
        // Calculate average processing days for delivered items
        $deliveredCarpets = $carpets->where('delivered', 'Delivered');
        $avgProcessingDays = 0;
        if ($deliveredCarpets->count() > 0) {
            $totalDays = $deliveredCarpets->map(function($carpet) {
                $received = Carbon::parse($carpet->date_received);
                $delivered = Carbon::parse($carpet->date_delivered);
                return $received->diffInDays($delivered);
            })->sum();
            $avgProcessingDays = $totalDays / $deliveredCarpets->count();
        }

        // New customers rate
        $newCustomersCount = $customerData['totals']['new'];
        $newCustomersRate = $totalOrders > 0 ? ($newCustomersCount / $totalOrders) * 100 : 0;

        return response()->json([
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'paid_revenue' => $paidRevenue,
                'total_orders' => $totalOrders,
                'unpaid_orders' => $unpaidOrders,
                'unpaid_revenue' => $unpaidRevenue,
                'payment_rate' => round($paymentRate, 1),
                'avg_daily_orders' => round($avgDailyOrders, 1),
                'period_start' => $fromDate->format('M d, Y')
            ],
            'charts' => [
                'revenue' => [
                    'labels' => $revenueLabels,
                    'total' => $revenueTotal,
                    'paid' => $revenuePaid,
                    'unpaid' => $revenueUnpaid
                ],
                'payment' => [
                    'paid' => $paidRevenue,
                    'unpaid' => $totalRevenue - $paidRevenue
                ],
                'volume' => [
                    'labels' => $revenueLabels,
                    'data' => $volumeData
                ],
                'customers' => $customerData['chart']
            ],
            'operational' => [
                'pending_deliveries' => $pendingDeliveries,
                'completed_today' => $completedToday,
                'avg_processing_days' => round($avgProcessingDays, 1),
                'new_customers_rate' => round($newCustomersRate, 1)
            ]
        ]);
    }

    private function getLaundryPerformanceData($fromDate, $toDate)
    {
        // Get laundry data for the period
        $laundry = Laundry::whereBetween('date_received', [$fromDate, $toDate])
            ->orderBy('date_received', 'desc')
            ->get();

        // Calculate metrics
        $totalRevenue = $laundry->sum('total');
        $paidLaundry = $laundry->where('payment_status', 'Paid');
        $unpaidLaundry = $laundry->where('payment_status', 'Not Paid');
        $paidRevenue = $paidLaundry->sum('total');
        $unpaidRevenue = $unpaidLaundry->sum('total');
        $totalOrders = $laundry->count();
        $unpaidOrders = $unpaidLaundry->count();
        $paymentRate = $totalOrders > 0 ? ($paidLaundry->count() / $totalOrders) * 100 : 0;
        $avgDailyOrders = $totalOrders / max(1, $fromDate->diffInDays($toDate) + 1);

        // Daily revenue data for chart
        $dailyRevenue = $laundry->groupBy(function($item) {
            return Carbon::parse($item->date_received)->format('Y-m-d');
        })->map(function($dayLaundry) {
            $paidAmount = $dayLaundry->where('payment_status', 'Paid')->sum('total');
            $totalAmount = $dayLaundry->sum('total');
            return [
                'total' => $totalAmount,
                'paid' => $paidAmount,
                'unpaid' => $totalAmount - $paidAmount
            ];
        });

        // Fill missing dates with zero values
        $revenueLabels = [];
        $revenueTotal = [];
        $revenuePaid = [];
        $revenueUnpaid = [];
        $currentDate = $fromDate->copy();
        
        while ($currentDate <= $toDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $revenueLabels[] = $currentDate->format('M d');
            $revenueTotal[] = $dailyRevenue[$dateStr]['total'] ?? 0;
            $revenuePaid[] = $dailyRevenue[$dateStr]['paid'] ?? 0;
            $revenueUnpaid[] = $dailyRevenue[$dateStr]['unpaid'] ?? 0;
            $currentDate->addDay();
        }

        // Volume data (daily order count)
        $dailyVolume = $laundry->groupBy(function($item) {
            return Carbon::parse($item->date_received)->format('Y-m-d');
        })->map(function($dayLaundry) {
            return $dayLaundry->count();
        });

        $volumeData = [];
        $currentDate = $fromDate->copy();
        while ($currentDate <= $toDate) {
            $volumeData[] = $dailyVolume[$currentDate->format('Y-m-d')] ?? 0;
            $currentDate->addDay();
        }

        // Customer analytics (new vs returning)
        $customerData = $this->getLaundryCustomerAnalytics($laundry, $fromDate, $toDate);

        // Operational metrics
        $pendingDeliveries = Laundry::where('delivered', 'Not Delivered')->count();
        $completedToday = Laundry::whereDate('date_delivered', Carbon::today())
            ->where('delivered', 'Delivered')->count();
        
        // Calculate average processing days for delivered items
        $deliveredLaundry = $laundry->where('delivered', 'Delivered');
        $avgProcessingDays = 0;
        if ($deliveredLaundry->count() > 0) {
            $totalDays = $deliveredLaundry->map(function($item) {
                $received = Carbon::parse($item->date_received);
                $delivered = Carbon::parse($item->date_delivered);
                return $received->diffInDays($delivered);
            })->sum();
            $avgProcessingDays = $totalDays / $deliveredLaundry->count();
        }

        // New customers rate
        $newCustomersCount = $customerData['totals']['new'];
        $newCustomersRate = $totalOrders > 0 ? ($newCustomersCount / $totalOrders) * 100 : 0;

        return response()->json([
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'paid_revenue' => $paidRevenue,
                'total_orders' => $totalOrders,
                'unpaid_orders' => $unpaidOrders,
                'unpaid_revenue' => $unpaidRevenue,
                'payment_rate' => round($paymentRate, 1),
                'avg_daily_orders' => round($avgDailyOrders, 1),
                'period_start' => $fromDate->format('M d, Y')
            ],
            'charts' => [
                'revenue' => [
                    'labels' => $revenueLabels,
                    'total' => $revenueTotal,
                    'paid' => $revenuePaid,
                    'unpaid' => $revenueUnpaid
                ],
                'payment' => [
                    'paid' => $paidRevenue,
                    'unpaid' => $totalRevenue - $paidRevenue
                ],
                'volume' => [
                    'labels' => $revenueLabels,
                    'data' => $volumeData
                ],
                'customers' => $customerData['chart']
            ],
            'operational' => [
                'pending_deliveries' => $pendingDeliveries,
                'completed_today' => $completedToday,
                'avg_processing_days' => round($avgProcessingDays, 1),
                'new_customers_rate' => round($newCustomersRate, 1)
            ]
        ]);
    }

    private function getCarpetCustomerAnalytics($carpets, $fromDate, $toDate)
    {
        $weeklyData = [];
        $currentDate = $fromDate->copy()->startOfWeek();
        
        while ($currentDate <= $toDate) {
            $weekEnd = $currentDate->copy()->endOfWeek();
            $weekCarpets = $carpets->filter(function($carpet) use ($currentDate, $weekEnd) {
                $orderDate = Carbon::parse($carpet->date_received);
                return $orderDate >= $currentDate && $orderDate <= $weekEnd;
            });

            $newCustomers = $weekCarpets->filter(function($carpet) use ($currentDate) {
                return !Carpet::where('phone', $carpet->phone)
                    ->where('date_received', '<', $currentDate)
                    ->exists();
            })->count();

            $returningCustomers = $weekCarpets->count() - $newCustomers;

            $weeklyData[] = [
                'label' => $currentDate->format('M d'),
                'new' => $newCustomers,
                'returning' => $returningCustomers
            ];

            $currentDate->addWeek();
        }

        return [
            'chart' => [
                'labels' => array_column($weeklyData, 'label'),
                'new' => array_column($weeklyData, 'new'),
                'returning' => array_column($weeklyData, 'returning')
            ],
            'totals' => [
                'new' => array_sum(array_column($weeklyData, 'new')),
                'returning' => array_sum(array_column($weeklyData, 'returning'))
            ]
        ];
    }

    private function getLaundryCustomerAnalytics($laundry, $fromDate, $toDate)
    {
        $weeklyData = [];
        $currentDate = $fromDate->copy()->startOfWeek();
        
        while ($currentDate <= $toDate) {
            $weekEnd = $currentDate->copy()->endOfWeek();
            $weekLaundry = $laundry->filter(function($item) use ($currentDate, $weekEnd) {
                $orderDate = Carbon::parse($item->date_received);
                return $orderDate >= $currentDate && $orderDate <= $weekEnd;
            });

            $newCustomers = $weekLaundry->filter(function($item) use ($currentDate) {
                return !Laundry::where('phone', $item->phone)
                    ->where('date_received', '<', $currentDate)
                    ->exists();
            })->count();

            $returningCustomers = $weekLaundry->count() - $newCustomers;

            $weeklyData[] = [
                'label' => $currentDate->format('M d'),
                'new' => $newCustomers,
                'returning' => $returningCustomers
            ];

            $currentDate->addWeek();
        }

        return [
            'chart' => [
                'labels' => array_column($weeklyData, 'label'),
                'new' => array_column($weeklyData, 'new'),
                'returning' => array_column($weeklyData, 'returning')
            ],
            'totals' => [
                'new' => array_sum(array_column($weeklyData, 'new')),
                'returning' => array_sum(array_column($weeklyData, 'returning'))
            ]
        ];
    }

}
