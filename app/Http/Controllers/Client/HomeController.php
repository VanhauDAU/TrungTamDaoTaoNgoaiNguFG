<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Course\KhoaHoc;
use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index(){
        $khoaHocs = KhoaHoc::all();
        $giaoViens = TaiKhoan::where('role', 1)->get();
        return view('clients.home.index', compact('khoaHocs','giaoViens'));
    }
}
