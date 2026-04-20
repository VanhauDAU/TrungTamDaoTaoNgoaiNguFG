<?php

namespace App\Http\Controllers\Staff\KhoaHoc;

use App\Http\Controllers\Admin\KhoaHoc\LopHocController as AdminLopHocController;

class LopHocController extends AdminLopHocController
{
    protected function viewPrefix(): string
    {
        return 'staff.lop-hoc';
    }
}
