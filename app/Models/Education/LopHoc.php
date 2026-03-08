<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course\KhoaHoc;
use App\Models\Facility\PhongHoc;
use App\Models\Facility\CoSoDaoTao as CoSo;
use App\Models\Auth\TaiKhoan;
use App\Models\Course\HocPhi;

class LopHoc extends Model
{
    use SoftDeletes;

    public const TRANG_THAI_SAP_MO = 0;
    public const TRANG_THAI_DANG_TUYEN_SINH = 1;
    public const TRANG_THAI_DA_DONG = 2;
    public const TRANG_THAI_DA_HUY = 3;
    public const TRANG_THAI_DANG_HOC = 4;

    protected $table = 'lophoc';
    protected $primaryKey = 'lopHocId';
    protected $fillable = [
        'slug',
        'maLopHoc',
        'khoaHocId',
        'tenLopHoc',
        'phongHocId',
        'taiKhoanId',
        'hocPhiId', 
        'ngayBatDau',
        'ngayKetThuc',
        'soBuoiDuKien',
        'soHocVienToiDa',
        'donGiaDay',
        'coSoId',
        'caHocId',
        'lichHoc', // Lịch học trong tuần: "2,4,6" (Thứ 2, 4, 6)
        'trangThai',
    ];

    protected $casts = [
        'trangThai' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public static function generateMaLopHoc($khoaHocId)
    {
        $khoaHoc = KhoaHoc::find($khoaHocId);
        $maVietTatKhoa = 'KH';

        if ($khoaHoc && $khoaHoc->maKhoaHoc) {
            $parts = explode('-', $khoaHoc->maKhoaHoc);
            // Prefix dựa vào maDanhMuc hoặc ký tự đầu của maKhoaHoc
            $maVietTatKhoa = $parts[0] ?? substr($khoaHoc->maKhoaHoc, 0, 2);
        }

        $namCuoi = substr(date('Y'), -2); // vd 2026 -> "26"
        $prefix = $namCuoi . strtoupper(substr($maVietTatKhoa, 0, 2));

        $count = self::where('maLopHoc', 'LIKE', $prefix . '%')->count();
        $so = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $so;
    }

    public function khoaHoc(){
        return $this->belongsTo(KhoaHoc::class, 'khoaHocId', 'khoaHocId');
    }
    public function phongHoc(){
        return $this->belongsTo(PhongHoc::class, 'phongHocId', 'phongHocId');
    }
    public function taiKhoan(){
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
    public function hocPhi(){
        return $this->belongsTo(HocPhi::class, 'hocPhiId', 'hocPhiId');
    }
    public function coSo(){
        return $this->belongsTo(CoSo::class, 'coSoId', 'coSoId');
    }
    public function caHoc(){
        return $this->belongsTo(CaHoc::class, 'caHocId', 'caHocId');
    }
    public function buoiHocs(){
        return $this->hasMany(BuoiHoc::class, 'lopHocId', 'lopHocId');
    }
    public function dangKyLopHocs(){
        return $this->hasMany(DangKyLopHoc::class, 'lopHocId', 'lopHocId');
    }

    public static function trangThaiLabels(): array
    {
        return [
            self::TRANG_THAI_SAP_MO => 'Sắp mở',
            self::TRANG_THAI_DANG_TUYEN_SINH => 'Đang mở đăng ký',
            self::TRANG_THAI_DA_DONG => 'Đã đóng đăng ký',
            self::TRANG_THAI_DA_HUY => 'Đã hủy',
            self::TRANG_THAI_DANG_HOC => 'Đang học',
        ];
    }

    public static function trangThaiOptions(): array
    {
        return [
            self::TRANG_THAI_SAP_MO => self::trangThaiLabels()[self::TRANG_THAI_SAP_MO],
            self::TRANG_THAI_DANG_TUYEN_SINH => self::trangThaiLabels()[self::TRANG_THAI_DANG_TUYEN_SINH],
            self::TRANG_THAI_DANG_HOC => self::trangThaiLabels()[self::TRANG_THAI_DANG_HOC],
            self::TRANG_THAI_DA_DONG => self::trangThaiLabels()[self::TRANG_THAI_DA_DONG],
            self::TRANG_THAI_DA_HUY => self::trangThaiLabels()[self::TRANG_THAI_DA_HUY],
        ];
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return self::trangThaiLabels()[$this->trangThai] ?? 'Không xác định';
    }

    public function isSapMo(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_SAP_MO;
    }

    public function isOpenForRegistration(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DANG_TUYEN_SINH;
    }

    public function isClosedForRegistration(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DA_DONG;
    }

    public function isInProgress(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DANG_HOC;
    }

    public function isCancelled(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_DA_HUY;
    }

    public function isOperational(): bool
    {
        return in_array((int) $this->trangThai, [
            self::TRANG_THAI_DANG_TUYEN_SINH,
            self::TRANG_THAI_DANG_HOC,
        ], true);
    }

    public function scopeOpenForRegistration($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_DANG_TUYEN_SINH);
    }

    public function scopeInProgress($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_DANG_HOC);
    }

    public function scopeOperational($query)
    {
        return $query->whereIn('trangThai', [
            self::TRANG_THAI_DANG_TUYEN_SINH,
            self::TRANG_THAI_DANG_HOC,
        ]);
    }
}
