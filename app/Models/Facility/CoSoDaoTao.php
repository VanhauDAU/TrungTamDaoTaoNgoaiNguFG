<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Model;

class CoSoDaoTao extends Model
{
    //
    protected $table = 'cosodaotao';
    protected $primaryKey = 'coSoId';
    public function tinhThanh()
    {
        return $this->belongsTo(TinhThanh::class, 'tinhThanhId', 'tinhThanhId');
    }
    public function lopHoc(){
        return $this->hasMany(LopHoc::class, 'coSoId', 'coSoId');
    }
}
