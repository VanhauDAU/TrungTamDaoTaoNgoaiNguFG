<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use App\Models\Facility\PhongHoc;
use App\Models\Auth\TaiKhoan;

class BuoiHoc extends Model
{
    //
    protected $table = 'buoihoc';
    protected $primaryKey = 'buoiHocId';
    protected $fillable = [
        'lopHocId',
        'tenBuoiHoc',
        'ngayHoc',
        'caHocId',
        'phongHocId',
        'taiKhoanId',
        'ghiChu',
        'daDiemDanh',
        'daHoanThanh',
        'trangThai'
    ];

    protected $casts = [
        'trangThai' => 'integer',
    ];

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lopHocId', 'lopHocId');
    }

    public function caHoc()
    {
        return $this->belongsTo(CaHoc::class, 'caHocId', 'caHocId');
    }

    public function phongHoc()
    {
        return $this->belongsTo(PhongHoc::class, 'phongHocId', 'phongHocId');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    /** Danh sách điểm danh của từng học viên trong buổi học này */
    public function diemDanhs()
    {
        return $this->hasMany(DiemDanh::class, 'buoiHocId', 'buoiHocId');
    }
}