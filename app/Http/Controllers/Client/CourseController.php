<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course\LoaiKhoaHoc;
use App\Models\Course\KhoaHoc;
use App\Models\Education\LopHoc;

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
        $course = KhoaHoc::where('slug', $slug)
            ->with([
                'loaiKhoaHoc', 
                'lopHoc.coSo.tinhThanh',  // Load cơ sở và tỉnh thành
                'lopHoc.phongHoc',
                'lopHoc.taiKhoan',
                'hocPhis'
            ])
            ->first();
        
        // Lấy 3 khóa học liên quan cùng loại, khác khóa hiện tại
        $relatedCourses = KhoaHoc::where('loaiKhoaHocId', $course->loaiKhoaHocId)
            ->where('khoaHocId', '!=', $course->khoaHocId)
            ->where('trangThai', 1)
            ->with('loaiKhoaHoc', 'lopHoc')
            ->take(4)
            ->get();
        
        return view('clients.courses.show', compact('course', 'relatedCourses'));
    }
    public function showClass($slug, $slugLopHoc)
    {
        // Lưu ý: $lopHocId ở đây thực chất là slug do route định nghĩa vậy nhưng view truyền vào slug
        // Nên query theo column 'slug'
        
        $class = LopHoc::where('slug', $slugLopHoc)
            ->with([
                'khoaHoc.loaiKhoaHoc', // Để lấy breadcrumb
                'coSo.tinhThanh',      // Địa điểm
                'phongHoc',            // Phòng học
                'taiKhoan.hoSoNguoiDung', // Giảng viên
                'hocPhi'               // Học phí
            ])
            ->firstOrFail();

        // Check xem class có thuộc đúng khóa học không (optional but recommended)
        if ($class->khoaHoc->slug !== $slug) {
            abort(404);
        }

        return view('clients.classes.show', compact('class'));
    }
}
