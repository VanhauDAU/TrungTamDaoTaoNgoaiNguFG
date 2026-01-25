<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class NhanSu extends Model
{
    //
    protected $table = 'nhansu';
    protected $primaryKey = 'taiKhoanId';
    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
}
