<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course\LoaiKhoaHoc;
use App\Models\Course\KhoaHoc;

class CourseController extends Controller
{
    //
    public function index(){
        $listTypeCourses = LoaiKhoaHoc::all();
        $listCourses = KhoaHoc::where('trangThai',1)->paginate(6);
        return view('clients.courses.index', compact('listTypeCourses','listCourses'));
    }
}
