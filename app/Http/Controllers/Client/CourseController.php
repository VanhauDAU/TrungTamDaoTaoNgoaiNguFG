<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course\LoaiKhoaHoc;
use App\Models\Course\KhoaHoc;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $listTypeCourses = LoaiKhoaHoc::all();
        
        // Tạo query builder với điều kiện cơ bản
        $query = KhoaHoc::where('trangThai', 1);
        
        // Lọc theo category nếu có
        if ($request->has('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('loaiKhoaHoc', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }
        
        // Lấy danh sách khóa học với pagination và giữ query parameters
        $listCourses = $query->with('loaiKhoaHoc')->paginate(6)->withQueryString();
        
        return view('clients.courses.index', compact('listTypeCourses', 'listCourses'));
    }
    public function show($slug)
    {
        $course = KhoaHoc::where('slug', $slug)->with('loaiKhoaHoc', 'lopHoc', 'hocPhis')->first();
        return view('clients.courses.show', compact('course'));
    }
}
