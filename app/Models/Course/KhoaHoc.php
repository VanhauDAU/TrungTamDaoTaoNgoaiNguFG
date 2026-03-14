<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Education\LopHoc;

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
}
