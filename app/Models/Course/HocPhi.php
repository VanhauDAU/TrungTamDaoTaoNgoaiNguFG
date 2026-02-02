<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;

class HocPhi extends Model
{
    //
    protected $table = 'hocphi';
    protected $primaryKey = 'hocPhiId';
    
    public function khoaHoc(){
        return $this->belongsTo(KhoaHoc::class, 'khoaHocId', 'khoaHocId');
    }
}
