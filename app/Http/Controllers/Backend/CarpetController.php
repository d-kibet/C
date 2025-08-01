<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Carpet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CarpetController extends Controller
{
    public function AllCarpet(){

        $carpet = Carpet::latest()->get();
        return view('backend.carpet.all_carpet',compact('carpet'));
    } // End Method

    public function CarpetDashboard()
    {
        // Define today and yesterday in 'YYYY-MM-DD' format.
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        // Retrieve only records where date_received is either today or yesterday.
        $carpet = Carpet::whereDate('date_received', $today)
                    ->orWhereDate('date_received', $yesterday)
                    // Optionally, order them so that today's records come first.
                    ->orderByRaw('(DATE(date_received) = CURDATE()) DESC, date_received DESC')
                    ->get();

        return view('admin.index', compact('carpet'));
    } // End Method

    public function AddCarpet(){
        return view('backend.carpet.add_carpet');
    } // End Method

    public function StoreCarpet(Request $request){
        $validateData = $request->validate([
             'uniqueid' => 'required|max:200',
             'name' => 'required|max:200',
             'size' => 'required|max:200',
             'price' => 'required|max:200',
             'phone' => 'required|max:200',
             'location' => 'required|max:400',
             'date_received' => 'required|date',
             'date_delivered' => 'required|date',
             'payment_status' => 'required',
             'transaction_code' => 'required_if:payment_status,Paid|nullable|string|max:255',
             'delivered' => 'required|max:200',

        ]);

        $carpet = Carpet::create(array_merge($validateData, [
            'follow_up_due_at' => Carbon::parse($validateData['date_received'])
                                        ->addDays(config('followup.stages')[1]),
            'transaction_code' => $request->transaction_code,
            // follow_up_stage defaults to 0, last_notified_at/resolved_at null
        ]));

        $notification = array(
            'message' => 'Carpet Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.carpet')->with($notification);

    }

    public function EditCarpet($id){
        $carpet = Carpet::FindOrfail($id);
        return view('backend.carpet.edit_carpet',compact('carpet'));
    }

    public function UpdateCarpet(Request $request){
        $carpet_id = $request->id;

        Carpet::findOrFail($carpet_id)->update([
             'uniqueid' => $request->uniqueid,
             'name' => $request->name,
             'size' => $request->size,
             'price' => $request->price,
             'phone' => $request->phone,
             'location' => $request->location,
             'date_received' => $request->date_received,
             'date_delivered' => $request->date_delivered,
             'payment_status' => $request->payment_status,
             'transaction_code' => $request->transaction_code,
             'delivered' => $request->delivered,
             'created_at' => Carbon::now(),
        ]);

        $notification = array(
            'message' => 'Carpet Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.carpet')->with($notification);

    } // End Method

    public function DeleteCarpet($id){

        Carpet::findOrFail($id)->delete();

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

    public function downloadAllCarpets()
{
    // Fetch all Carpet records.
    $carpets = \App\Models\Carpet::all();
    $filename = 'carpets_all.csv';

    // Define headers including Content-Disposition.
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=' . $filename,
    ];

    $columns = ['Unique ID', 'Size', 'Price', 'Payment Status', 'Date Received'];

    $callback = function() use ($carpets, $columns) {
        $file = fopen('php://output', 'w');
        // Output header row.
        fputcsv($file, $columns);
        // Output each carpet record.
        foreach ($carpets as $carpet) {
            fputcsv($file, [
                $carpet->uniqueid,
                $carpet->size,
                $carpet->price,
                $carpet->phone,
                $carpet->payment_status,
                $carpet->date_received,
            ]);
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

    $filename = "carpets_{$year}_{$month}.csv";
    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename={$filename}",
    ];

    $callback = function() use ($carpets, $totalPaid, $totalUnpaid, $grandTotal) {
        $file = fopen('php://output', 'w');
        // Header row
        fputcsv($file, ['Unique ID', 'Size', 'Price', 'Payment Status', 'Phone', 'Date Received']);

        // Data rows
        foreach ($carpets as $carpet) {
            fputcsv($file, [
                $carpet->uniqueid,
                $carpet->size,
                $carpet->price,
                $carpet->payment_status,
                $carpet->phone,
                $carpet->date_received,
            ]);
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

    $filename = "new_clients_{$year}_{$month}.csv";
    $headers = [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename={$filename}",
    ];

    $callback = function() use ($newCarpets) {
        $file = fopen('php://output', 'w');
        // Header row
        fputcsv($file, ['Unique ID', 'Phone', 'Name', 'Size', 'Price', 'Payment Status', 'Date Received']);

        // Data rows
        foreach ($newCarpets as $carpet) {
            fputcsv($file, [
                $carpet->uniqueid,
                $carpet->phone,
                $carpet->name,
                $carpet->size,
                $carpet->price,
                $carpet->payment_status,
                $carpet->date_received,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

}
