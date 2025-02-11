<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laundry;
use Carbon\Carbon;

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
            'name' => 'required|max:200',
            'phone' => 'required|max:200',
            'location' => 'required|max:200',
            'unique_id' => 'required|max:200',
            'date_received' => 'required|date',
            'date_delivered' => 'required|date',
            'quantity' => 'required',
            'item_description' => 'required|max:200',
            'weight' => 'required|max:200',
            'price' => 'required|max:200',
            'total' => 'required|max:200',
            'delivered' => 'required|max:200',
            'payment_status' => 'required|max:200',

       ]);

       Laundry::insert([
            'name' => $request->name,
           'phone' => $request->phone,
           'location' => $request->location,
           'unique_id' => $request->unique_id,
            'date_received' => $request->date_received,
            'date_delivered' => $request->date_delivered,
            'quantity' => $request->quantity,
            'item_description' => $request->item_description,
            'weight' => $request->weight,
            'price' => $request->price,
            'total' => $request->total,
            'delivered' => $request->delivered,
            'payment_status' => $request->payment_status,
            'created_at' => Carbon::now(),
       ]);

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
        $laundry_id = $request->id;

        Laundry::findOrFail($laundry_id)->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'location' => $request->location,
            'unique_id' => $request->unique_id,
             'date_received' => $request->date_received,
             'date_delivered' => $request->date_delivered,
             'quantity' => $request->quantity,
             'item_description' => $request->item_description,
             'weight' => $request->weight,
             'price' => $request->price,
             'total' => $request->total,
             'delivered' => $request->delivered,
             'payment_status' => $request->payment_status,
             'created_at' => Carbon::now(),
        ]);

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
}
