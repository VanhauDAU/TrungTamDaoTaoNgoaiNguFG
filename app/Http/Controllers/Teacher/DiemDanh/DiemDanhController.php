<?php

namespace App\Http\Controllers\Teacher\DiemDanh;

use App\Http\Controllers\Controller;

class DiemDanhController extends Controller
{
    public function index()
    {
        return view('internal.placeholder', [
            'title' => 'Điểm danh',
            'description' => 'Portal giáo viên đã có entry point cho điểm danh. Logic nghiệp vụ và giao diện chi tiết sẽ được phát triển ở phase sau.',
        ]);
    }
}
