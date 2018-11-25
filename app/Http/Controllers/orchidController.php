<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Orchid;
use Log;

class orchidController extends Controller
{
    public function getOrchid($name){
    	$orchid = Orchid::where('species', '=', $name)->get();
    	return $orchid;
    }
}
