<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class BaoCaoHocTapMau extends Model
{
    protected $table = 'bao_cao_hoc_tap_mau';
    protected $primaryKey = 'baoCaoHocTapMauId';

    protected $fillable = [
        'tenMau',
        'moTa',
        'phienBan',
        'macDinh',
        'kichHoat',
    ];

    protected $casts = [
        'macDinh' => 'boolean',
        'kichHoat' => 'boolean',
    ];

    public function tieuChis()
    {
        return $this->hasMany(BaoCaoHocTapMauTieuChi::class, 'baoCaoHocTapMauId', 'baoCaoHocTapMauId')
            ->orderBy('thuTu');
    }
}
