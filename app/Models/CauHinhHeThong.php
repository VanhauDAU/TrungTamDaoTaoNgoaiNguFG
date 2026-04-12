<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CauHinhHeThong extends Model
{
    protected $table = 'cau_hinh_he_thong';

    protected $fillable = [
        'nhom',
        'khoa',
        'ten_hien_thi',
        'gia_tri',
        'kieu_du_lieu',
        'mo_ta',
        'gia_tri_mac_dinh',
        'tuy_chon',
        'yeu_cau',
        'thu_tu',
        'an_trong_ui',
    ];

    protected $casts = [
        'tuy_chon'   => 'array',
        'yeu_cau'    => 'boolean',
        'an_trong_ui' => 'boolean',
    ];

    // ── Scope ────────────────────────────────────────────────────
    public function scopeNhom($query, string $nhom)
    {
        return $query->where('nhom', $nhom)->orderBy('thu_tu')->orderBy('id');
    }

    public function scopeHienThi($query)
    {
        return $query->where('an_trong_ui', false);
    }

    // ── Helpers ──────────────────────────────────────────────────
    /**
     * Lấy giá trị thực tế (fallback về mặc định nếu rỗng).
     */
    public function getGiaTriThucTe(): mixed
    {
        $val = $this->gia_tri ?? $this->gia_tri_mac_dinh;

        return match ($this->kieu_du_lieu) {
            'boolean' => filter_var($val, FILTER_VALIDATE_BOOLEAN),
            'number'  => is_numeric($val) ? (float) $val : 0,
            'json'    => json_decode($val ?? '{}', true),
            default   => $val,
        };
    }

    /**
     * Static helper: lấy giá trị theo khóa.
     */
    public static function get(string $khoa, mixed $default = null): mixed
    {
        $row = Cache::remember("cauhinh:{$khoa}", 300, fn () => static::where('khoa', $khoa)->first());

        if (! $row) {
            return $default;
        }

        return $row->getGiaTriThucTe() ?? $default;
    }

    /**
     * Xóa cache khi lưu / cập nhật.
     */
    protected static function booted(): void
    {
        static::saved(fn ($m) => Cache::forget("cauhinh:{$m->khoa}"));
        static::deleted(fn ($m) => Cache::forget("cauhinh:{$m->khoa}"));
    }

    // ── Label nhóm ───────────────────────────────────────────────
    public static function labelNhom(): array
    {
        return [
            'he_thong'    => ['label' => 'Hệ thống',         'icon' => 'fa-server',         'color' => '#7c3aed'],
            'giao_duc'    => ['label' => 'Giáo dục',         'icon' => 'fa-graduation-cap', 'color' => '#0891b2'],
            'bao_mat'     => ['label' => 'Bảo mật',          'icon' => 'fa-shield-alt',     'color' => '#dc2626'],
            'thong_bao'   => ['label' => 'Thông báo',        'icon' => 'fa-bell',           'color' => '#d97706'],
            'tai_chinh'   => ['label' => 'Tài chính',        'icon' => 'fa-coins',          'color' => '#059669'],
            'giao_dien'   => ['label' => 'Giao diện',        'icon' => 'fa-palette',        'color' => '#db2777'],
            'tich_hop'    => ['label' => 'Tích hợp API',     'icon' => 'fa-plug',           'color' => '#6366f1'],
        ];
    }
}
