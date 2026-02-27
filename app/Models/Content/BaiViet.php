<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class BaiViet extends Model
{
    //
    protected $table = 'baiviet';
    protected $primaryKey = 'baiVietId'; 
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
}
