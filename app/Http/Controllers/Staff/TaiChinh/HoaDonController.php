<?php

namespace App\Http\Controllers\Staff\TaiChinh;

use App\Http\Controllers\Admin\TaiChinh\HoaDonController as AdminHoaDonController;

class HoaDonController extends AdminHoaDonController
{
    protected function viewPrefix(): string
    {
        return 'staff.hoa-don';
    }
}
