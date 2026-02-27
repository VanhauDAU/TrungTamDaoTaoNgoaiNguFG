<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class DanhMucBaiViet extends Model
{
    //
    protected $table = 'danhmucbaiviet';
    protected $fillable = [
        'danhMucId',
        'tenDanhMuc',
        'slug',
        'moTa',
        'trangThai',
    ];
    protected $primaryKey = 'danhMucId';
    public function baiViets()
    {
        return $this->belongsToMany(
            BaiViet::class,
            'BaiViet_DanhMuc',
            'danhMucId',
            'baiVietId'
        );
    }

}
