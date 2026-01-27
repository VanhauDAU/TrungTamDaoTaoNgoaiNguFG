<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class BaiVietDanhMuc extends Model
{
    //
    protected $table = 'baiviet_danhmuc';
    public function baiViet()
    {
        return $this->belongsTo(BaiViet::class, 'baiVietId', 'baiVietId');
    }
    public function danhMucBaiViet()
    {
        return $this->belongsTo(DanhMucBaiViet::class, 'danhMucId', 'danhMucId');
    }

}
