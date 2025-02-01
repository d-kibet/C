<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function ServicePage(){

        return view('frontend.services_details');

     } // End Method
}
