<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Mpesa;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
    public function AllMpesa(){
        $mpesa = Mpesa::latest()->get();
        return view('backend.mpesa.all_mpesa',compact('mpesa'));

    }

    public function AddMpesa(){
        return view('backend.mpesa.add_mpesa');

    }

    public function StoreMpesa(Request $request){
        $validateData = $request->validate([
            'date' => 'required|date',
            'cash' => 'required|max:200',
            'float' => 'required|max:200',
            'working' => 'required|max:200',
            'account' => 'required|max:200',

       ]);

       Mpesa::insert([
        'date' => $request->date,
        'cash' => $request->cash,
        'float' => $request->float,
        'working' => $request->working,
        'account' => $request->account,
       ]);

       $notification = array(
        'message' => 'Mpesa Record Added Successfully',
        'alert-type' => 'success'
    );

    return redirect()->route('all.mpesa')->with($notification);

    }

    public function EditMpesa($id){
        $mpesa = Mpesa::FindOrfail($id);
        return view('backend.mpesa.edit_mpesa',compact('mpesa'));
    }

    public function UpdateMpesa(Request $request){
        $mpesa_id = $request->id;

        Mpesa::findOrFail($mpesa_id)->update([
            'date' => $request->date,
            'cash' => $request->cash,
            'float' => $request->float,
            'working' => $request->working,
            'account' => $request->account,

        ]);

        $notification = array(
            'message' => 'Mpesa Record Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.mpesa')->with($notification);
    }
}
