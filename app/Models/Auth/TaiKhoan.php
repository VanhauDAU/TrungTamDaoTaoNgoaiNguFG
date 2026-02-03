<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TaiKhoan extends Authenticatable
{
    //
    use Notifiable;
    protected $table = 'taikhoan';
    protected $primaryKey = 'taiKhoanId';
    protected $fillable = [
        'taiKhoan',
        'email',
        'matKhau',
        'role',
        'trangThai',
        'remember_token',
        'lastLogin'
    ];
    protected $hidden = [
        'matKhau',
        'remember_token',
    ];
    public function username()
    {
        return 'taiKhoan';
    }
    public function getAuthPassword()
    {
        return $this->matKhau;
    }
    public function hoSoNguoiDung()
    {
        return $this->hasOne(HoSoNguoiDung::class, 'taiKhoanId', 'taiKhoanId');
    }
    public function nhanSu()
    {
        return $this->hasOne(NhanSu::class, 'taiKhoanId', 'taiKhoanId');
    }
    public function dangKyLopHocs()
    {
        return $this->hasMany(DangKyLopHoc::class, 'taiKhoanId', 'taiKhoanId');
    }
}
