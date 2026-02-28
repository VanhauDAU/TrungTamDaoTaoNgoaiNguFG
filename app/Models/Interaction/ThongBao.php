<?php

namespace App\Models\Interaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;

class ThongBao extends Model
{
    //
    protected $table = 'thongbao';
    protected $primaryKey = 'thongBaoId';
    protected $fillable = [
        'tieuDe',
        'noiDung',
        'nguoiGuiId',
        'loaiThongBao',
        'doiTuongGui', // 	0-Tất cả, 1-Theo lớp, 2-Theo khóa học, 3-Cá nhân,....
        'doiTuongId',
        'ngayGui',
        'trangThai',
        'created_at',
        'updated_at',
    ];

    public function nguoiGui()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiGuiId', 'taiKhoanId');
    }

    public function doiTuong()
    {
        return $this->belongsTo(TaiKhoan::class, 'doiTuongId', 'taiKhoanId');
    }
}
