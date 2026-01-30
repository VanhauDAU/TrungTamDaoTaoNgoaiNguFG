<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course\KhoaHoc;

class LopHoc extends Model
{
    //
    protected $table = 'lophoc';
    protected $primarykey = 'lopHocId';
    public function khoaHoc(){
        return $this->belongsTo(KhoaHoc::class, 'khoaHocId', 'khoaHocId');
    }
}
