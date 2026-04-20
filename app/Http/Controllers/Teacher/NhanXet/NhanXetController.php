<?php

namespace App\Http\Controllers\Teacher\NhanXet;

use App\Http\Controllers\Controller;

class NhanXetController extends Controller
{
    public function index()
    {
        return view('internal.placeholder', [
            'title' => 'Nhận xét học viên',
            'description' => 'Portal giáo viên đã có khung điều hướng riêng. Module nhận xét sẽ được bổ sung sau khi hoàn tất nền tảng 4 portal.',
        ]);
    }
}
