<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Mpesa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MpesaController extends Controller
{
    public function AllMpesa(){


     // Define today and yesterday using Carbon
$today = Carbon::today();
$yesterday = Carbon::yesterday();

// Calculate today's total
$todayTotal = Mpesa::whereDate('date', $today)
    ->get()
    ->sum(function ($item) {
        return (float)$item->cash
             + (float)$item->float
             + (float)($item->working ?? 0)
             + (float)$item->account;
    });

// Calculate yesterday's total
$yesterdayTotal = Mpesa::whereDate('date', $yesterday)
    ->get()
    ->sum(function ($item) {
        return (float)$item->cash
             + (float)$item->float
             + (float)($item->working ?? 0)
             + (float)$item->account;
    });

// Compute the summary difference
$summaryDifference = $yesterdayTotal - $todayTotal;

// Retrieve all Mpesa records so that rows with today's date come first,
// then the rest in descending order by date.
$mpesaData = Mpesa::orderByRaw('(date = CURDATE()) desc, date desc')->get();

$previousTotal = null;
foreach ($mpesaData as $item) {
    // Calculate total for this record
    $total = (float)$item->cash
           + (float)$item->float
           + (float)($item->working ?? 0)
           + (float)$item->account;
    $item->total = $total;

    // Calculate the difference compared to the previous record
    if ($previousTotal !== null) {
        $item->difference = $total - $previousTotal;
    } else {
        $item->difference = 0; // First record: no previous value
    }
    $previousTotal = $total;
}

// Pass all data to the view
return view('backend.mpesa.all_mpesa', compact(
    'mpesaData',
    'todayTotal',
    'yesterdayTotal',
    'summaryDifference'
));
}

    public function AddMpesa(){
        return view('backend.mpesa.add_mpesa');

    }

    public function StoreMpesa(Request $request)
    {
        $validateData = $request->validate([
            'date'    => 'required|date',
            'cash'    => 'required|numeric',
            'float'   => 'required|numeric',
            'working' => 'required|numeric',
            'account' => 'required|numeric',
        ]);

        // Ensure numeric values
        $cash    = (float)$request->cash;
        $float   = (float)$request->float;
        $working = (float)$request->working;
        $account = (float)$request->account;

        // Calculate the total
        $total = $cash + $float + $working + $account;

        // Retrieve the most recent record (ordered by date, or ID if dates can be identical)
        $lastRecord = Mpesa::orderBy('date', 'desc')->orderBy('id', 'desc')->first();

        // Calculate the difference compared to the last record's total
        // If there's no last record, default difference to 0
        $difference = $lastRecord ? $total - $lastRecord->total : 0;

        // Insert the new record with the computed total and difference
        Mpesa::insert([
            'date'       => $request->date,
            'cash'       => $cash,
            'float'      => $float,
            'working'    => $working,
            'account'    => $account,
            'total'      => $total,
            'difference' => $difference,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $notification = array(
            'message'    => 'Mpesa Record Added Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.mpesa')->with($notification);
    }


    public function EditMpesa($id){
        $mpesa = Mpesa::FindOrfail($id);
        return view('backend.mpesa.edit_mpesa',compact('mpesa'));
    }

    public function UpdateMpesa(Request $request)
{
    $mpesa_id = $request->id;

    $validateData = $request->validate([
        'date'    => 'required|date',
        'cash'    => 'required|numeric',
        'float'   => 'required|numeric',
        'working' => 'required|numeric',
        'account' => 'required|numeric',
    ]);

    // Compute new total
    $cash    = (float)$request->cash;
    $float   = (float)$request->float;
    $working = (float)$request->working;
    $account = (float)$request->account;
    $total   = $cash + $float + $working + $account;

    // For simplicity, let’s not update the difference here (or you could recalc it separately)
    Mpesa::findOrFail($mpesa_id)->update([
        'date'    => $request->date,
        'cash'    => $cash,
        'float'   => $float,
        'working' => $working,
        'account' => $account,
        'total'   => $total,
        // 'difference' => <logic if needed>
    ]);

    $notification = array(
        'message'    => 'Mpesa Record Updated Successfully',
        'alert-type' => 'success'
    );

    return redirect()->route('all.mpesa')->with($notification);
}


    public function DeleteMpesa($id){

        Mpesa::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Item Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function downloadAllMpesa()
{
    // Fetch all Mpesa records.
    $mpesaRecords = \App\Models\Mpesa::all();
    $filename = 'mpesa_all.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=' . $filename,
    ];

    $columns = ['Date', 'Cash', 'Float', 'Working', 'Account'];

    $callback = function() use ($mpesaRecords, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);
        foreach ($mpesaRecords as $record) {
            fputcsv($file, [
                $record->date,
                $record->cash,
                $record->float,
                $record->working,
                $record->account,
            ]);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}



}
