<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Model;

class TinhThanh extends Model
{
    //
    protected $table = 'tinhthanh';
    protected $primaryKey = 'tinhThanhId';
    protected $fillable = [
        'tenTinhThanh',
        'slug'
    ];
    public function coSoDaoTao()
    {
        return $this->hasMany(CoSoDaoTao::class, 'tinhThanhId', 'tinhThanhId');
    }
}
