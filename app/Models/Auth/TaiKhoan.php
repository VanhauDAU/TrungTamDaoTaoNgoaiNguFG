<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Education\DangKyLopHoc;

class TaiKhoan extends Authenticatable
{
    // Constants cho role
    const ROLE_HOC_VIEN  = 0;
    const ROLE_GIAO_VIEN = 1;
    const ROLE_NHAN_VIEN = 2;
    const ROLE_ADMIN     = 3;

    //
    use Notifiable;
    protected $table = 'taikhoan';
    protected $primaryKey = 'taiKhoanId';
    protected $keyType    = 'int';
    public    $incrementing = true;
    protected $fillable = [
        'taiKhoan',
        'email',
        'matKhau',
        'role',
        'nhomQuyenId',
        'trangThai',
        'remember_token',
        'lastLogin'
    ];
    protected $hidden = [
        'matKhau',
        'remember_token',
    ];
    protected $casts = [
        'role'        => 'integer',
        'trangThai'   => 'integer',
        'nhomQuyenId' => 'integer',
    ];
    public function username()
    {
        return 'taiKhoan';
    }

    /** Kiểm tra có phải Admin (role = 3) không */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Kiểm tra có phải nhân sự (giáo viên/nhân viên/admin) không */
    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_GIAO_VIEN,
            self::ROLE_NHAN_VIEN,
            self::ROLE_ADMIN,
        ]);
    }

    /** Trả về nhãn tên role */
    public function getRoleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_HOC_VIEN  => 'Học viên',
            self::ROLE_GIAO_VIEN => 'Giáo viên',
            self::ROLE_NHAN_VIEN => 'Nhân viên',
            self::ROLE_ADMIN     => 'Admin',
            default              => 'Không xác định',
        };
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

    /** Nhóm quyền được gán cho tài khoản này */
    public function nhomQuyen()
    {
        return $this->belongsTo(NhomQuyen::class, 'nhomQuyenId', 'nhomQuyenId');
    }

    /**
     * Kiểm tra user có quyền thực hiện action trên tính năng không.
     *
     * @param string $feature  VD: 'khoa_hoc', 'tai_chinh'
     * @param string $action   'xem' | 'them' | 'sua' | 'xoa'
     */
    public function canDo(string $feature, string $action = 'xem'): bool
    {
        // Admin luôn có toàn quyền
        if ($this->isAdmin()) {
            return true;
        }

        // Chưa gắn nhóm quyền → không có quyền
        if (!$this->nhomQuyen) {
            return false;
        }

        $colMap = [
            'xem'  => 'coXem',
            'them' => 'coThem',
            'sua'  => 'coSua',
            'xoa'  => 'coXoa',
        ];

        $col = $colMap[$action] ?? 'coXem';

        $pq = $this->nhomQuyen->phanQuyens()
                ->where('tinhNang', $feature)
                ->first();

        return $pq ? (bool) $pq->{$col} : false;
    }
}
