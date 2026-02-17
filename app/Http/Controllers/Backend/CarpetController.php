<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarpetRequest;
use App\Http\Requests\UpdateCarpetRequest;
use App\Models\Carpet;
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
        // Define today and yesterday in 'YYYY-MM-DD' format.
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        // Get recent carpets for the table (received today or yesterday)
        $carpet = Carpet::whereDate('date_received', $today)
                    ->orWhereDate('date_received', $yesterday)
                    // Order them so that today's records come first.
                    ->orderByRaw('(DATE(date_received) = CURDATE()) DESC, date_received DESC')
                    ->get();

        // Count carpets actually washed/processed today (using date_received as processing date)
        $todayCarpetCount = Carpet::whereDate('date_received', $today)->count();

        // Count new clients today using unique phone numbers and unique IDs
        // A client is "new" if this is their first carpet service ever
        $todayNewClientCount = Carpet::whereDate('date_received', $today)
            ->whereNotExists(function ($query) use ($today) {
                $query->select(DB::raw(1))
                    ->from('carpets as c2')
                    ->whereColumn('c2.phone', 'carpets.phone')
                    ->where('c2.date_received', '<', $today);
            })
            ->distinct('phone')
            ->count('phone');

        // Also check laundry for truly new clients across all services
        $todayUniqueNewClients = collect();

        // Get carpet clients from today
        $todayCarpetClients = Carpet::whereDate('date_received', $today)
            ->select('phone', 'name', 'date_received')
            ->get();

        // Check if they exist in carpet before today OR in laundry before today
        foreach ($todayCarpetClients as $client) {
            $existsInCarpet = Carpet::where('phone', $client->phone)
                ->where('date_received', '<', $today)
                ->exists();

            $existsInLaundry = \App\Models\Laundry::where('phone', $client->phone)
                ->where('date_received', '<', $today)
                ->exists();

            if (!$existsInCarpet && !$existsInLaundry) {
                $todayUniqueNewClients->push($client->phone);
            }
        }

        // Get laundry clients from today and check if they're truly new
        $todayLaundryClients = \App\Models\Laundry::whereDate('date_received', $today)
            ->select('phone', 'name', 'date_received')
            ->get();

        foreach ($todayLaundryClients as $client) {
            if ($todayUniqueNewClients->contains($client->phone)) {
                continue; // Already counted from carpet
            }

            $existsInCarpet = Carpet::where('phone', $client->phone)
                ->where('date_received', '<', $today)
                ->exists();

            $existsInLaundry = \App\Models\Laundry::where('phone', $client->phone)
                ->where('date_received', '<', $today)
                ->exists();

            if (!$existsInCarpet && !$existsInLaundry) {
                $todayUniqueNewClients->push($client->phone);
            }
        }

        $todayClientCount = $todayUniqueNewClients->unique()->count();

        // Laundry count today
        $todayLaundryCount = \App\Models\Laundry::whereDate('date_received', $today)->count();

        // Revenue today
        $todayCarpetRevenue = Carpet::whereDate('date_received', $today)->sum('price');
        $todayLaundryRevenue = \App\Models\Laundry::whereDate('date_received', $today)->sum('total');
        $todayTotalRevenue = $todayCarpetRevenue + $todayLaundryRevenue;

        // Recent laundry (today or yesterday)
        $recentLaundry = \App\Models\Laundry::whereDate('date_received', $today)
                    ->orWhereDate('date_received', $yesterday)
                    ->orderByRaw('(DATE(date_received) = CURDATE()) DESC, date_received DESC')
                    ->get();

        // Weekly chart data (last 7 days including today)
        $weekLabels = [];
        $weeklyCarpets = [];
        $weeklyLaundry = [];
        $weeklyRevenue = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            $weekLabels[] = $date->format('D d/m');

            $weeklyCarpets[] = Carpet::whereDate('date_received', $dateStr)->count();
            $weeklyLaundry[] = \App\Models\Laundry::whereDate('date_received', $dateStr)->count();

            $carpetRev = Carpet::whereDate('date_received', $dateStr)->sum('price');
            $laundryRev = \App\Models\Laundry::whereDate('date_received', $dateStr)->sum('total');
            $weeklyRevenue[] = $carpetRev + $laundryRev;
        }

        return view('admin.index', compact(
            'carpet', 'todayCarpetCount', 'todayClientCount',
            'todayLaundryCount', 'todayTotalRevenue', 'recentLaundry',
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

        // Check in Carpets first (most recent)
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
    // Fetch all Carpet records.
    $carpets = \App\Models\Carpet::all();
    $filename = 'carpets_all.csv';
    $includePhone = Gate::allows('admin.all');

    // Define headers including Content-Disposition.
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=' . $filename,
    ];

    $columns = $includePhone
        ? ['Unique ID', 'Size', 'Price', 'Phone', 'Payment Status', 'Date Received']
        : ['Unique ID', 'Size', 'Price', 'Payment Status', 'Date Received'];

    $callback = function() use ($carpets, $columns, $includePhone) {
        $file = fopen('php://output', 'w');
        // Output header row.
        fputcsv($file, $columns);
        // Output each carpet record.
        foreach ($carpets as $carpet) {
            $row = $includePhone
                ? [
                    $carpet->uniqueid,
                    $carpet->size,
                    $carpet->price,
                    $carpet->phone,
                    $carpet->payment_status,
                    $carpet->date_received,
                ]
                : [
                    $carpet->uniqueid,
                    $carpet->size,
                    $carpet->price,
                    $carpet->payment_status,
                    $carpet->date_received,
                ];
            fputcsv($file, $row);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function viewCarpetsByMonth(Request $request)
{
    // Default to current month/year if none provided
    $month = (int) $request->input('month', Carbon::now()->format('m'));
    $year  = (int) $request->input('year', Carbon::now()->format('Y'));

    // Determine the start and end of that month
    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
    $endDate   = $startDate->copy()->endOfMonth();

    // Fetch all carpets in that month
    $carpets = Carpet::whereBetween('date_received', [$startDate, $endDate])->get();

    // Calculate totals (Paid, Unpaid, Grand)
    $paidCarpets   = $carpets->where('payment_status', 'Paid');
    $unpaidCarpets = $carpets->where('payment_status', 'Not Paid');
    $totalPaid     = $paidCarpets->sum('price');
    $totalUnpaid   = $unpaidCarpets->sum('price');
    $grandTotal    = $totalPaid + $totalUnpaid;

    // Identify new clients by uniqueid
    // A client is new if there's no existing record with that uniqueid
    // and date_received < $startDate
    $newCarpets = $carpets->filter(function ($carpet) use ($startDate) {
        // If a record exists for this uniqueid with date_received < startDate, not new
        return !Carpet::where('uniqueid', $carpet->uniqueid)
            ->where('date_received', '<', $startDate)
            ->exists();
    });

    return view('reports.carpets_month', [
        'month'        => $month,
        'year'         => $year,
        'carpets'      => $carpets,
        'newCarpets'   => $newCarpets,
        'totalPaid'    => $totalPaid,
        'totalUnpaid'  => $totalUnpaid,
        'grandTotal'   => $grandTotal,
    ]);
}

/**
 * Download all carpets for a given month/year as CSV
 */
public function downloadCarpetsByMonth(Request $request)
{
    $month = (int) $request->input('month', Carbon::now()->format('m'));
    $year  = (int) $request->input('year', Carbon::now()->format('Y'));

    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
    $endDate   = $startDate->copy()->endOfMonth();

    $carpets = Carpet::whereBetween('date_received', [$startDate, $endDate])->get();

    // Totals
    $paidCarpets   = $carpets->where('payment_status', 'Paid');
    $unpaidCarpets = $carpets->where('payment_status', 'Not Paid');
    $totalPaid     = $paidCarpets->sum('price');
    $totalUnpaid   = $unpaidCarpets->sum('price');
    $grandTotal    = $totalPaid + $totalUnpaid;

    // Check if user has admin.all permission
    $includePhone = Gate::allows('admin.all');

    $filename = "carpets_{$year}_{$month}.csv";
    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename={$filename}",
    ];

    $callback = function() use ($carpets, $totalPaid, $totalUnpaid, $grandTotal, $includePhone) {
        $file = fopen('php://output', 'w');

        // Header row - conditionally include Phone
        if ($includePhone) {
            fputcsv($file, ['Unique ID', 'Size', 'Price', 'Payment Status', 'Phone', 'Date Received']);
        } else {
            fputcsv($file, ['Unique ID', 'Size', 'Price', 'Payment Status', 'Date Received']);
        }

        // Data rows - conditionally include phone
        foreach ($carpets as $carpet) {
            if ($includePhone) {
                fputcsv($file, [
                    $carpet->uniqueid,
                    $carpet->size,
                    $carpet->price,
                    $carpet->payment_status,
                    $carpet->phone,
                    $carpet->date_received,
                ]);
            } else {
                fputcsv($file, [
                    $carpet->uniqueid,
                    $carpet->size,
                    $carpet->price,
                    $carpet->payment_status,
                    $carpet->date_received,
                ]);
            }
        }

        // Blank line
        fputcsv($file, []);

        // Totals
        fputcsv($file, ['Total Paid Amount', $totalPaid]);
        fputcsv($file, ['Total Unpaid Amount', $totalUnpaid]);
        fputcsv($file, ['Grand Total', $grandTotal]);

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

/**
 * Download new clients only for a given month/year as CSV
 */
public function downloadNewCarpetsByMonth(Request $request)
{
    $month = (int) $request->input('month', Carbon::now()->format('m'));
    $year  = (int) $request->input('year', Carbon::now()->format('Y'));

    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
    $endDate   = $startDate->copy()->endOfMonth();

    $carpets = Carpet::whereBetween('date_received', [$startDate, $endDate])->get();

    // Identify new carpets
    $newCarpets = $carpets->filter(function ($carpet) use ($startDate) {
        return !Carpet::where('uniqueid', $carpet->uniqueid)
            ->where('date_received', '<', $startDate)
            ->exists();
    });

    // Check if user has admin.all permission
    $includePhone = Gate::allows('admin.all');

    $filename = "new_clients_{$year}_{$month}.csv";
    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename={$filename}",
    ];

    $callback = function() use ($newCarpets, $includePhone) {
        $file = fopen('php://output', 'w');

        // Header row - conditionally include Phone
        if ($includePhone) {
            fputcsv($file, ['Unique ID', 'Phone', 'Name', 'Size', 'Price', 'Payment Status', 'Date Received']);
        } else {
            fputcsv($file, ['Unique ID', 'Name', 'Size', 'Price', 'Payment Status', 'Date Received']);
        }

        // Data rows - conditionally include phone
        foreach ($newCarpets as $carpet) {
            if ($includePhone) {
                fputcsv($file, [
                    $carpet->uniqueid,
                    $carpet->phone,
                    $carpet->name,
                    $carpet->size,
                    $carpet->price,
                    $carpet->payment_status,
                    $carpet->date_received,
                ]);
            } else {
                fputcsv($file, [
                    $carpet->uniqueid,
                    $carpet->name,
                    $carpet->size,
                    $carpet->price,
                    $carpet->payment_status,
                    $carpet->date_received,
                ]);
            }
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

}
