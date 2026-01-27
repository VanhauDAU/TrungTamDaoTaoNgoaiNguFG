<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Model;

class TinhThanh extends Model
{
    //
    protected $table = 'tinhthanh';
    protected $primaryKey = 'tinhThanhId';
    public function coSoDaoTao()
    {
        return $this->hasMany(CoSoDaoTao::class, 'tinhThanhId', 'tinhThanhId');
    }
}
