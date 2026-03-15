<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NhanSuHoSo extends Model
{
    public const TRANG_THAI_NHAP = 'draft';
    public const TRANG_THAI_HOAN_TAT = 'complete';

    protected $table = 'nhansu_hoso';
    protected $primaryKey = 'nhanSuHoSoId';

    protected $fillable = [
        'taiKhoanId',
        'maHoSo',
        'nhanSuMauQuyDinhId',
        'tieuDeMauSnapshot',
        'noiDungQuyDinhSnapshot',
        'trangThaiHoSo',
        'ghiChuHoSo',
    ];

    public function taiKhoan(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function nhanSuMauQuyDinh(): BelongsTo
    {
        return $this->belongsTo(NhanSuMauQuyDinh::class, 'nhanSuMauQuyDinhId', 'nhanSuMauQuyDinhId');
    }

    public function isCompleted(): bool
    {
        return $this->trangThaiHoSo === self::TRANG_THAI_HOAN_TAT;
    }
}
