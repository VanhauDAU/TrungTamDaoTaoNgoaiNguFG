<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\LopHoc;

class DangKyLopHoc extends Model
{
    //
    protected $table = 'dangkylophoc';
    protected $primaryKey = 'dangKyLopHocId';
    protected $fillable = [
        'taiKhoanId',
        'lopHocId',
        'ngayDangKy',
        'trangThai',
    ];
    public $timestamps = false;

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lopHocId', 'lopHocId');
    }
}
