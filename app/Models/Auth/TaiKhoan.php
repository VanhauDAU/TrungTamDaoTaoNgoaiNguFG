<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Education\DangKyLopHoc;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoNguoiDung;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TaiKhoan extends Authenticatable implements MustVerifyEmail
{
    // Constants cho role
    const ROLE_HOC_VIEN = 0;
    const ROLE_GIAO_VIEN = 1;
    const ROLE_NHAN_VIEN = 2;
    const ROLE_ADMIN = 3;

    //
    use MustVerifyEmailTrait, Notifiable, SoftDeletes;
    protected $table = 'taikhoan';
    protected $primaryKey = 'taiKhoanId';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'taiKhoan',
        'email',
        'matKhau',
        'role',
        'nhomQuyenId',
        'trangThai',
        'phaiDoiMatKhau',
        'auth_provider',
        'google_id',
        'google_avatar',
        'email_verified_at',
        'remember_token',
        'lastLogin'
    ];
    protected $hidden = [
        'matKhau',
        'remember_token',
    ];
    protected $casts = [
        'role' => 'integer',
        'trangThai' => 'integer',
        'phaiDoiMatKhau' => 'integer',
        'nhomQuyenId' => 'integer',
        'email_verified_at' => 'datetime',
        'lastLogin' => 'datetime',
    ];

    public function username()
    {
        return 'taiKhoan';
    }

    public static function prefixForRole(int $role): string
    {
        return match ($role) {
            self::ROLE_HOC_VIEN => 'HV',
            self::ROLE_GIAO_VIEN => 'GV',
            self::ROLE_NHAN_VIEN => 'NV',
            self::ROLE_ADMIN => 'AD',
            default => 'TK',
        };
    }

    public static function generateTemporaryUsername(int $role): string
    {
        return strtolower(static::prefixForRole($role)) . '_pending_' . Str::lower((string) Str::ulid());
    }

    public static function buildSystemUsername(int $role, int $taiKhoanId): string
    {
        return static::prefixForRole($role) . str_pad((string) $taiKhoanId, 6, '0', STR_PAD_LEFT);
    }

    public function assignSystemUsername(): void
    {
        $username = static::buildSystemUsername((int) $this->role, (int) $this->getKey());

        if ($this->taiKhoan !== $username) {
            $this->forceFill(['taiKhoan' => $username])->saveQuietly();
        }
    }

    public function rotateRememberToken(?string $reason = null, ?string $sessionId = null): void
    {
        $this->setRememberToken(Str::random(60));
        $this->saveQuietly();

        if ($reason !== null) {
            NhatKyBaoMat::create([
                'taiKhoanId' => $this->taiKhoanId,
                'sessionId' => $sessionId,
                'suKien' => 'remember_token_rotated',
                'moTa' => $this->rememberTokenRotationDescription($reason),
                'duLieu' => ['reason' => $reason],
                'thoiGian' => now(),
            ]);
        }
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
        ], true);
    }

    /** Trả về nhãn tên role */
    public function getRoleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_HOC_VIEN => 'Học viên',
            self::ROLE_GIAO_VIEN => 'Giáo viên',
            self::ROLE_NHAN_VIEN => 'Nhân viên',
            self::ROLE_ADMIN => 'Admin',
            default => 'Không xác định',
        };
    }

    public function getAuthProviderLabel(): string
    {
        return match ($this->auth_provider) {
            'google' => 'Google',
            default => 'Email và mật khẩu',
        };
    }

    public function getAvatarUrl(): string
    {
        $path = $this->hoSoNguoiDung?->anhDaiDien;

        if (is_string($path) && $path !== '') {
            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            return asset('storage/' . ltrim($path, '/'));
        }

        if (is_string($this->google_avatar) && $this->google_avatar !== '') {
            return $this->google_avatar;
        }

        return asset('assets/images/user-default.png');
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

    public function nhanSuHoSo(): HasOne
    {
        return $this->hasOne(NhanSuHoSo::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function nhanSuGoiLuongs(): HasMany
    {
        return $this->hasMany(NhanSuGoiLuong::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function nhanSuTaiLieus(): HasMany
    {
        return $this->hasMany(NhanSuTaiLieu::class, 'taiKhoanId', 'taiKhoanId');
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

    /** Thông báo đã gửi (với tư cách người gửi) */
    public function thongBaoDaGui()
    {
        return $this->hasMany(ThongBao::class, 'nguoiGuiId', 'taiKhoanId');
    }

    /** Thông báo nhận được (qua bảng pivot thongbaonguoidung) */
    public function thongBaoNhanDuoc()
    {
        return $this->hasMany(ThongBaoNguoiDung::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function phienDangNhaps(): HasMany
    {
        return $this->hasMany(PhienDangNhap::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function nhatKyBaoMats(): HasMany
    {
        return $this->hasMany(NhatKyBaoMat::class, 'taiKhoanId', 'taiKhoanId');
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
            'xem' => 'coXem',
            'them' => 'coThem',
            'sua' => 'coSua',
            'xoa' => 'coXoa',
        ];

        $col = $colMap[$action] ?? 'coXem';

        $pq = $this->nhomQuyen->phanQuyens()
            ->where('tinhNang', $feature)
            ->first();

        return $pq ? (bool) $pq->{$col} : false;
    }

    private function rememberTokenRotationDescription(string $reason): string
    {
        return match ($reason) {
            'password_changed' => 'Xoay remember token sau khi người dùng đổi mật khẩu.',
            'force_password_change' => 'Xoay remember token sau khi đổi mật khẩu bắt buộc.',
            'password_reset' => 'Xoay remember token sau khi đặt lại mật khẩu qua email.',
            'admin_password_reset' => 'Xoay remember token do quản trị viên reset mật khẩu.',
            'account_locked' => 'Xoay remember token khi tài khoản bị khóa.',
            'logout_all_devices' => 'Xoay remember token khi đăng xuất khỏi tất cả thiết bị.',
            'device_revoke' => 'Xoay remember token khi thu hồi một thiết bị đã đăng nhập.',
            default => 'Xoay remember token.',
        };
    }
}
