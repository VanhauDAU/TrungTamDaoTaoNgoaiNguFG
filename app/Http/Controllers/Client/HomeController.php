<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Course\KhoaHoc;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index(){
        $khoaHocs = KhoaHoc::all();
        
        return view('client.home.index', compact('khoaHocs'));
    }
}
