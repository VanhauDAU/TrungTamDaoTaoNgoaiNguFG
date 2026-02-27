<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    //
    protected $table = 'tags';
    protected $fillable = [
        'tagId',
        'tenTag',
        'slug',
    ];
    protected $primaryKey = 'tagId';
    public function baiViets()
    {
        return $this->belongsToMany(
            BaiViet::class,
            'BaiViet_Tag',
            'tagId',
            'baiVietId'
        );
    }
}
