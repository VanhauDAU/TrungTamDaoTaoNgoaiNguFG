<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Models\Course\KhoaHoc;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index(){
        $khoaHocs = KhoaHoc::all();
        $topGiaoVien = TaiKhoan::where('role', 1)->take(4)->get();
        return view('clients.home.index', compact('khoaHocs', 'topGiaoVien'));
    }
}
