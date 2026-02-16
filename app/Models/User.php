<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\NhanSu;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'taikhoan';
    protected $primaryKey = 'taiKhoanId';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'taiKhoan',
        'name',
        'email',
        'matKhau',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'matKhau',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'matKhau' => 'hashed',
        ];
    }

    public function getAuthPassword()
    {
        return $this->matKhau;
    }

    public function hoSoNguoiDung()
    {
        return $this->hasOne(HoSoNguoiDung::class, 'taiKhoanId', 'id');
    }

    public function nhanSu()
    {
        return $this->hasOne(NhanSu::class, 'taiKhoanId', 'id');
    }
}
