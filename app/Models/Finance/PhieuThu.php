<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class PhieuThu extends Model
{
    //
    protected $table = 'phieuthu';
    protected $primaryKey = 'phieuThuId';
    protected $fillable = [
        'hoaDonId',
        'soTien',
        'ngayThu',
        'taiKhoanId', // người thu
        'ghiChu'
    ];

    public function hoaDon()
    {
        return $this->belongsTo(HoaDon::class, 'hoaDonId', 'hoaDonId');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
}
