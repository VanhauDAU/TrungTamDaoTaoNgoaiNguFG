<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class BaoCaoHocTapMauTieuChi extends Model
{
    protected $table = 'bao_cao_hoc_tap_mau_tieu_chi';
    protected $primaryKey = 'baoCaoHocTapMauTieuChiId';

    protected $fillable = [
        'baoCaoHocTapMauId',
        'nhom',
        'maTieuChi',
        'tenTieuChi',
        'loaiDuLieu',
        'danhSachMuc',
        'tuyChon',
        'batBuoc',
        'isReadonly',
        'thuTu',
    ];

    protected $casts = [
        'danhSachMuc' => 'array',
        'tuyChon' => 'array',
        'batBuoc' => 'boolean',
        'isReadonly' => 'boolean',
        'thuTu' => 'integer',
    ];

    public function mau()
    {
        return $this->belongsTo(BaoCaoHocTapMau::class, 'baoCaoHocTapMauId', 'baoCaoHocTapMauId');
    }
}
