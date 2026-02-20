<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auth\TaiKhoan;

class HomeController extends Controller
{
    public function index()
    {
        $totalStudent = TaiKhoan::where('role', 0)->count();
        return view('admin.dashboard', compact('totalStudent'));
    }
}
