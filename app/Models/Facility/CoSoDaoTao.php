<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Model;

class CoSoDaoTao extends Model
{
    //
    protected $table = 'cosodaotao';
    protected $primaryKey = 'coSoId';
    protected $fillable = [
        'maCoSo',
        'slug',
        'tenCoSo',
        'diaChi',
        'soDienThoai',
        'email',
        'email',
        'quanLyId',
        'banDoGoogle',
        'tinhThanhId',
        'ngayKhaiTruong',
        'trangThai'
    ];
    public function tinhThanh()
    {
        return $this->belongsTo(TinhThanh::class, 'tinhThanhId', 'tinhThanhId');
    }
    public function lopHoc(){
        return $this->hasMany(LopHoc::class, 'coSoId', 'coSoId');
    }
    public function quanLy()
    {
        return $this->belongsTo(User::class, 'quanLyId', 'id');
    }
}
