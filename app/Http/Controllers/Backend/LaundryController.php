<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laundry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LaundryController extends Controller
{
    public function AllLaundry(){
        $laundry = Laundry::latest()->get();
        return view('backend.laundry.all_laundry',compact('laundry'));

    }

    public function AddLaundry(){
        return view('backend.laundry.add_laundry');
    }

    public function StoreLaundry(Request $request){
        $validateData = $request->validate([
            'name' => 'required|string|max:200',
            'phone' => 'required|string|max:15',
            'location' => 'required|string|max:200',
            'unique_id' => 'required|string|max:200',
            'date_received' => 'required|date',
            'date_delivered' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'item_description' => 'required|string|max:200',
            'weight' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'delivered' => 'required|in:Delivered,Not Delivered',
            'payment_status' => 'required|in:Paid,Not Paid',
       ]);

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
        return view('backend.laundry.edit_laundry',compact('laundry'));
    }

    public function UpdateLaundry(Request $request){
        $validated = $request->validate([
            'id' => 'required|exists:laundries,id',
            'name' => 'required|string|max:200',
            'phone' => 'required|string|max:15',
            'location' => 'required|string|max:200',
            'unique_id' => 'required|string|max:200',
            'date_received' => 'required|date',
            'date_delivered' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'item_description' => 'required|string|max:200',
            'weight' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'delivered' => 'required|in:Delivered,Not Delivered',
            'payment_status' => 'required|in:Paid,Not Paid',
        ]);

        $laundry_id = $validated['id'];

        Laundry::findOrFail($laundry_id)->update([
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

        Laundry::findOrFail($id)->delete();

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

    public function downloadAllLaundry()
{
    // Fetch all Laundry records.
    $laundryRecords = \App\Models\Laundry::all();
    $filename = 'laundry_all.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=' . $filename,
    ];

    $columns = ['Name', 'Phone', 'Price', 'Total', 'Date Received'];

    $callback = function() use ($laundryRecords, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);
        foreach ($laundryRecords as $record) {
            fputcsv($file, [
                $record->name,
                $record->phone,
                $record->price,
                $record->total,
                $record->date_received,
            ]);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function viewLaundryByMonth(Request $request)
    {

        $month = (int) $request->input('month', Carbon::now()->format('m'));
        $year  = (int) $request->input('year', Carbon::now()->format('Y'));

        // Determine the start and end of that month
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate   = $startDate->copy()->endOfMonth();


        $laundryRecords = Laundry::whereBetween('date_received', [$startDate, $endDate])->get();


        $paidLaundry   = $laundryRecords->where('payment_status', 'Paid');
        $unpaidLaundry = $laundryRecords->where('payment_status', 'Not Paid');
        $totalPaid = $paidLaundry->sum(function($item) {
            return is_numeric($item->total) ? (float) $item->total : 0;
        });
        $totalUnpaid = $unpaidLaundry->sum(function($item) {
            return is_numeric($item->total) ? (float) $item->total : 0;
        });
        $grandTotal    = $totalPaid + $totalUnpaid;

        $newLaundry = $laundryRecords->filter(function ($record) use ($startDate) {
            return !Laundry::where('unique_id', $record->unique_id)
                ->where('date_received', '<', $startDate)
                ->exists();
        });

        return view('reports.laundry_month', [
            'month'       => $month,
            'year'        => $year,
            'laundry'     => $laundryRecords,
            'newLaundry'  => $newLaundry,
            'totalPaid'   => $totalPaid,
            'totalUnpaid' => $totalUnpaid,
            'grandTotal'  => $grandTotal,
        ]);
    }


    public function downloadLaundryByMonth(Request $request)
    {
        $month = (int) $request->input('month', Carbon::now()->format('m'));
        $year  = (int) $request->input('year', Carbon::now()->format('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate   = $startDate->copy()->endOfMonth();

        $laundryRecords = Laundry::whereBetween('date_received', [$startDate, $endDate])->get();

        // Calculate totals
        $paidLaundry   = $laundryRecords->where('payment_status', 'Paid');
        $unpaidLaundry = $laundryRecords->where('payment_status', 'Not Paid');
        $totalPaid = $paidLaundry->sum(function($item) {
            return is_numeric($item->total) ? (float) $item->total : 0;
        });
        $totalUnpaid = $unpaidLaundry->sum(function($item) {
            return is_numeric($item->total) ? (float) $item->total : 0;
        });
        $grandTotal    = $totalPaid + $totalUnpaid;

        // Check if user has admin.all permission
        $includePhone = Gate::allows('admin.all');

        $filename = "laundry_{$year}_{$month}.csv";
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($laundryRecords, $totalPaid, $totalUnpaid, $grandTotal, $includePhone) {
            $file = fopen('php://output', 'w');

            // CSV header row - conditionally include Phone
            if ($includePhone) {
                fputcsv($file, ['Unique ID', 'Phone', 'Price', 'Payment Status', 'Date Received']);
            } else {
                fputcsv($file, ['Unique ID', 'Price', 'Payment Status', 'Date Received']);
            }

            // Rows - conditionally include phone
            foreach ($laundryRecords as $record) {
                if ($includePhone) {
                    fputcsv($file, [
                        $record->unique_id,
                        $record->phone,
                        $record->price,
                        $record->payment_status,
                        $record->date_received,
                    ]);
                } else {
                    fputcsv($file, [
                        $record->unique_id,
                        $record->price,
                        $record->payment_status,
                        $record->date_received,
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

    public function downloadNewLaundryByMonth(Request $request)
    {
        $month = (int) $request->input('month', Carbon::now()->format('m'));
        $year  = (int) $request->input('year', Carbon::now()->format('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate   = $startDate->copy()->endOfMonth();

        $laundryRecords = Laundry::whereBetween('date_received', [$startDate, $endDate])->get();

        // Identify new records
        $newLaundry = $laundryRecords->filter(function ($record) use ($startDate) {
            return !Laundry::where('unique_id', $record->unique_id)
                ->where('date_received', '<', $startDate)
                ->exists();
        });

        // Check if user has admin.all permission
        $includePhone = Gate::allows('admin.all');

        $filename = "new_laundry_{$year}_{$month}.csv";
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($newLaundry, $includePhone) {
            $file = fopen('php://output', 'w');

            // CSV header - conditionally include Phone
            if ($includePhone) {
                fputcsv($file, ['Unique ID', 'Phone', 'Price', 'Payment Status', 'Date Received']);
            } else {
                fputcsv($file, ['Unique ID', 'Price', 'Payment Status', 'Date Received']);
            }

            // Rows - conditionally include phone
            foreach ($newLaundry as $record) {
                if ($includePhone) {
                    fputcsv($file, [
                        $record->unique_id,
                        $record->phone,
                        $record->price,
                        $record->payment_status,
                        $record->date_received,
                    ]);
                } else {
                    fputcsv($file, [
                        $record->unique_id,
                        $record->price,
                        $record->payment_status,
                        $record->date_received,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}
