<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Education\LopHoc;
use App\Models\Education\LopHocChinhSachGia;

class KhoaHoc extends Model
{
    use SoftDeletes;
    protected $table = 'khoahoc'; 
    protected $primaryKey = 'khoaHocId'; 
    protected $fillable = [
        'khoaHocId',
        'maKhoaHoc',
        'danhMucId',
        'tenKhoaHoc',
        'slug',
        'anhKhoaHoc',
        'moTa',
        'doiTuong',
        'yeuCauDauVao',
        'ketQuaDatDuoc',
        'trangThai'
    ];

    public static function generateMaKhoaHoc($danhMucId)
    {
        $danhMuc = DanhMucKhoaHoc::find($danhMucId);
        $maVietTat = $danhMuc && $danhMuc->maDanhMuc ? $danhMuc->maDanhMuc : 'KH';

        $count = self::where('maKhoaHoc', 'LIKE', $maVietTat . '-%')->count();
        $so = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return strtoupper($maVietTat) . '-' . $so;
    }

    public function danhMuc()
    {
        return $this->belongsTo(DanhMucKhoaHoc::class, 'danhMucId', 'danhMucId');
    }
    public function lopHoc(){
        return $this->hasMany(LopHoc::class, 'khoaHocId', 'khoaHocId');
    }

    /**
     * Lấy giá thấp nhất từ các lớp học đang mở (Sắp mở / Đang tuyển sinh).
     * Trả về null nếu chưa có lớp hoặc chưa có chính sách giá.
     */
    public function getLowestPriceAttribute(): ?float
    {
        $openStatuses = [
            LopHoc::TRANG_THAI_SAP_MO,
            LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
        ];

        // Nếu đã eager-load thì dùng collection, tránh N+1
        if ($this->relationLoaded('lopHoc')) {
            $price = $this->lopHoc
                ->whereIn('trangThai', $openStatuses)
                ->map(fn ($l) => $l->relationLoaded('chinhSachGia') && $l->chinhSachGia
                    ? (float) $l->chinhSachGia->hocPhiNiemYet
                    : null)
                ->filter(fn ($v) => $v !== null && $v > 0)
                ->min();

            return $price ?? null;
        }

        return $this->lopHoc()
            ->whereIn('trangThai', $openStatuses)
            ->join('lophoc_chinhsachgia', function ($join) {
                $join->on('lophoc.lopHocId', '=', 'lophoc_chinhsachgia.lopHocId')
                    ->where('lophoc_chinhsachgia.trangThai', 1)
                    ->where('lophoc_chinhsachgia.hocPhiNiemYet', '>', 0);
            })
            ->min('lophoc_chinhsachgia.hocPhiNiemYet');
    }

    /**
     * Lấy số buổi dự kiến từ lớp học đầu tiên đang mở.
     */
    public function getTotalLessonsAttribute(): ?int
    {
        $openStatuses = [
            LopHoc::TRANG_THAI_SAP_MO,
            LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
        ];

        if ($this->relationLoaded('lopHoc')) {
            $val = $this->lopHoc
                ->whereIn('trangThai', $openStatuses)
                ->first()?->soBuoiDuKien;
            return $val !== null ? (int) $val : null;
        }

        return $this->lopHoc()
            ->whereIn('trangThai', $openStatuses)
            ->value('soBuoiDuKien');
    }
}
