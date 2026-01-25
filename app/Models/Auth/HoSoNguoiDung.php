<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class HoSoNguoiDung extends Model
{
    //
    protected $table = 'hosonguoidung';
    protected $primaryKey = 'taiKhoanId';
    protected $fillable = [
        'taiKhoanId',
        'hoTen',
        'soDienThoai',
        'ngaySinh',
        'gioiTinh',
        'diaChi',
        'cccd',
        'anhDaiDien'
    ];

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
}
