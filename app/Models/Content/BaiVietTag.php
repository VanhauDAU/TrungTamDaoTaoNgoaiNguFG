<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class BaiVietTag extends Model
{
    //
    protected $table = 'baiviet_tag';
    protected $fillable = [
        'baiVietId',
        'tagId',
    ];
    public function baiViet()
    {
        return $this->belongsTo(BaiViet::class, 'baiVietId', 'baiVietId');
    }
    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tagId', 'tagId');
    }
}
