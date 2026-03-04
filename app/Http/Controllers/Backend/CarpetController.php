<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarpetRequest;
use App\Http\Requests\UpdateCarpetRequest;
use App\Models\Carpet;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CarpetController extends Controller
{
    public function AllCarpet(){
        // Just return the view, data will be loaded via AJAX
        return view('backend.carpet.all_carpet');
    } // End Method

    /**
     * Server-side DataTables data for All Carpets
     */
    public function getCarpetsData(Request $request)
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = min((int) $request->input('length', 25), 100); // Max 100 records per page
            $search = $request->input('search.value', '');
            $orderColumnIndex = (int) $request->input('order.0.column', 0);
            $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

            // Map column index to database column (whitelist approach for security)
            $columns = ['date_received', 'uniqueid', 'size', 'price', 'discount', 'phone', 'payment_status', 'delivered'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'date_received';

            // Base query
            $query = Carpet::query();

            // Total records (without filtering)
            $totalRecords = Carpet::count();

            // Apply search filter (sanitize search input)
            if (!empty($search)) {
                $search = trim($search);
                $query->where(function($q) use ($search) {
                    $q->where('uniqueid', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('size', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%")
                      ->orWhere('payment_status', 'like', "%{$search}%")
                      ->orWhere('delivered', 'like', "%{$search}%");
                });
            }

            // Filtered count
            $filteredRecords = $query->count();

            // Apply ordering and pagination
            $carpets = $query->orderBy($orderColumn, $orderDirection)
                             ->skip($start)
                             ->take($length)
                             ->get();

            // Format data for DataTables
            $data = [];
            foreach ($carpets as $carpet) {
                $actions = '';
                $isLocked = $carpet->payment_status === 'Paid' && $carpet->delivered === 'Delivered';

                if (Gate::allows('carpet.edit') && !$isLocked) {
                    $actions .= '<a href="' . route('edit.carpet', $carpet->id) . '" class="btn btn-secondary btn-sm rounded-pill waves-effect">Edit</a> ';
                }

                if (Gate::allows('carpet.delete') && (!$isLocked || Gate::allows('admin.all'))) {
                    $actions .= '<form action="' . route('delete.carpet', $carpet->id) . '" method="POST" style="display:inline" class="delete-form">'
                        . '<input type="hidden" name="_token" value="' . csrf_token() . '">'
                        . '<input type="hidden" name="_method" value="DELETE">'
                        . '<button type="submit" class="btn btn-danger btn-sm rounded-pill waves-effect waves-light" id="delete">Delete</button>'
                        . '</form> ';
                }

                if ($carpet->payment_status !== 'Paid') {
                    $amount = ($carpet->price ?? 0) - ($carpet->discount ?? 0);
                    $actions .= '<button type="button" class="btn btn-success btn-sm rounded-pill waves-effect waves-light mpesa-btn" '
                        . 'data-service-type="carpet" '
                        . 'data-service-id="' . $carpet->id . '" '
                        . 'data-phone="' . e($carpet->phone) . '" '
                        . 'data-amount="' . $amount . '" '
                        . 'data-name="' . e($carpet->uniqueid) . '" '
                        . 'title="Send M-Pesa Prompt">'
                        . '<i class="mdi mdi-cellphone"></i></button> ';
                }

                if (Gate::allows('carpet.details')) {
                    $actions .= '<a href="' . route('details.carpet', $carpet->id) . '" class="btn btn-info btn-sm rounded-pill waves-effect waves-light">Info</a>';
                }

                $data[] = [
                    'date_received' => e($carpet->date_received),
                    'uniqueid' => e($carpet->uniqueid),
                    'size' => e($carpet->size),
                    'price' => number_format($carpet->price ?? 0, 2),
                    'discount' => number_format($carpet->discount ?? 0, 2),
                    'phone' => e($carpet->phone),
                    'payment_status' => e($carpet->payment_status),
                    'delivered' => e($carpet->delivered),
                    'actions' => $actions
                ];
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while fetching data.'
            ], 500);
        }
    } // End Method

    public function CarpetDashboard()
    {
        $today     = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        // Summary card counts (from orders table)
        $todayCarpetCount  = \App\Models\Order::where('type', 'carpet')
                                ->whereDate('date_received', $today)->count();

        $todayLaundryCount = \App\Models\Order::where('type', 'laundry')
                                ->whereDate('date_received', $today)->count();

        // New clients today: orders whose unique IDs have never appeared before today
        $todayOrders = \App\Models\Order::with('items')->whereDate('date_received', $today)->get();

        $todayClientCount = $todayOrders->filter(function ($order) use ($today) {
            $uniqueIds = $order->items->pluck('unique_id')->filter()->toArray();
            if (empty($uniqueIds)) return false;
            return !DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('order_items.unique_id', $uniqueIds)
                ->where('orders.date_received', '<', $today)
                ->exists();
        })->count();

        // Today's revenue (from orders.total which is already net of discounts)
        $todayTotalRevenue = \App\Models\Order::whereDate('date_received', $today)
                                ->sum('total');

        // Recent orders for the dashboard table (today + yesterday)
        $recentOrders = \App\Models\Order::withCount('items')
                            ->where(function ($q) use ($today, $yesterday) {
                                $q->whereDate('date_received', $today)
                                  ->orWhereDate('date_received', $yesterday);
                            })
                            ->orderByRaw('(DATE(date_received) = CURDATE()) DESC, date_received DESC')
                            ->get();

        // Weekly chart data (last 7 days including today)
        $weekLabels    = [];
        $weeklyCarpets = [];
        $weeklyLaundry = [];
        $weeklyRevenue = [];

        for ($i = 6; $i >= 0; $i--) {
            $date    = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();

            $weekLabels[]    = $date->format('D d/m');
            $weeklyCarpets[] = \App\Models\Order::where('type', 'carpet')
                                    ->whereDate('date_received', $dateStr)->count();
            $weeklyLaundry[] = \App\Models\Order::where('type', 'laundry')
                                    ->whereDate('date_received', $dateStr)->count();
            $weeklyRevenue[] = \App\Models\Order::whereDate('date_received', $dateStr)
                                    ->sum('total');
        }

        return view('admin.index', compact(
            'todayCarpetCount', 'todayLaundryCount', 'todayClientCount',
            'todayTotalRevenue', 'recentOrders',
            'weekLabels', 'weeklyCarpets', 'weeklyLaundry', 'weeklyRevenue'
        ));
    } // End Method

    public function AddCarpet(){
        return view('backend.carpet.add_carpet');
    } // End Method

    public function StoreCarpet(StoreCarpetRequest $request){
        $validateData = $request->validated();

        $carpet = Carpet::create(array_merge($validateData, [
            'follow_up_due_at' => Carbon::parse($validateData['date_received'])
                                        ->addDays(config('followup.stages')[1]),
            'transaction_code' => $request->transaction_code,
        ]));

        $notification = array(
            'message' => 'Carpet Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.carpet')->with($notification);

    }

    public function EditCarpet($id){
        $carpet = Carpet::FindOrfail($id);

        if ($carpet->payment_status === 'Paid' && $carpet->delivered === 'Delivered') {
            $notification = array(
                'message' => 'This record is locked because it has been paid and delivered.',
                'alert-type' => 'warning'
            );
            return redirect()->route('all.carpet')->with($notification);
        }

        return view('backend.carpet.edit_carpet',compact('carpet'));
    }

    public function UpdateCarpet(UpdateCarpetRequest $request){
        $validated = $request->validated();

        $carpet_id = $validated['id'];
        $carpet = Carpet::findOrFail($carpet_id);

        if ($carpet->payment_status === 'Paid' && $carpet->delivered === 'Delivered') {
            $notification = array(
                'message' => 'This record is locked because it has been paid and delivered.',
                'alert-type' => 'warning'
            );
            return redirect()->route('all.carpet')->with($notification);
        }

        $carpet->update([
             'uniqueid' => $validated['uniqueid'],
             'name' => $validated['name'],
             'size' => $validated['size'],
             'price' => $validated['price'],
             'discount' => $validated['discount'] ?? 0,
             'phone' => $validated['phone'],
             'location' => $validated['location'],
             'date_received' => $validated['date_received'],
             'date_delivered' => $validated['date_delivered'],
             'payment_status' => $validated['payment_status'],
             'transaction_code' => $validated['transaction_code'],
             'delivered' => $validated['delivered'],
        ]);

        // Clean up overdue notifications when item is marked as delivered
        if ($request->delivered === 'Delivered') {
            DB::table('notifications')
                ->where('type', 'App\Notifications\OverdueDeliveryNotification')
                ->where('data->service_type', 'carpet')
                ->where('data->service_id', $carpet_id)
                ->delete();
        }

        $notification = array(
            'message' => 'Carpet Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.carpet')->with($notification);

    } // End Method

    public function DeleteCarpet($id){
        $carpet = Carpet::findOrFail($id);

        if ($carpet->payment_status === 'Paid' && $carpet->delivered === 'Delivered' && !Gate::allows('admin.all')) {
            $notification = array(
                'message' => 'This record is locked because it has been paid and delivered.',
                'alert-type' => 'warning'
            );
            return redirect()->back()->with($notification);
        }

        $carpet->delete();

        $notification = array(
            'message' => 'Carpet Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function DetailsCarpet($id){

        $carpet = Carpet::findOrFail($id);
        return view('backend.carpet.details_carpet',compact('carpet'));

    } // End Method

    public function TrashedItems()
    {
        $trashedCarpets = Carpet::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        $trashedLaundry = \App\Models\Laundry::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return view('backend.trash.index', compact('trashedCarpets', 'trashedLaundry'));
    }

    public function RestoreCarpet($id)
    {
        $carpet = Carpet::onlyTrashed()->findOrFail($id);
        $carpet->restore();

        return redirect()->back()->with([
            'message' => 'Carpet record restored successfully.',
            'alert-type' => 'success'
        ]);
    }

    public function ForceDeleteCarpet($id)
    {
        $carpet = Carpet::onlyTrashed()->findOrFail($id);
        $carpet->forceDelete();

        return redirect()->back()->with([
            'message' => 'Carpet record permanently deleted.',
            'alert-type' => 'success'
        ]);
    }

    /**
     * Get customer details by phone number for autofill
     */
    public function getCustomerByPhone(Request $request)
    {
        $phone = $request->phone;

        if (!$phone) {
            return response()->json(['found' => false]);
        }

        // Check Orders table first (new system)
        $order = \App\Models\Order::where('phone', $phone)
            ->orderBy('date_received', 'desc')
            ->first();

        if ($order) {
            return response()->json([
                'found'        => true,
                'name'         => $order->name,
                'location'     => $order->location,
                'phone'        => $order->phone,
                'uniqueid'     => '',
                'size'         => '',
                'service_type' => $order->type,
            ]);
        }

        // Fall back to legacy Carpets table
        $customer = Carpet::where('phone', $phone)
            ->orderBy('date_received', 'desc')
            ->first();

        $serviceType = 'carpet';

        // If not found in carpets, check laundry
        if (!$customer) {
            $customer = \App\Models\Laundry::where('phone', $phone)
                ->orderBy('date_received', 'desc')
                ->first();
            $serviceType = 'laundry';
        }

        if ($customer) {
            // Laundry uses 'unique_id', Carpet uses 'uniqueid'
            $uid = $serviceType === 'laundry' ? $customer->unique_id : $customer->uniqueid;

            // Get last carpet record for this phone to retrieve discount info
            $lastCarpet = Carpet::where('phone', $phone)
                ->orderBy('date_received', 'desc')
                ->first();

            // Get last laundry record for this phone to retrieve discount info
            $lastLaundry = \App\Models\Laundry::where('phone', $phone)
                ->orderBy('date_received', 'desc')
                ->first();

            return response()->json([
                'found'                => true,
                'name'                 => $customer->name,
                'location'             => $customer->location,
                'phone'                => $customer->phone,
                'uniqueid'             => $uid ?? '',
                'size'                 => $customer->size ?? '',
                'service_type'         => $serviceType,
                'last_carpet_price'    => $lastCarpet->price ?? null,
                'last_carpet_discount' => $lastCarpet->discount ?? 0,
                'last_laundry_price'   => $lastLaundry->price ?? null,
                'last_laundry_discount'=> $lastLaundry->discount ?? 0,
                'last_laundry_total'   => $lastLaundry->total ?? null,
            ]);
        }

        return response()->json(['found' => false]);
    } // End Method

    /**
     * Get customer details by unique ID for autofill
     */
    public function getCustomerByUniqueId(Request $request)
    {
        $uniqueId = $request->uniqueid;

        if (!$uniqueId) {
            return response()->json(['found' => false]);
        }

        // Check in Carpets first
        $customer = Carpet::where('uniqueid', $uniqueId)->first();
        $serviceType = 'carpet';

        // If not found in carpets, check laundry (column is 'unique_id')
        if (!$customer) {
            $customer = \App\Models\Laundry::where('unique_id', $uniqueId)->first();
            $serviceType = 'laundry';
        }

        if ($customer) {
            $uid = $serviceType === 'laundry' ? $customer->unique_id : $customer->uniqueid;
            $phone = $customer->phone;

            // Get last carpet record for this customer
            $lastCarpet = Carpet::where('uniqueid', $uniqueId)
                ->orderBy('date_received', 'desc')
                ->first();
            if (!$lastCarpet && $phone) {
                $lastCarpet = Carpet::where('phone', $phone)
                    ->orderBy('date_received', 'desc')
                    ->first();
            }

            // Get last laundry record for this customer
            $lastLaundry = \App\Models\Laundry::where('unique_id', $uniqueId)
                ->orderBy('date_received', 'desc')
                ->first();
            if (!$lastLaundry && $phone) {
                $lastLaundry = \App\Models\Laundry::where('phone', $phone)
                    ->orderBy('date_received', 'desc')
                    ->first();
            }

            return response()->json([
                'found' => true,
                'name' => $customer->name,
                'location' => $customer->location,
                'phone' => $customer->phone,
                'uniqueid' => $uid ?? '',
                'size' => $customer->size ?? '',
                'service_type' => $serviceType,
                'last_carpet_price' => $lastCarpet->price ?? null,
                'last_carpet_discount' => $lastCarpet->discount ?? 0,
                'last_laundry_price' => $lastLaundry->price ?? null,
                'last_laundry_discount' => $lastLaundry->discount ?? 0,
                'last_laundry_total' => $lastLaundry->total ?? null,
            ]);
        }

        return response()->json(['found' => false]);
    } // End Method

    public function downloadAllCarpets()
{
    $orders = Order::with('items')->where('type', 'carpet')->get();
    $filename = 'carpets_all.csv';
    $includePhone = Gate::allows('admin.all');

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=' . $filename,
    ];

    $callback = function() use ($orders, $includePhone) {
        $file = fopen('php://output', 'w');
        if ($includePhone) {
            fputcsv($file, ['Unique ID', 'Size', 'Name', 'Phone', 'Price', 'Discount', 'Item Total', 'Payment Status', 'Date Received']);
        } else {
            fputcsv($file, ['Unique ID', 'Size', 'Price', 'Discount', 'Item Total', 'Payment Status', 'Date Received']);
        }
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if ($includePhone) {
                    fputcsv($file, [$item->unique_id, $item->size, $order->name, $order->phone, $item->price, $item->discount, $item->item_total, $order->payment_status, $order->date_received]);
                } else {
                    fputcsv($file, [$item->unique_id, $item->size, $item->price, $item->discount, $item->item_total, $order->payment_status, $order->date_received]);
                }
            }
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function viewCarpetsByMonth(Request $request)
{
    $month = (int) $request->input('month', Carbon::now()->format('m'));
    $year  = (int) $request->input('year', Carbon::now()->format('Y'));

    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
    $endDate   = $startDate->copy()->endOfMonth();

    $orders = Order::with('items')
        ->where('type', 'carpet')
        ->whereBetween('date_received', [$startDate, $endDate])
        ->get();

    $totalPaid   = $orders->where('payment_status', 'Paid')->sum('total');
    $totalUnpaid = $orders->where('payment_status', 'Not Paid')->sum('total');
    $grandTotal  = $totalPaid + $totalUnpaid;

    // New clients: unique IDs in this order have never appeared before this month
    $newOrders = $orders->filter(function ($order) use ($startDate) {
        $uniqueIds = $order->items->pluck('unique_id')->filter()->toArray();
        if (empty($uniqueIds)) return false;
        return !DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.unique_id', $uniqueIds)
            ->where('orders.date_received', '<', $startDate)
            ->exists();
    });

    return view('reports.carpets_month', [
        'month'      => $month,
        'year'       => $year,
        'orders'     => $orders,
        'newOrders'  => $newOrders,
        'totalPaid'  => $totalPaid,
        'totalUnpaid'=> $totalUnpaid,
        'grandTotal' => $grandTotal,
    ]);
}

/**
 * Download all carpet orders for a given month/year as CSV (one row per item)
 */
public function downloadCarpetsByMonth(Request $request)
{
    $month = (int) $request->input('month', Carbon::now()->format('m'));
    $year  = (int) $request->input('year', Carbon::now()->format('Y'));

    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
    $endDate   = $startDate->copy()->endOfMonth();

    $orders = Order::with('items')
        ->where('type', 'carpet')
        ->whereBetween('date_received', [$startDate, $endDate])
        ->get();

    $totalPaid   = $orders->where('payment_status', 'Paid')->sum('total');
    $totalUnpaid = $orders->where('payment_status', 'Not Paid')->sum('total');
    $grandTotal  = $totalPaid + $totalUnpaid;

    $includePhone = Gate::allows('admin.all');

    $filename = "carpets_{$year}_{$month}.csv";
    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename={$filename}",
    ];

    $callback = function() use ($orders, $totalPaid, $totalUnpaid, $grandTotal, $includePhone) {
        $file = fopen('php://output', 'w');

        if ($includePhone) {
            fputcsv($file, ['Unique ID', 'Size', 'Name', 'Phone', 'Price', 'Discount', 'Item Total', 'Payment Status', 'Date Received']);
        } else {
            fputcsv($file, ['Unique ID', 'Size', 'Price', 'Discount', 'Item Total', 'Payment Status', 'Date Received']);
        }

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if ($includePhone) {
                    fputcsv($file, [$item->unique_id, $item->size, $order->name, $order->phone, $item->price, $item->discount, $item->item_total, $order->payment_status, $order->date_received]);
                } else {
                    fputcsv($file, [$item->unique_id, $item->size, $item->price, $item->discount, $item->item_total, $order->payment_status, $order->date_received]);
                }
            }
        }

        fputcsv($file, []);
        fputcsv($file, ['Total Paid Amount', $totalPaid]);
        fputcsv($file, ['Total Unpaid Amount', $totalUnpaid]);
        fputcsv($file, ['Grand Total', $grandTotal]);

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

/**
 * Download new carpet clients only for a given month/year as CSV
 */
public function downloadNewCarpetsByMonth(Request $request)
{
    $month = (int) $request->input('month', Carbon::now()->format('m'));
    $year  = (int) $request->input('year', Carbon::now()->format('Y'));

    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
    $endDate   = $startDate->copy()->endOfMonth();

    $orders = Order::with('items')
        ->where('type', 'carpet')
        ->whereBetween('date_received', [$startDate, $endDate])
        ->get();

    $newOrders = $orders->filter(function ($order) use ($startDate) {
        $uniqueIds = $order->items->pluck('unique_id')->filter()->toArray();
        if (empty($uniqueIds)) return false;
        return !DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.unique_id', $uniqueIds)
            ->where('orders.date_received', '<', $startDate)
            ->exists();
    });

    $includePhone = Gate::allows('admin.all');

    $filename = "new_carpet_clients_{$year}_{$month}.csv";
    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename={$filename}",
    ];

    $callback = function() use ($newOrders, $includePhone) {
        $file = fopen('php://output', 'w');

        if ($includePhone) {
            fputcsv($file, ['Name', 'Phone', 'Unique IDs', 'Total (KES)', 'Payment Status', 'Date Received']);
        } else {
            fputcsv($file, ['Name', 'Unique IDs', 'Total (KES)', 'Payment Status', 'Date Received']);
        }

        foreach ($newOrders as $order) {
            $uniqueIds = $order->items->pluck('unique_id')->implode(', ');
            if ($includePhone) {
                fputcsv($file, [$order->name, $order->phone, $uniqueIds, $order->total, $order->payment_status, $order->date_received]);
            } else {
                fputcsv($file, [$order->name, $uniqueIds, $order->total, $order->payment_status, $order->date_received]);
            }
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

}
