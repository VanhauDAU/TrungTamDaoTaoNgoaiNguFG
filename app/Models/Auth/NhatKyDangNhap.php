<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class NhatKyDangNhap extends Model
{
    public $timestamps = false;

    protected $table = 'nhatky_dangnhap';

    protected $fillable = [
        'taiKhoan',
        'ip',
        'thanhCong',
        'userAgent',
        'thoiGian',
    ];

    protected $casts = [
        'thanhCong' => 'boolean',
        'thoiGian' => 'datetime',
    ];

    /**
     * Đếm số lần đăng nhập thất bại liên tiếp gần đây theo tài khoản + IP.
     * Chuỗi thất bại sẽ dừng ngay khi gặp một lần đăng nhập thành công.
     */
    public static function soLanThatBaiLienTiep(string $taiKhoan, string $ip, int $gioReset = 24, int $limit = 50): int
    {
        $records = static::where(function ($q) use ($taiKhoan, $ip) {
            $q->where('taiKhoan', $taiKhoan)
                ->orWhere('ip', $ip);
        })
            ->where('thoiGian', '>=', now()->subHours($gioReset))
            ->orderByDesc('thoiGian')
            ->limit($limit)
            ->get(['thanhCong']);

        $failures = 0;

        foreach ($records as $record) {
            if ((bool) $record->thanhCong) {
                break;
            }

            $failures++;
        }

        return $failures;
    }

    /**
     * Lấy thời điểm thất bại cuối cùng.
     */
    public static function thoiDiemThatBaiCuoi(string $taiKhoan, string $ip): ?\Carbon\Carbon
    {
        $record = static::where(function ($q) use ($taiKhoan, $ip) {
            $q->where('taiKhoan', $taiKhoan)
                ->orWhere('ip', $ip);
        })
            ->where('thanhCong', false)
            ->orderByDesc('thoiGian')
            ->first();

        return $record?->thoiGian;
    }

    /**
     * Ghi nhận một lần đăng nhập.
     */
    public static function ghiLog(string $taiKhoan, string $ip, bool $thanhCong, ?string $userAgent = null): static
    {
        return static::create([
            'taiKhoan' => $taiKhoan,
            'ip' => $ip,
            'thanhCong' => $thanhCong,
            'userAgent' => $userAgent,
            'thoiGian' => now(),
        ]);
    }
}
