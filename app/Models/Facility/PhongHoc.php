<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Education\LopHoc;
use App\Models\Education\BuoiHoc;

class PhongHoc extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'phongHocId';
    protected $table = 'phonghoc';

    // ── Trạng thái phòng học (chuẩn hệ thống đào tạo) ──────────────────────
    // 0 = Vô hiệu hóa : Phòng đóng cửa hẳn, không đưa vào lịch học
    // 1 = Sẵn sàng     : Phòng trống, sẵn sàng xếp lịch lớp mới
    // 2 = Đang sử dụng : (Tính động) Đang có buổi học diễn ra
    // 3 = Bảo trì      : Tạm ngưng để sửa chữa / bảo dưỡng
    const TRANG_THAI_VO_HIEU   = 0;
    const TRANG_THAI_SAN_SANG  = 1;
    const TRANG_THAI_DANG_DUNG = 2; // Tính động, không lưu thủ công
    const TRANG_THAI_BAO_TRI   = 3;

    protected $fillable = [
        'tenPhong',
        'sucChua',
        'trangThietBi',
        'coSoId',
        'khuBlock',
        'tang',
        'trangThai',
        'ghiChuBaoTri',
        'ngayBaoTri',
    ];

    protected $casts = [
        'sucChua'    => 'integer',
        'tang'       => 'integer',
        'trangThai'  => 'integer',
        'ngayBaoTri' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['is_currently_in_use'];

    public function getIsCurrentlyInUseAttribute(): bool
    {
        return $this->isCurrentlyInUse();
    }

    // ── Labels & Options ─────────────────────────────────────────────────────

    public static function trangThaiLabels(): array
    {
        return [
            self::TRANG_THAI_VO_HIEU   => 'Vô hiệu hóa',
            self::TRANG_THAI_SAN_SANG  => 'Sẵn sàng',
            self::TRANG_THAI_DANG_DUNG => 'Đang sử dụng',
            self::TRANG_THAI_BAO_TRI   => 'Bảo trì',
        ];
    }

    /** Chỉ những trạng thái admin có thể đặt thủ công */
    public static function trangThaiManual(): array
    {
        return [
            self::TRANG_THAI_VO_HIEU  => 'Vô hiệu hóa',
            self::TRANG_THAI_SAN_SANG => 'Sẵn sàng',
            self::TRANG_THAI_BAO_TRI  => 'Bảo trì',
        ];
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return self::trangThaiLabels()[$this->trangThai] ?? 'Không xác định';
    }

    public function getViTriLabelAttribute(): string
    {
        $parts = [];

        if ($this->khuBlock) {
            $parts[] = 'Block ' . trim((string) $this->khuBlock);
        }

        if ($this->tang !== null) {
            $parts[] = 'Tầng ' . $this->tang;
        }

        return $parts !== [] ? implode(' · ', $parts) : 'Chưa phân khu';
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_SAN_SANG;
    }

    public function isInMaintenance(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_BAO_TRI;
    }

    public function isDisabled(): bool
    {
        return (int) $this->trangThai === self::TRANG_THAI_VO_HIEU;
    }

    public function isOperational(): bool
    {
        return in_array((int) $this->trangThai, [
            self::TRANG_THAI_SAN_SANG,
            self::TRANG_THAI_DANG_DUNG,
        ], true);
    }

    /**
     * Kiểm tra phòng có đang có buổi học nào ĐANG DIỄN RA không (tính động).
     * Dùng để hiển thị badge "Đang sử dụng" theo thời gian thực (realtime) mà không khóa chết phòng.
     */
    public function isCurrentlyInUse(): bool
    {
        return $this->buoiHocDangDienRa()->exists();
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function coSoDaoTao()
    {
        return $this->belongsTo(CoSoDaoTao::class, 'coSoId', 'coSoId');
    }

    /** Toàn bộ lịch sử lớp học đã / đang dùng phòng này */
    public function lopHocs()
    {
        return $this->hasMany(LopHoc::class, 'phongHocId', 'phongHocId');
    }

    /** Lớp học đang hoạt động trong phòng (chỉ mang tính tham khảo dài hạn) */
    public function lopHocDangHoc()
    {
        return $this->hasMany(LopHoc::class, 'phongHocId', 'phongHocId')
            ->whereIn('trangThai', [
                LopHoc::TRANG_THAI_DANG_HOC,
                LopHoc::TRANG_THAI_CHOT_DANH_SACH,
                LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
            ]);
    }

    /** Toàn bộ buổi học đã/đang/sẽ diễn ra trong phòng này */
    public function buoiHocs()
    {
        return $this->hasMany(BuoiHoc::class, 'phongHocId', 'phongHocId');
    }

    /** Buổi học cụ thể đang diễn ra ngay lúc này trong phòng */
    public function buoiHocDangDienRa()
    {
        return $this->buoiHocs()->where('trangThai', BuoiHoc::TRANG_THAI_DANG_DIEN_RA);
    }

    public function nhatKys()
    {
        return $this->hasMany(CoSoNhatKy::class, 'phongHocId', 'phongHocId');
    }

    public function maintenanceTickets()
    {
        return $this->hasMany(PhongHocBaoTri::class, 'phongHocId', 'phongHocId');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_SAN_SANG);
    }

    public function scopeInMaintenance($query)
    {
        return $query->where('trangThai', self::TRANG_THAI_BAO_TRI);
    }

    public function scopeOperational($query)
    {
        return $query->whereIn('trangThai', [
            self::TRANG_THAI_SAN_SANG,
            self::TRANG_THAI_DANG_DUNG,
        ]);
    }
}
