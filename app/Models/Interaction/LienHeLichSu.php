<?php

namespace App\Models\Interaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienHeLichSu extends Model
{
    public $timestamps = false;

    protected $table = 'lienhe_lichsu';
    protected $primaryKey = 'lichSuId';

    protected $fillable = [
        'lienHeId',
        'hanhDong',
        'noiDung',
        'giaTriCu',
        'giaTriMoi',
        'nguoiThucHienId',
        'tenNguoiThucHien',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ─── Hành động constants ────────────────────────────────────────────────────

    const HANH_DONG = [
        'tiep_nhan'          => ['label' => 'Tiếp nhận liên hệ',     'icon' => 'fa-inbox',          'color' => '#3b82f6'],
        'cap_nhat_trang_thai'=> ['label' => 'Cập nhật trạng thái',   'icon' => 'fa-arrows-rotate',  'color' => '#8b5cf6'],
        'cap_nhat_loai'      => ['label' => 'Đổi loại liên hệ',      'icon' => 'fa-tag',            'color' => '#06b6d4'],
        'ghi_chu'            => ['label' => 'Thêm ghi chú nội bộ',   'icon' => 'fa-sticky-note',    'color' => '#f59e0b'],
        'gan_phu_trach'      => ['label' => 'Gán người phụ trách',   'icon' => 'fa-user-check',     'color' => '#10b981'],
        'phan_hoi'           => ['label' => 'Đã phản hồi nội bộ',    'icon' => 'fa-comment-dots',   'color' => '#6366f1'],
        'gui_email'          => ['label' => 'Gửi email cho khách',   'icon' => 'fa-paper-plane',    'color' => '#059669'],
        'xoa_mem'            => ['label' => 'Chuyển vào thùng rác',  'icon' => 'fa-trash',          'color' => '#ef4444'],
        'khoi_phuc'          => ['label' => 'Khôi phục liên hệ',     'icon' => 'fa-rotate-left',    'color' => '#22c55e'],
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function lienHe(): BelongsTo
    {
        return $this->belongsTo(LienHe::class, 'lienHeId', 'lienHeId');
    }

    // ─── Accessors ──────────────────────────────────────────────────────────────

    public function getHanhDongInfoAttribute(): array
    {
        return self::HANH_DONG[$this->hanhDong] ?? [
            'label' => $this->hanhDong,
            'icon'  => 'fa-circle-info',
            'color' => '#94a3b8',
        ];
    }
}
