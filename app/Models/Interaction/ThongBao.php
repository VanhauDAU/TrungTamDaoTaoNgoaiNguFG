<?php

namespace App\Models\Interaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\TaiKhoan;

class ThongBao extends Model
{
    use SoftDeletes;

    protected $table = 'thongbao';
    protected $primaryKey = 'thongBaoId';

    // ── Constants: Loại đối tượng gửi ──────────────────────
    const DOI_TUONG_TAT_CA      = 0; // Tất cả user active
    const DOI_TUONG_THEO_LOP    = 1; // Theo lớp học
    const DOI_TUONG_THEO_KHOA   = 2; // Theo khóa học
    const DOI_TUONG_CA_NHAN     = 3; // Cá nhân (1 user)
    const DOI_TUONG_THEO_ROLE   = 4; // Theo role (admin/giaovien/nhanvien/hocvien)
    const DOI_TUONG_THEO_CO_SO  = 5; // Theo cơ sở đào tạo

    // ── Constants: Loại thông báo (danh mục) ───────────────
    const LOAI_HE_THONG  = 0;
    const LOAI_HOC_TAP   = 1;
    const LOAI_TAI_CHINH = 2;
    const LOAI_SU_KIEN   = 3;
    const LOAI_KHAN_CAP  = 4;

    // ── Constants: Ưu tiên ─────────────────────────────────
    const UU_TIEN_BINH_THUONG = 0;
    const UU_TIEN_QUAN_TRONG  = 1;
    const UU_TIEN_KHAN_CAP    = 2;

    // ── Constants: Trạng thái gửi ────────────────────────────
    const SEND_TRANG_THAI_NHAP      = 0;
    const SEND_TRANG_THAI_DA_LEN_LICH = 1;
    const SEND_TRANG_THAI_DA_GUI    = 2;
    const SEND_TRANG_THAI_GUI_LOI   = 3;
    const SEND_TRANG_THAI_DANG_XU_LY = 4;

    public static function doiTuongLabels(): array
    {
        return [
            self::DOI_TUONG_TAT_CA    => 'Tất cả',
            self::DOI_TUONG_THEO_LOP  => 'Theo lớp học',
            self::DOI_TUONG_THEO_KHOA => 'Theo khóa học',
            self::DOI_TUONG_CA_NHAN   => 'Cá nhân',
            self::DOI_TUONG_THEO_ROLE => 'Theo vai trò',
            self::DOI_TUONG_THEO_CO_SO => 'Theo cơ sở đào tạo',
        ];
    }

    public static function loaiLabels(): array
    {
        return [
            self::LOAI_HE_THONG  => 'Hệ thống',
            self::LOAI_HOC_TAP   => 'Học tập',
            self::LOAI_TAI_CHINH => 'Tài chính',
            self::LOAI_SU_KIEN   => 'Sự kiện',
            self::LOAI_KHAN_CAP  => 'Khẩn cấp',
        ];
    }

    public static function uuTienLabels(): array
    {
        return [
            self::UU_TIEN_BINH_THUONG => 'Bình thường',
            self::UU_TIEN_QUAN_TRONG  => 'Quan trọng',
            self::UU_TIEN_KHAN_CAP    => 'Khẩn cấp',
        ];
    }

    public static function sendTrangThaiLabels(): array
    {
        return [
            self::SEND_TRANG_THAI_NHAP => 'Nháp',
            self::SEND_TRANG_THAI_DA_LEN_LICH => 'Đã lên lịch',
            self::SEND_TRANG_THAI_DA_GUI => 'Đã gửi',
            self::SEND_TRANG_THAI_GUI_LOI => 'Gửi lỗi',
            self::SEND_TRANG_THAI_DANG_XU_LY => 'Đang xử lý',
        ];
    }

    protected $fillable = [
        'tieuDe',
        'noiDung',
        'nguoiGuiId',
        'loaiThongBao',
        'doiTuongGui',
        'doiTuongId',
        'ngayGui',
        'trangThai',
        'loaiGui',
        'uuTien',
        'ghim',
        'sendTrangThai',
        'scheduled_at',
        'sent_at',
        'failed_at',
        'failure_reason',
        'hinhAnh',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'ghim'    => 'boolean',
        'ngayGui' => 'datetime',
        'loaiGui' => 'integer',
        'uuTien'  => 'integer',
        'doiTuongGui' => 'integer',
        'sendTrangThai' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ── Relationships ───────────────────────────────────────

    public function nguoiGui()
    {
        return $this->belongsTo(TaiKhoan::class, 'nguoiGuiId', 'taiKhoanId');
    }

    public function nguoiNhans()
    {
        return $this->hasMany(ThongBaoNguoiDung::class, 'thongBaoId', 'thongBaoId');
    }

    public function tepDinhs(): HasMany
    {
        return $this->hasMany(ThongBaoTepDinh::class, 'thongBaoId', 'thongBaoId')
                    ->orderBy('tepDinhId');
    }

    public function lichSus(): HasMany
    {
        return $this->hasMany(ThongBaoLichSu::class, 'thongBaoId', 'thongBaoId')
            ->orderByDesc('created_at');
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeGhim($query)
    {
        return $query->where('ghim', true);
    }

    public function scopeByLoai($query, $loai)
    {
        return $query->where('loaiGui', $loai);
    }

    public function scopeByUuTien($query, $uuTien)
    {
        return $query->where('uuTien', $uuTien);
    }

    public function scopeByDoiTuong($query, $doiTuong)
    {
        return $query->where('doiTuongGui', $doiTuong);
    }

    // ── Computed Attributes ────────────────────────────────

    public function getTongNguoiNhanAttribute(): int
    {
        return $this->nguoiNhans()->count();
    }

    public function getSoNguoiDaDocAttribute(): int
    {
        return $this->nguoiNhans()->where('daDoc', true)->count();
    }

    public function getTiLeDocAttribute(): float
    {
        $tong = $this->getTongNguoiNhanAttribute();
        if ($tong === 0) return 0;
        return round(($this->getSoNguoiDaDocAttribute() / $tong) * 100, 1);
    }

    public function getLoaiLabel(): string
    {
        return self::loaiLabels()[$this->loaiGui] ?? 'Hệ thống';
    }

    public function getUuTienLabel(): string
    {
        return self::uuTienLabels()[$this->uuTien] ?? 'Bình thường';
    }

    public function getDoiTuongLabel(): string
    {
        return self::doiTuongLabels()[$this->doiTuongGui] ?? 'Tất cả';
    }

    public function getLoaiBadgeClass(): string
    {
        return match ((int) $this->loaiGui) {
            self::LOAI_HOC_TAP   => 'badge-hoc-tap',
            self::LOAI_TAI_CHINH => 'badge-tai-chinh',
            self::LOAI_SU_KIEN   => 'badge-su-kien',
            self::LOAI_KHAN_CAP  => 'badge-khan-cap',
            default              => 'badge-he-thong',
        };
    }

    public function getUuTienBadgeClass(): string
    {
        return match ((int) $this->uuTien) {
            self::UU_TIEN_QUAN_TRONG => 'uu-tien-quan-trong',
            self::UU_TIEN_KHAN_CAP   => 'uu-tien-khan-cap',
            default                  => 'uu-tien-binh-thuong',
        };
    }

    public function getSendTrangThaiLabel(): string
    {
        return self::sendTrangThaiLabels()[$this->sendTrangThai] ?? 'Không xác định';
    }

    public function getSendTrangThaiBadgeClass(): string
    {
        return match ((int) $this->sendTrangThai) {
            self::SEND_TRANG_THAI_NHAP => 'send-draft',
            self::SEND_TRANG_THAI_DA_LEN_LICH => 'send-scheduled',
            self::SEND_TRANG_THAI_DA_GUI => 'send-sent',
            self::SEND_TRANG_THAI_GUI_LOI => 'send-failed',
            self::SEND_TRANG_THAI_DANG_XU_LY => 'send-scheduled',
            default => 'send-draft',
        };
    }
}
