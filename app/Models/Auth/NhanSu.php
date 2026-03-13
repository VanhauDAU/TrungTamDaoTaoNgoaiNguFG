<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use App\Models\Facility\CoSoDaoTao;
class NhanSu extends Model
{
    //
    protected $table = 'nhansu';
    protected $primaryKey = 'taiKhoanId';
    protected $fillable = [
        'taiKhoanId',
        'maNhanVien',
        'chucVu',
        'luongCoBan',
        'ngayVaoLam',
        'chuyenMon',
        'bangCap',
        'hocVi',
        'coSoId',
        'loaiHopDong',
        'trangThai',
    ];
    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }
    public function coSoDaoTao()
    {
        return $this->belongsTo(CoSoDaoTao::class, 'coSoId', 'coSoId');
    }
}
