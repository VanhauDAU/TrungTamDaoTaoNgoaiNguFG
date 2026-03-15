<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NhanSuGoiLuongChiTiet extends Model
{
    public const LOAI_PHU_CAP = 'PHU_CAP';
    public const LOAI_KHAU_TRU = 'KHAU_TRU';
    public const LOAI_THUONG = 'THUONG';
    public const LOAI_KHAC = 'KHAC';

    protected $table = 'nhansu_goi_luong_chi_tiet';
    protected $primaryKey = 'nhanSuGoiLuongChiTietId';

    protected $fillable = [
        'nhanSuGoiLuongId',
        'loai',
        'tenKhoan',
        'soTien',
        'ghiChu',
        'sortOrder',
    ];

    protected $casts = [
        'soTien' => 'decimal:2',
        'sortOrder' => 'integer',
    ];

    public static function loaiOptions(): array
    {
        return [
            self::LOAI_PHU_CAP => 'Phụ cấp',
            self::LOAI_KHAU_TRU => 'Khấu trừ tham chiếu',
            self::LOAI_THUONG => 'Thưởng cố định',
            self::LOAI_KHAC => 'Khác',
        ];
    }

    public function goiLuong(): BelongsTo
    {
        return $this->belongsTo(NhanSuGoiLuong::class, 'nhanSuGoiLuongId', 'nhanSuGoiLuongId');
    }
}
