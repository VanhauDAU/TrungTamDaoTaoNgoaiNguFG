<?php

namespace App\Models\Interaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LienHe extends Model
{
    use SoftDeletes;
    protected $table = 'lienhe';
    protected $primaryKey = 'lienHeId';
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
