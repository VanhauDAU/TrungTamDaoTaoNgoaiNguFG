<?php

namespace App\Http\Controllers\Staff\HocVien;

use App\Http\Controllers\Admin\HocVien\DangKyHocController as AdminDangKyHocController;

class DangKyHocController extends AdminDangKyHocController
{
    protected function viewPrefix(): string
    {
        return 'staff.dang-ky';
    }
}
