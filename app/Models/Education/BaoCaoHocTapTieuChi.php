<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class BaoCaoHocTapTieuChi extends Model
{
    protected $table = 'bao_cao_hoc_tap_tieu_chi';
    protected $primaryKey = 'baoCaoHocTapTieuChiId';

    protected $fillable = [
        'baoCaoHocTapId',
        'baoCaoHocTapMauTieuChiId',
        'nhom',
        'maTieuChi',
        'tenTieuChi',
        'loaiDuLieu',
        'giaTriMucDanhGia',
        'giaTriSo',
        'noiDungNhanXet',
        'tuyChon',
        'batBuoc',
        'isReadonly',
        'thuTu',
    ];

    protected $casts = [
        'giaTriSo' => 'decimal:2',
        'tuyChon' => 'array',
        'batBuoc' => 'boolean',
        'isReadonly' => 'boolean',
        'thuTu' => 'integer',
    ];

    public function baoCao()
    {
        return $this->belongsTo(BaoCaoHocTap::class, 'baoCaoHocTapId', 'baoCaoHocTapId');
    }

    public function mauTieuChi()
    {
        return $this->belongsTo(BaoCaoHocTapMauTieuChi::class, 'baoCaoHocTapMauTieuChiId', 'baoCaoHocTapMauTieuChiId');
    }
}
