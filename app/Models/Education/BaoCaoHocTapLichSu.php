<?php

namespace App\Models\Education;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;

class BaoCaoHocTapLichSu extends Model
{
    public $timestamps = false;

    protected $table = 'bao_cao_hoc_tap_lich_su';
    protected $primaryKey = 'baoCaoHocTapLichSuId';

    protected $fillable = [
        'baoCaoHocTapId',
        'hanhDong',
        'trangThaiTruoc',
        'trangThaiSau',
        'nguoiThucHienId',
        'ghiChu',
        'duLieu',
        'created_at',
    ];

    protected $casts = [
        'duLieu' => 'array',
        'created_at' => 'datetime',
    ];

    public function baoCao()
    {
        return $this->belongsTo(BaoCaoHocTap::class, 'baoCaoHocTapId', 'baoCaoHocTapId');
    }

    public function nguoiThucHien()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiThucHienId', 'taiKhoanId');
    }
}
