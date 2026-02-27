<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class BaiViet extends Model
{
    //
    protected $table = 'baiviet';
    protected $primaryKey = 'baiVietId';
    protected $fillable = [
        'tieuDe',
        'slug',
        'tomTat',
        'noiDung',
        'anhDaiDien',
        'taiKhoanId',
        'luotXem',
        'trangThai',
    ];
    public function danhMucs()
    {
        return $this->belongsToMany(
            DanhMucBaiViet::class,
            'BaiViet_DanhMuc',
            'baiVietId',
            'danhMucId'
        );
    }

    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'BaiViet_Tag',
            'baiVietId',
            'tagId'
        );
    }

    public function taiKhoan()
    {
        return $this->belongsTo(\App\Models\Auth\TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
}
