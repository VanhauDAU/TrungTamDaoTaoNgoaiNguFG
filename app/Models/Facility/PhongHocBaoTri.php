<?php

namespace App\Models\Facility;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PhongHocBaoTri extends Model
{
    public const TRANG_THAI_MOI_TAO = 0;
    public const TRANG_THAI_DANG_XU_LY = 1;
    public const TRANG_THAI_DA_HOAN_TAT = 2;
    public const TRANG_THAI_DA_HUY = 3;

    public const UU_TIEN_THAP = 0;
    public const UU_TIEN_BINH_THUONG = 1;
    public const UU_TIEN_CAO = 2;
    public const UU_TIEN_KHAN = 3;

    protected $table = 'phonghoc_baotri';
    protected $primaryKey = 'phongHocBaoTriId';

    protected $fillable = [
        'phongHocId',
        'coSoId',
        'maPhieu',
        'tieuDe',
        'moTa',
        'mucDoUuTien',
        'trangThai',
        'createdById',
        'assignedToId',
        'ngayYeuCau',
        'ngayBatDau',
        'ngayHoanTat',
        'ketQuaXuLy',
    ];

    protected $casts = [
        'phongHocId' => 'integer',
        'coSoId' => 'integer',
        'mucDoUuTien' => 'integer',
        'trangThai' => 'integer',
        'createdById' => 'integer',
        'assignedToId' => 'integer',
        'ngayYeuCau' => 'datetime',
        'ngayBatDau' => 'datetime',
        'ngayHoanTat' => 'datetime',
    ];

    public static function generateCode(): string
    {
        return 'BT-' . Str::upper(Str::random(8));
    }

    public static function trangThaiLabels(): array
    {
        return [
            self::TRANG_THAI_MOI_TAO => 'Mới tạo',
            self::TRANG_THAI_DANG_XU_LY => 'Đang xử lý',
            self::TRANG_THAI_DA_HOAN_TAT => 'Đã hoàn tất',
            self::TRANG_THAI_DA_HUY => 'Đã hủy',
        ];
    }

    public static function mucDoUuTienLabels(): array
    {
        return [
            self::UU_TIEN_THAP => 'Thấp',
            self::UU_TIEN_BINH_THUONG => 'Bình thường',
            self::UU_TIEN_CAO => 'Cao',
            self::UU_TIEN_KHAN => 'Khẩn',
        ];
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return self::trangThaiLabels()[(int) $this->trangThai] ?? 'Không xác định';
    }

    public function getMucDoUuTienLabelAttribute(): string
    {
        return self::mucDoUuTienLabels()[(int) $this->mucDoUuTien] ?? 'Không xác định';
    }

    public function phongHoc()
    {
        return $this->belongsTo(PhongHoc::class, 'phongHocId', 'phongHocId');
    }

    public function coSoDaoTao()
    {
        return $this->belongsTo(CoSoDaoTao::class, 'coSoId', 'coSoId');
    }

    public function nguoiTao()
    {
        return $this->belongsTo(TaiKhoan::class, 'createdById', 'taiKhoanId');
    }

    public function nguoiPhuTrach()
    {
        return $this->belongsTo(TaiKhoan::class, 'assignedToId', 'taiKhoanId');
    }
}
