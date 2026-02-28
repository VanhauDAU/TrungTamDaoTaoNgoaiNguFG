<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;

class DanhMucKhoaHoc extends Model
{
    protected $table      = 'danhmuckhoahoc';
    protected $primaryKey = 'danhMucId';

    protected $fillable = [
        'tenDanhMuc',
        'slug',
        'moTa',
        'trangThai',
    ];

    public function khoaHocs()
    {
        return $this->hasMany(KhoaHoc::class, 'danhMucId', 'danhMucId');
    }
}
