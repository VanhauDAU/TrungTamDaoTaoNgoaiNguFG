<?php

namespace App\Models\Interaction;

use Illuminate\Database\Eloquent\Model;

class LienHe extends Model
{
    //
    protected $table = 'lienhe';
    protected $primaryKey = 'LienHeId';
    protected $fillable = [
        'hoTen',
        'email',
        'soDienThoai',
        'tieuDe',
        'noiDung',
        'trangThai',
        'taiKhoanId'
    ];
}
