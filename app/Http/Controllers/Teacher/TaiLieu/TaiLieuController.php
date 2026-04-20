<?php

namespace App\Http\Controllers\Teacher\TaiLieu;

use App\Http\Controllers\Controller;

class TaiLieuController extends Controller
{
    public function index()
    {
        return view('internal.placeholder', [
            'title' => 'Tài liệu lớp học',
            'description' => 'Portal giáo viên đã sẵn sàng route và layout. Chức năng quản lý tài liệu lớp học sẽ được triển khai ở nhánh tiếp theo.',
        ]);
    }
}
