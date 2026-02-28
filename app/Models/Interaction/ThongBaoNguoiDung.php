<?php

namespace App\Models\Interaction;

use Illuminate\Database\Eloquent\Model;

class ThongBaoNguoiDung extends Model
{
    //
    protected $table = 'thongbaonguoidung';
    protected $primaryKey = 'thongBaoNguoiDungId';
    protected $fillable = [
        'thongBaoId',
        'taiKhoanId',
        'daDoc',
        'ngayDoc',
        'created_at',
        'updated_at',
    ];

    public function thongBao()
    {
        return $this->belongsTo(ThongBao::class, 'thongBaoId', 'thongBaoId');
    }

    public function nguoiDung()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiDungId', 'taiKhoanId');
    }
}
