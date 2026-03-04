<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carpet;
use App\Models\Laundry;
use App\Models\Order;
use App\Models\Mpesa;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function CarpetsToday(Request $request){
        $date = $request->input('date', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($date)->toDateString();

        $allOrders = Order::with('items')
            ->where('type', 'carpet')
            ->whereDate('date_received', $selectedDate)
            ->get();

        $oldCarpets = Carpet::withTrashed()->whereDate('date_received', $selectedDate)->get();
        if ($oldCarpets->isNotEmpty()) {
            $allOrders = $allOrders->concat($this->carpetsToFakeOrders($oldCarpets));
        }

        $paidOrders   = $allOrders->where('payment_status', 'Paid');
        $unpaidOrders = $allOrders->whereIn('payment_status', ['Not Paid', 'Partial']);

        $totalPaidCarpets   = $paidOrders->sum('total');
        $totalUnpaidCarpets = $unpaidOrders->sum('total');

        return view('reports.carpets_today', compact(
            'paidOrders', 'unpaidOrders',
            'totalPaidCarpets', 'totalUnpaidCarpets',
            'selectedDate'
        ));
    }

    public function LaundryToday(Request $request){
        $date = $request->input('date', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($date)->toDateString();

        $allOrders = Order::with('items')
            ->where('type', 'laundry')
            ->whereDate('date_received', $selectedDate)
            ->get();

        $oldLaundries = Laundry::withTrashed()->whereDate('date_received', $selectedDate)->get();
        if ($oldLaundries->isNotEmpty()) {
            $allOrders = $allOrders->concat($this->laundriesToFakeOrders($oldLaundries));
        }

        $paidOrders   = $allOrders->where('payment_status', 'Paid');
        $unpaidOrders = $allOrders->whereIn('payment_status', ['Not Paid', 'Partial']);

        $totalLaundryPaid   = (float) $paidOrders->sum('total');
        $totalLaundryUnpaid = (float) $unpaidOrders->sum('total');
        $grandTotal = $totalLaundryPaid + $totalLaundryUnpaid;

        return view('reports.laundry_today', compact(
            'paidOrders', 'unpaidOrders',
            'totalLaundryPaid', 'totalLaundryUnpaid',
            'grandTotal', 'selectedDate'
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
        } elseif ($serviceType === 'laundry') {
            return $this->getLaundryPerformanceData($fromDate, $toDate);
        } else {
            return $this->getExpensePerformanceData($fromDate, $toDate);
        }
    }

    private function getCarpetPerformanceData($fromDate, $toDate)
    {
        $orders = Order::with('items')->where('type', 'carpet')
            ->whereBetween('date_received', [$fromDate, $toDate])
            ->orderBy('date_received', 'desc')
            ->get();

        $totalRevenue  = $orders->sum('total');
        $paidOrders    = $orders->where('payment_status', 'Paid');
        $unpaidOrders  = $orders->where('payment_status', 'Not Paid');
        $paidRevenue   = $paidOrders->sum('total');
        $unpaidRevenue = $unpaidOrders->sum('total');
        $totalCount    = $orders->count();
        $unpaidCount   = $unpaidOrders->count();
        $paymentRate   = $totalCount > 0 ? ($paidOrders->count() / $totalCount) * 100 : 0;
        $avgDailyOrders = $totalCount / max(1, $fromDate->diffInDays($toDate) + 1);

        $dailyRevenue = $orders->groupBy(function($item) {
            return Carbon::parse($item->date_received)->format('Y-m-d');
        })->map(function($dayOrders) {
            $paidAmount  = $dayOrders->where('payment_status', 'Paid')->sum('total');
            $totalAmount = $dayOrders->sum('total');
            return ['total' => $totalAmount, 'paid' => $paidAmount, 'unpaid' => $totalAmount - $paidAmount];
        });

        $revenueLabels = []; $revenueTotal = []; $revenuePaid = []; $revenueUnpaid = [];
        $currentDate = $fromDate->copy();
        while ($currentDate <= $toDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $revenueLabels[] = $currentDate->format('M d');
            $revenueTotal[]  = $dailyRevenue[$dateStr]['total']  ?? 0;
            $revenuePaid[]   = $dailyRevenue[$dateStr]['paid']   ?? 0;
            $revenueUnpaid[] = $dailyRevenue[$dateStr]['unpaid'] ?? 0;
            $currentDate->addDay();
        }

        $dailyVolume = $orders->groupBy(function($item) {
            return Carbon::parse($item->date_received)->format('Y-m-d');
        })->map(fn($d) => $d->count());

        $volumeData = [];
        $currentDate = $fromDate->copy();
        while ($currentDate <= $toDate) {
            $volumeData[] = $dailyVolume[$currentDate->format('Y-m-d')] ?? 0;
            $currentDate->addDay();
        }

        $customerData = $this->getCarpetCustomerAnalytics($orders, $fromDate, $toDate);

        $pendingDeliveries = Order::where('type', 'carpet')
            ->whereHas('items', fn($q) => $q->where('delivered', 'Not Delivered'))->count();
        $completedToday = Order::where('type', 'carpet')
            ->whereDoesntHave('items', fn($q) => $q->where('delivered', 'Not Delivered'))
            ->whereHas('items')
            ->whereDate('updated_at', Carbon::today())->count();

        $deliveredOrders = $orders->filter(
            fn($o) => $o->items->isNotEmpty() && $o->items->every(fn($i) => $i->delivered === 'Delivered')
        );
        $avgProcessingDays = 0;
        if ($deliveredOrders->count() > 0) {
            $totalDays = $deliveredOrders->map(function($order) {
                return Carbon::parse($order->date_received)->diffInDays(Carbon::parse($order->updated_at));
            })->sum();
            $avgProcessingDays = $totalDays / $deliveredOrders->count();
        }

        $newCustomersCount = $customerData['totals']['new'];
        $newCustomersRate  = $totalCount > 0 ? ($newCustomersCount / $totalCount) * 100 : 0;

        return response()->json([
            'metrics' => [
                'total_revenue'    => $totalRevenue,
                'paid_revenue'     => $paidRevenue,
                'total_orders'     => $totalCount,
                'unpaid_orders'    => $unpaidCount,
                'unpaid_revenue'   => $unpaidRevenue,
                'payment_rate'     => round($paymentRate, 1),
                'avg_daily_orders' => round($avgDailyOrders, 1),
                'period_start'     => $fromDate->format('M d, Y')
            ],
            'charts' => [
                'revenue'   => ['labels' => $revenueLabels, 'total' => $revenueTotal, 'paid' => $revenuePaid, 'unpaid' => $revenueUnpaid],
                'payment'   => ['paid' => $paidRevenue, 'unpaid' => $totalRevenue - $paidRevenue],
                'volume'    => ['labels' => $revenueLabels, 'data' => $volumeData],
                'customers' => $customerData['chart']
            ],
            'operational' => [
                'pending_deliveries'  => $pendingDeliveries,
                'completed_today'     => $completedToday,
                'avg_processing_days' => round($avgProcessingDays, 1),
                'new_customers_rate'  => round($newCustomersRate, 1)
            ]
        ]);
    }

    private function getLaundryPerformanceData($fromDate, $toDate)
    {
        $orders = Order::with('items')->where('type', 'laundry')
            ->whereBetween('date_received', [$fromDate, $toDate])
            ->orderBy('date_received', 'desc')
            ->get();

        $totalRevenue  = $orders->sum('total');
        $paidOrders    = $orders->where('payment_status', 'Paid');
        $unpaidOrders  = $orders->where('payment_status', 'Not Paid');
        $paidRevenue   = $paidOrders->sum('total');
        $unpaidRevenue = $unpaidOrders->sum('total');
        $totalCount    = $orders->count();
        $unpaidCount   = $unpaidOrders->count();
        $paymentRate   = $totalCount > 0 ? ($paidOrders->count() / $totalCount) * 100 : 0;
        $avgDailyOrders = $totalCount / max(1, $fromDate->diffInDays($toDate) + 1);

        $dailyRevenue = $orders->groupBy(function($item) {
            return Carbon::parse($item->date_received)->format('Y-m-d');
        })->map(function($dayOrders) {
            $paidAmount  = $dayOrders->where('payment_status', 'Paid')->sum('total');
            $totalAmount = $dayOrders->sum('total');
            return ['total' => $totalAmount, 'paid' => $paidAmount, 'unpaid' => $totalAmount - $paidAmount];
        });

        $revenueLabels = []; $revenueTotal = []; $revenuePaid = []; $revenueUnpaid = [];
        $currentDate = $fromDate->copy();
        while ($currentDate <= $toDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $revenueLabels[] = $currentDate->format('M d');
            $revenueTotal[]  = $dailyRevenue[$dateStr]['total']  ?? 0;
            $revenuePaid[]   = $dailyRevenue[$dateStr]['paid']   ?? 0;
            $revenueUnpaid[] = $dailyRevenue[$dateStr]['unpaid'] ?? 0;
            $currentDate->addDay();
        }

        $dailyVolume = $orders->groupBy(function($item) {
            return Carbon::parse($item->date_received)->format('Y-m-d');
        })->map(fn($d) => $d->count());

        $volumeData = [];
        $currentDate = $fromDate->copy();
        while ($currentDate <= $toDate) {
            $volumeData[] = $dailyVolume[$currentDate->format('Y-m-d')] ?? 0;
            $currentDate->addDay();
        }

        $customerData = $this->getLaundryCustomerAnalytics($orders, $fromDate, $toDate);

        $pendingDeliveries = Order::where('type', 'laundry')
            ->whereHas('items', fn($q) => $q->where('delivered', 'Not Delivered'))->count();
        $completedToday = Order::where('type', 'laundry')
            ->whereDoesntHave('items', fn($q) => $q->where('delivered', 'Not Delivered'))
            ->whereHas('items')
            ->whereDate('updated_at', Carbon::today())->count();

        $deliveredOrders = $orders->filter(
            fn($o) => $o->items->isNotEmpty() && $o->items->every(fn($i) => $i->delivered === 'Delivered')
        );
        $avgProcessingDays = 0;
        if ($deliveredOrders->count() > 0) {
            $totalDays = $deliveredOrders->map(function($order) {
                return Carbon::parse($order->date_received)->diffInDays(Carbon::parse($order->updated_at));
            })->sum();
            $avgProcessingDays = $totalDays / $deliveredOrders->count();
        }

        $newCustomersCount = $customerData['totals']['new'];
        $newCustomersRate  = $totalCount > 0 ? ($newCustomersCount / $totalCount) * 100 : 0;

        return response()->json([
            'metrics' => [
                'total_revenue'    => $totalRevenue,
                'paid_revenue'     => $paidRevenue,
                'total_orders'     => $totalCount,
                'unpaid_orders'    => $unpaidCount,
                'unpaid_revenue'   => $unpaidRevenue,
                'payment_rate'     => round($paymentRate, 1),
                'avg_daily_orders' => round($avgDailyOrders, 1),
                'period_start'     => $fromDate->format('M d, Y')
            ],
            'charts' => [
                'revenue'   => ['labels' => $revenueLabels, 'total' => $revenueTotal, 'paid' => $revenuePaid, 'unpaid' => $revenueUnpaid],
                'payment'   => ['paid' => $paidRevenue, 'unpaid' => $totalRevenue - $paidRevenue],
                'volume'    => ['labels' => $revenueLabels, 'data' => $volumeData],
                'customers' => $customerData['chart']
            ],
            'operational' => [
                'pending_deliveries'  => $pendingDeliveries,
                'completed_today'     => $completedToday,
                'avg_processing_days' => round($avgProcessingDays, 1),
                'new_customers_rate'  => round($newCustomersRate, 1)
            ]
        ]);
    }

    private function getCarpetCustomerAnalytics($orders, $fromDate, $toDate)
    {
        // Batch-fetch the absolute first-seen date per unique_id from both systems
        $allIds = $orders->flatMap(fn($o) => $o->items->pluck('unique_id'))->filter()->unique()->toArray();

        $ordersFirst = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.type', 'carpet')->whereIn('order_items.unique_id', $allIds)
            ->groupBy('order_items.unique_id')
            ->select('order_items.unique_id', DB::raw('MIN(orders.date_received) as first_seen'))
            ->pluck('first_seen', 'unique_id')
            ->toArray();

        $legacyFirst = Carpet::withTrashed()->whereIn('uniqueid', $allIds)
            ->groupBy('uniqueid')
            ->select('uniqueid', DB::raw('MIN(date_received) as first_seen'))
            ->pluck('first_seen', 'uniqueid')
            ->toArray();

        $firstSeenMap = [];
        foreach ($allIds as $uid) {
            $dates = array_filter([$ordersFirst[$uid] ?? null, $legacyFirst[$uid] ?? null]);
            $firstSeenMap[$uid] = $dates ? min($dates) : null;
        }

        $weeklyData  = [];
        $currentDate = $fromDate->copy()->startOfWeek();

        while ($currentDate <= $toDate) {
            $weekEnd    = $currentDate->copy()->endOfWeek();
            $weekOrders = $orders->filter(function($order) use ($currentDate, $weekEnd) {
                $d = Carbon::parse($order->date_received);
                return $d >= $currentDate && $d <= $weekEnd;
            });

            $newCustomers = $weekOrders->filter(function($order) use ($currentDate, $firstSeenMap) {
                $ids = $order->items->pluck('unique_id')->filter()->toArray();
                if (empty($ids)) return false;
                foreach ($ids as $uid) {
                    if (isset($firstSeenMap[$uid]) && Carbon::parse($firstSeenMap[$uid]) < $currentDate) {
                        return false; // at least one uid seen before this week → returning
                    }
                }
                return true;
            })->count();

            $weeklyData[] = [
                'label'     => $currentDate->format('M d'),
                'new'       => $newCustomers,
                'returning' => $weekOrders->count() - $newCustomers,
            ];

            $currentDate->addWeek();
        }

        return [
            'chart' => [
                'labels'    => array_column($weeklyData, 'label'),
                'new'       => array_column($weeklyData, 'new'),
                'returning' => array_column($weeklyData, 'returning'),
            ],
            'totals' => [
                'new'       => array_sum(array_column($weeklyData, 'new')),
                'returning' => array_sum(array_column($weeklyData, 'returning')),
            ]
        ];
    }

    private function getLaundryCustomerAnalytics($orders, $fromDate, $toDate)
    {
        // Batch-fetch the absolute first-seen date per unique_id from both systems
        $allIds = $orders->flatMap(fn($o) => $o->items->pluck('unique_id'))->filter()->unique()->toArray();

        $ordersFirst = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.type', 'laundry')->whereIn('order_items.unique_id', $allIds)
            ->groupBy('order_items.unique_id')
            ->select('order_items.unique_id', DB::raw('MIN(orders.date_received) as first_seen'))
            ->pluck('first_seen', 'unique_id')
            ->toArray();

        $legacyFirst = Laundry::withTrashed()->whereIn('unique_id', $allIds)
            ->groupBy('unique_id')
            ->select('unique_id', DB::raw('MIN(date_received) as first_seen'))
            ->pluck('first_seen', 'unique_id')
            ->toArray();

        $firstSeenMap = [];
        foreach ($allIds as $uid) {
            $dates = array_filter([$ordersFirst[$uid] ?? null, $legacyFirst[$uid] ?? null]);
            $firstSeenMap[$uid] = $dates ? min($dates) : null;
        }

        $weeklyData  = [];
        $currentDate = $fromDate->copy()->startOfWeek();

        while ($currentDate <= $toDate) {
            $weekEnd    = $currentDate->copy()->endOfWeek();
            $weekOrders = $orders->filter(function($order) use ($currentDate, $weekEnd) {
                $d = Carbon::parse($order->date_received);
                return $d >= $currentDate && $d <= $weekEnd;
            });

            $newCustomers = $weekOrders->filter(function($order) use ($currentDate, $firstSeenMap) {
                $ids = $order->items->pluck('unique_id')->filter()->toArray();
                if (empty($ids)) return false;
                foreach ($ids as $uid) {
                    if (isset($firstSeenMap[$uid]) && Carbon::parse($firstSeenMap[$uid]) < $currentDate) {
                        return false; // at least one uid seen before this week → returning
                    }
                }
                return true;
            })->count();

            $weeklyData[] = [
                'label'     => $currentDate->format('M d'),
                'new'       => $newCustomers,
                'returning' => $weekOrders->count() - $newCustomers,
            ];

            $currentDate->addWeek();
        }

        return [
            'chart' => [
                'labels'    => array_column($weeklyData, 'label'),
                'new'       => array_column($weeklyData, 'new'),
                'returning' => array_column($weeklyData, 'returning'),
            ],
            'totals' => [
                'new'       => array_sum(array_column($weeklyData, 'new')),
                'returning' => array_sum(array_column($weeklyData, 'returning')),
            ]
        ];
    }

    public function pendingCarpets()
    {
        $pendingDelivery = Order::with('items')
            ->where('type', 'carpet')
            ->whereHas('items', fn($q) => $q->where('delivered', 'Not Delivered'))
            ->orderBy('date_received', 'asc')
            ->get()
            ->map(function($order) {
                $order->aging_days = Carbon::parse($order->date_received)->diffInDays(now());
                return $order;
            });

        $unpaidOrders = Order::with('items')
            ->where('type', 'carpet')
            ->where('payment_status', 'Not Paid')
            ->orderBy('date_received', 'asc')
            ->get()
            ->map(function($order) {
                $order->aging_days = Carbon::parse($order->date_received)->diffInDays(now());
                return $order;
            });

        $pendingCount = $pendingDelivery->count();
        $unpaidCount  = $unpaidOrders->count();
        $unpaidValue  = $unpaidOrders->sum('total');
        $avgAgingDays = $pendingDelivery->count() > 0
            ? round($pendingDelivery->avg('aging_days'), 1)
            : 0;

        return view('reports.pending_carpets', compact(
            'pendingDelivery',
            'unpaidOrders',
            'pendingCount',
            'unpaidCount',
            'unpaidValue',
            'avgAgingDays'
        ));
    }

    private function carpetsToFakeOrders($carpets): \Illuminate\Support\Collection
    {
        return collect($carpets)
            ->groupBy(fn($c) => $c->phone . '|' . $c->date_received)
            ->map(function ($group) {
                $first    = $group->first();
                $statuses = $group->pluck('payment_status')->unique()->values();

                if ($statuses->count() === 1 && $statuses->first() === 'Paid') {
                    $payStatus = 'Paid';
                } elseif ($statuses->contains('Paid')) {
                    $payStatus = 'Partial';
                } else {
                    $payStatus = 'Not Paid';
                }

                $order                 = new \stdClass();
                $order->name           = $first->name;
                $order->phone          = $first->phone;
                $order->location       = $first->location ?? '';
                $order->date_received  = $first->date_received;
                $order->payment_status = $payStatus;
                $order->total          = $group->sum(fn($c) => (float)($c->price ?? 0) - (float)($c->discount ?? 0));
                $order->items          = $group->map(function ($c) {
                    $item             = new \stdClass();
                    $item->unique_id  = $c->uniqueid;
                    $item->size       = $c->size;
                    $item->price      = (float)($c->price    ?? 0);
                    $item->discount   = (float)($c->discount ?? 0);
                    $item->item_total = (float)($c->price    ?? 0) - (float)($c->discount ?? 0);
                    $item->delivered  = $c->delivered ?? 'Not Delivered';
                    return $item;
                })->values();

                return $order;
            })->values();
    }

    private function laundriesToFakeOrders($laundries): \Illuminate\Support\Collection
    {
        return collect($laundries)
            ->groupBy(fn($l) => $l->phone . '|' . $l->date_received)
            ->map(function ($group) {
                $first    = $group->first();
                $statuses = $group->pluck('payment_status')->unique()->values();

                if ($statuses->count() === 1 && $statuses->first() === 'Paid') {
                    $payStatus = 'Paid';
                } elseif ($statuses->contains('Paid')) {
                    $payStatus = 'Partial';
                } else {
                    $payStatus = 'Not Paid';
                }

                $order                 = new \stdClass();
                $order->name           = $first->name;
                $order->phone          = $first->phone;
                $order->location       = $first->location ?? '';
                $order->date_received  = $first->date_received;
                $order->payment_status = $payStatus;
                $order->total          = $group->sum(fn($l) => (float)($l->total ?? ((float)($l->price ?? 0) - (float)($l->discount ?? 0))));
                $order->items          = $group->map(function ($l) {
                    $item                   = new \stdClass();
                    $item->unique_id        = $l->unique_id;
                    $item->item_description = $l->item_description;
                    $item->quantity         = $l->quantity;
                    $item->weight           = $l->weight;
                    $item->price            = (float)($l->price    ?? 0);
                    $item->discount         = (float)($l->discount ?? 0);
                    $item->item_total       = (float)($l->total    ?? ((float)($l->price ?? 0) - (float)($l->discount ?? 0)));
                    $item->delivered        = $l->delivered ?? 'Not Delivered';
                    return $item;
                })->values();

                return $order;
            })->values();
    }

    private function getExpensePerformanceData($fromDate, $toDate)
    {
        // Get expense data for the period
        $expenses = Expense::with(['category', 'creator'])
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->approved()
            ->orderBy('expense_date', 'desc')
            ->get();

        // Calculate metrics
        $totalExpenses = $expenses->sum('amount');
        $totalTransactions = $expenses->count();
        $avgDailyExpenses = $totalTransactions / max(1, $fromDate->diffInDays($toDate) + 1);
        
        // Category breakdown
        $categoryBreakdown = $expenses->groupBy('category_id')->map(function($categoryExpenses) {
            return [
                'category' => $categoryExpenses->first()->category->name,
                'amount' => $categoryExpenses->sum('amount'),
                'count' => $categoryExpenses->count(),
                'color' => $categoryExpenses->first()->category->color_code
            ];
        })->sortByDesc('amount');

        // Daily expense data for chart
        $dailyExpenses = $expenses->groupBy(function($item) {
            return Carbon::parse($item->expense_date)->format('Y-m-d');
        })->map(function($dayExpenses) {
            return $dayExpenses->sum('amount');
        });

        // Fill missing dates with zero values
        $expenseLabels = [];
        $expenseData = [];
        $currentDate = $fromDate->copy();
        
        while ($currentDate <= $toDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $expenseLabels[] = $currentDate->format('M d');
            $expenseData[] = $dailyExpenses[$dateStr] ?? 0;
            $currentDate->addDay();
        }

        // Top vendors
        $topVendors = $expenses->groupBy('vendor_name')
            ->map(function($vendorExpenses) {
                return [
                    'vendor' => $vendorExpenses->first()->vendor_name,
                    'amount' => $vendorExpenses->sum('amount'),
                    'count' => $vendorExpenses->count()
                ];
            })
            ->sortByDesc('amount')
            ->take(5);

        // Monthly comparison
        $thisMonth = $expenses->filter(function($expense) {
            return $expense->expense_date->month == Carbon::now()->month;
        })->sum('amount');
        
        $lastMonth = Expense::whereBetween('expense_date', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth()
            ])
            ->approved()
            ->sum('amount');

        $monthlyGrowth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

        return response()->json([
            'metrics' => [
                'total_revenue' => $totalExpenses, // Using same structure for consistency
                'paid_revenue' => $totalExpenses, // All approved expenses are "paid"
                'total_orders' => $totalTransactions,
                'unpaid_orders' => 0, // No unpaid concept for expenses
                'unpaid_revenue' => 0,
                'payment_rate' => 100, // All approved expenses are 100% "paid"
                'avg_daily_orders' => round($avgDailyExpenses, 1),
                'period_start' => $fromDate->format('M d, Y')
            ],
            'charts' => [
                'revenue' => [
                    'labels' => $expenseLabels,
                    'total' => $expenseData,
                    'paid' => $expenseData, // Same as total for expenses
                    'unpaid' => array_fill(0, count($expenseData), 0) // No unpaid expenses
                ],
                'payment' => [
                    'paid' => $totalExpenses,
                    'unpaid' => 0 // No unpaid concept for approved expenses
                ],
                'volume' => [
                    'labels' => $expenseLabels,
                    'data' => collect($expenseLabels)->map(function($label, $index) use ($expenses, $fromDate) {
                        $date = $fromDate->copy()->addDays($index)->format('Y-m-d');
                        return $expenses->filter(function($expense) use ($date) {
                            return $expense->expense_date->format('Y-m-d') === $date;
                        })->count();
                    })->values()->toArray()
                ],
                'categories' => [
                    'labels' => $categoryBreakdown->pluck('category')->values()->toArray(),
                    'data' => $categoryBreakdown->pluck('amount')->values()->toArray(),
                    'colors' => $categoryBreakdown->pluck('color')->values()->toArray()
                ]
            ],
            'operational' => [
                'pending_deliveries' => Expense::pending()->count(),
                'completed_today' => $expenses->filter(function($expense) {
                    return $expense->expense_date->isToday();
                })->count(),
                'avg_processing_days' => 0, // Not applicable for expenses
                'new_customers_rate' => round($monthlyGrowth, 1), // Using monthly growth instead
                'top_vendors' => $topVendors->values()->toArray(),
                'category_breakdown' => $categoryBreakdown->values()->toArray()
            ]
        ]);
    }

}
