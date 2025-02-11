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
             'delivered' => 'required|max:200',

        ]);

        Carpet::insert([
            'uniqueid' => $request->uniqueid,
            'name' => $request->name,
             'size' => $request->size,
             'price' => $request->price,
             'phone' => $request->phone,
             'location' => $request->location,
             'date_received' => $request->date_received,
             'date_delivered' => $request->date_delivered,
             'payment_status' => $request->payment_status,
             'delivered' => $request->delivered,
             'created_at' => Carbon::now(),
        ]);

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

//     public function HistoryCarpet(Request $request, $phone)
// {
//     // Build the query to get carpets matching the given phone number.
//     $query = Carpet::where('phone', $phone);

//     // If both start and end dates are provided, apply the date range filter.
//     if ($request->filled('start_date') && $request->filled('end_date')) {
//         $query->whereBetween('created_at', [
//             Carbon::parse($request->start_date)->startOfDay(),
//             Carbon::parse($request->end_date)->endOfDay(),
//         ]);
//     }

//     // Paginate the results.
//     $client = $query->paginate(10);

//     // Return the view with the paginated results and the phone number.
//     return view('backend.carpet.history_carpet', compact('client', 'phone'));
// }
}
