<?php

namespace App\Http\Controllers\Staff\HocVien;

use App\Http\Controllers\Admin\HocVien\HocVienController as AdminHocVienController;

class HocVienController extends AdminHocVienController
{
    protected function viewPrefix(): string
    {
        return 'staff.hoc-vien';
    }
}
