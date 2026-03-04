<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaundryRequest;
use App\Http\Requests\UpdateLaundryRequest;
use Illuminate\Http\Request;
use App\Models\Laundry;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LaundryController extends Controller
{
    public function AllLaundry(){
        // Just return the view, data will be loaded via AJAX
        return view('backend.laundry.all_laundry');
    }

    /**
     * Server-side DataTables data for All Laundry
     */
    public function getLaundriesData(Request $request)
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = min((int) $request->input('length', 25), 100); // Max 100 records per page
            $search = $request->input('search.value', '');
            $orderColumnIndex = (int) $request->input('order.0.column', 0);
            $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

            // Map column index to database column (whitelist approach for security)
            $columns = ['id', 'name', 'phone', 'date_received', 'date_delivered', 'total', 'payment_status', 'delivered'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';

            // Base query
            $query = Laundry::query();

            // Total records (without filtering)
            $totalRecords = Laundry::count();

            // Apply search filter (sanitize search input)
            if (!empty($search)) {
                $search = trim($search);
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('unique_id', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%")
                      ->orWhere('payment_status', 'like', "%{$search}%")
                      ->orWhere('delivered', 'like', "%{$search}%");
                });
            }

            // Filtered count
            $filteredRecords = $query->count();

            // Apply ordering and pagination
            $laundries = $query->orderBy($orderColumn, $orderDirection)
                              ->skip($start)
                              ->take($length)
                              ->get();

            // Format data for DataTables
            $data = [];
            $rowNumber = $start + 1;
            foreach ($laundries as $laundry) {
                $actions = '';
                $isLocked = $laundry->payment_status === 'Paid' && $laundry->delivered === 'Delivered';

                if (Gate::allows('laundry.edit') && !$isLocked) {
                    $actions .= '<a href="' . route('edit.laundry', $laundry->id) . '" class="btn btn-secondary btn-sm rounded-pill waves-effect" title="Edit"><i class="fa fa-pencil"></i></a> ';
                }

                if (Gate::allows('laundry.delete') && (!$isLocked || Gate::allows('admin.all'))) {
                    $actions .= '<form action="' . route('delete.laundry', $laundry->id) . '" method="POST" style="display:inline" class="delete-form">'
                        . '<input type="hidden" name="_token" value="' . csrf_token() . '">'
                        . '<input type="hidden" name="_method" value="DELETE">'
                        . '<button type="submit" class="btn btn-danger btn-sm rounded-pill waves-effect waves-light" id="delete" title="Delete"><i class="fa fa-trash"></i></button>'
                        . '</form> ';
                }

                if ($laundry->payment_status !== 'Paid') {
                    $amount = ($laundry->total ?? 0) - ($laundry->discount ?? 0);
                    $actions .= '<button type="button" class="btn btn-success btn-sm rounded-pill waves-effect waves-light mpesa-btn" '
                        . 'data-service-type="laundry" '
                        . 'data-service-id="' . $laundry->id . '" '
                        . 'data-phone="' . e($laundry->phone) . '" '
                        . 'data-amount="' . $amount . '" '
                        . 'data-name="' . e($laundry->name) . '" '
                        . 'title="Send M-Pesa Prompt">'
                        . '<i class="mdi mdi-cellphone"></i></button> ';
                }

                if (Gate::allows('laundry.details')) {
                    $actions .= '<a href="' . route('details.laundry', $laundry->id) . '" class="btn btn-info btn-sm rounded-pill waves-effect waves-light" title="Details"><i class="fa fa-eye"></i></a>';
                }

                $data[] = [
                    'row_number' => $rowNumber++,
                    'name' => e($laundry->name),
                    'phone' => e($laundry->phone),
                    'date_received' => e($laundry->date_received),
                    'date_delivered' => e($laundry->date_delivered),
                    'total' => number_format($laundry->total ?? 0, 2),
                    'payment_status' => e($laundry->payment_status),
                    'delivered' => e($laundry->delivered),
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
    }

    public function AddLaundry(){
        return view('backend.laundry.add_laundry');
    }

    public function StoreLaundry(StoreLaundryRequest $request){
        $validateData = $request->validated();

       $laundry = Laundry::create(array_merge($validateData, [
           'follow_up_due_at' => Carbon::parse($validateData['date_received'])
                                       ->addDays(config('followup.stages')[1]),
       ]));

       $notification = array(
           'message' => 'Laundry Added Successfully',
           'alert-type' => 'success'
       );

       return redirect()->route('all.laundry')->with($notification);
    }

    public function EditLaundry($id){
        $laundry = Laundry::FindOrfail($id);

        if ($laundry->payment_status === 'Paid' && $laundry->delivered === 'Delivered') {
            $notification = array(
                'message' => 'This record is locked because it has been paid and delivered.',
                'alert-type' => 'warning'
            );
            return redirect()->route('all.laundry')->with($notification);
        }

        return view('backend.laundry.edit_laundry',compact('laundry'));
    }

    public function UpdateLaundry(UpdateLaundryRequest $request){
        $validated = $request->validated();

        $laundry_id = $validated['id'];
        $laundry = Laundry::findOrFail($laundry_id);

        if ($laundry->payment_status === 'Paid' && $laundry->delivered === 'Delivered') {
            $notification = array(
                'message' => 'This record is locked because it has been paid and delivered.',
                'alert-type' => 'warning'
            );
            return redirect()->route('all.laundry')->with($notification);
        }

        $laundry->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'location' => $validated['location'],
            'unique_id' => $validated['unique_id'],
            'date_received' => $validated['date_received'],
            'date_delivered' => $validated['date_delivered'],
            'quantity' => $validated['quantity'],
            'item_description' => $validated['item_description'],
            'weight' => $validated['weight'],
            'price' => $validated['price'],
            'discount' => $validated['discount'] ?? 0,
            'total' => $validated['total'],
            'delivered' => $validated['delivered'],
            'payment_status' => $validated['payment_status'],
        ]);

        // Clean up overdue notifications when item is marked as delivered
        if ($request->delivered === 'Delivered') {
            DB::table('notifications')
                ->where('type', 'App\Notifications\OverdueDeliveryNotification')
                ->where('data->service_type', 'laundry')
                ->where('data->service_id', $laundry_id)
                ->delete();
        }

        $notification = array(
            'message' => 'Laundry Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.laundry')->with($notification);
    }

    public function DeleteLaundry($id){
        $laundry = Laundry::findOrFail($id);

        if ($laundry->payment_status === 'Paid' && $laundry->delivered === 'Delivered' && !Gate::allows('admin.all')) {
            $notification = array(
                'message' => 'This record is locked because it has been paid and delivered.',
                'alert-type' => 'warning'
            );
            return redirect()->back()->with($notification);
        }

        $laundry->delete();

        $notification = array(
            'message' => 'Item Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function DetailsLaundry($id){

        $laundry = Laundry::findOrFail($id);
        return view('backend.laundry.details_laundry',compact('laundry'));

    } // End Method

    public function RestoreLaundry($id)
    {
        $laundry = Laundry::onlyTrashed()->findOrFail($id);
        $laundry->restore();

        return redirect()->back()->with([
            'message' => 'Laundry record restored successfully.',
            'alert-type' => 'success'
        ]);
    }

    public function ForceDeleteLaundry($id)
    {
        $laundry = Laundry::onlyTrashed()->findOrFail($id);
        $laundry->forceDelete();

        return redirect()->back()->with([
            'message' => 'Laundry record permanently deleted.',
            'alert-type' => 'success'
        ]);
    }

    public function downloadAllLaundry()
{
    $orders = Order::with('items')->where('type', 'laundry')->get();
    $filename = 'laundry_all.csv';
    $includePhone = Gate::allows('admin.all');

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=' . $filename,
    ];

    $callback = function() use ($orders, $includePhone) {
        $file = fopen('php://output', 'w');
        if ($includePhone) {
            fputcsv($file, ['Name', 'Phone', 'Item', 'Qty', 'Unit Price', 'Item Total', 'Payment Status', 'Date Received']);
        } else {
            fputcsv($file, ['Name', 'Item', 'Qty', 'Unit Price', 'Item Total', 'Payment Status', 'Date Received']);
        }
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if ($includePhone) {
                    fputcsv($file, [$order->name, $order->phone, $item->item_description, $item->quantity, $item->price, $item->item_total, $order->payment_status, $order->date_received]);
                } else {
                    fputcsv($file, [$order->name, $item->item_description, $item->quantity, $item->price, $item->item_total, $order->payment_status, $order->date_received]);
                }
            }
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function viewLaundryByMonth(Request $request)
{
    $month = (int) $request->input('month', Carbon::now()->format('m'));
    $year  = (int) $request->input('year', Carbon::now()->format('Y'));

    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
    $endDate   = $startDate->copy()->endOfMonth();

    $startStr = $startDate->toDateString();
    $endStr   = $endDate->toDateString();

    $orders = Order::with('items')
        ->where('type', 'laundry')
        ->whereBetween('date_received', [$startStr, $endStr])
        ->get();

    $oldLaundries = Laundry::withTrashed()->whereBetween('date_received', [$startStr, $endStr])->get();
    if ($oldLaundries->isNotEmpty()) {
        $orders = $orders->concat($this->laundriesToFakeOrders($oldLaundries));
    }

    $priorPhones = Order::where('type', 'laundry')->where('date_received', '<', $startStr)->pluck('phone')
        ->merge(Laundry::withTrashed()->where('date_received', '<', $startStr)->pluck('phone'))
        ->unique()->toArray();

    $newOrders = $orders->filter(fn($o) => !in_array($o->phone, $priorPhones));

    $totalPaid   = (float) $orders->where('payment_status', 'Paid')->sum('total');
    $totalUnpaid = (float) $orders->where('payment_status', 'Not Paid')->sum('total');
    $grandTotal  = $totalPaid + $totalUnpaid;

    return view('reports.laundry_month', [
        'month'      => $month,
        'year'       => $year,
        'orders'     => $orders,
        'newOrders'  => $newOrders,
        'totalPaid'  => $totalPaid,
        'totalUnpaid'=> $totalUnpaid,
        'grandTotal' => $grandTotal,
    ]);
}

public function downloadLaundryByMonth(Request $request)
{
    $month = (int) $request->input('month', Carbon::now()->format('m'));
    $year  = (int) $request->input('year', Carbon::now()->format('Y'));

    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
    $endDate   = $startDate->copy()->endOfMonth();

    $startStr = $startDate->toDateString();
    $endStr   = $endDate->toDateString();

    $orders = Order::with('items')
        ->where('type', 'laundry')
        ->whereBetween('date_received', [$startStr, $endStr])
        ->get();

    $oldLaundries = Laundry::withTrashed()->whereBetween('date_received', [$startStr, $endStr])->get();
    if ($oldLaundries->isNotEmpty()) {
        $orders = $orders->concat($this->laundriesToFakeOrders($oldLaundries));
    }

    $totalPaid   = (float) $orders->where('payment_status', 'Paid')->sum('total');
    $totalUnpaid = (float) $orders->where('payment_status', 'Not Paid')->sum('total');
    $grandTotal  = $totalPaid + $totalUnpaid;

    $includePhone = Gate::allows('admin.all');

    $filename = "laundry_{$year}_{$month}.csv";
    $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];

    $callback = function() use ($orders, $totalPaid, $totalUnpaid, $grandTotal, $includePhone) {
        $file = fopen('php://output', 'w');

        if ($includePhone) {
            fputcsv($file, ['Name', 'Phone', 'Item', 'Qty', 'Unit Price', 'Item Total', 'Payment Status', 'Date Received']);
        } else {
            fputcsv($file, ['Name', 'Item', 'Qty', 'Unit Price', 'Item Total', 'Payment Status', 'Date Received']);
        }

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if ($includePhone) {
                    fputcsv($file, [$order->name, $order->phone, $item->item_description, $item->quantity, $item->price, $item->item_total, $order->payment_status, $order->date_received]);
                } else {
                    fputcsv($file, [$order->name, $item->item_description, $item->quantity, $item->price, $item->item_total, $order->payment_status, $order->date_received]);
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

public function downloadNewLaundryByMonth(Request $request)
{
    $month = (int) $request->input('month', Carbon::now()->format('m'));
    $year  = (int) $request->input('year', Carbon::now()->format('Y'));

    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
    $endDate   = $startDate->copy()->endOfMonth();

    $startStr = $startDate->toDateString();
    $endStr   = $endDate->toDateString();

    $orders = Order::with('items')
        ->where('type', 'laundry')
        ->whereBetween('date_received', [$startStr, $endStr])
        ->get();

    $oldLaundries = Laundry::withTrashed()->whereBetween('date_received', [$startStr, $endStr])->get();
    if ($oldLaundries->isNotEmpty()) {
        $orders = $orders->concat($this->laundriesToFakeOrders($oldLaundries));
    }

    $priorPhones = Order::where('type', 'laundry')->where('date_received', '<', $startStr)->pluck('phone')
        ->merge(Laundry::withTrashed()->where('date_received', '<', $startStr)->pluck('phone'))
        ->unique()->toArray();

    $newOrders = $orders->filter(fn($o) => !in_array($o->phone, $priorPhones));

    $includePhone = Gate::allows('admin.all');

    $filename = "new_laundry_clients_{$year}_{$month}.csv";
    $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];

    $callback = function() use ($newOrders, $includePhone) {
        $file = fopen('php://output', 'w');

        if ($includePhone) {
            fputcsv($file, ['Name', 'Phone', 'Items', 'Order Total', 'Payment Status', 'Date Received']);
        } else {
            fputcsv($file, ['Name', 'Items', 'Order Total', 'Payment Status', 'Date Received']);
        }

        foreach ($newOrders as $order) {
            $items = $order->items->map(fn($i) => ($i->item_description ?? '') . ' x' . ($i->quantity ?? 1))->implode('; ');
            if ($includePhone) {
                fputcsv($file, [$order->name, $order->phone, $items, $order->total, $order->payment_status, $order->date_received]);
            } else {
                fputcsv($file, [$order->name, $items, $order->total, $order->payment_status, $order->date_received]);
            }
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
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

}
