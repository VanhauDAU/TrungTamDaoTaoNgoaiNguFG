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
     * Đếm số lần đăng nhập thất bại gần đây theo tài khoản + IP.
     */
    public static function soLanThatBaiGanDay(string $taiKhoan, string $ip, int $phut = 15): int
    {
        return static::where(function ($q) use ($taiKhoan, $ip) {
            $q->where('taiKhoan', $taiKhoan)
                ->orWhere('ip', $ip);
        })
            ->where('thanhCong', false)
            ->where('thoiGian', '>=', now()->subMinutes($phut))
            ->count();
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
