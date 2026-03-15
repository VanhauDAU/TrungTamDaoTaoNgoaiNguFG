<?php

namespace App\Data\Admin\NhanVien;

use App\Models\Auth\TaiKhoan;

readonly class CreatedStaffAccountResult
{
    public function __construct(
        public TaiKhoan $taiKhoan,
        public string $plainTemporaryPassword,
        public string $oneTimeToken,
    ) {
    }
}
