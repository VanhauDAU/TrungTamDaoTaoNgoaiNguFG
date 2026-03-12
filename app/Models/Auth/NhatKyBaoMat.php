<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NhatKyBaoMat extends Model
{
    public $timestamps = false;

    protected $table = 'nhatky_bao_mat';
    protected $primaryKey = 'nhatKyBaoMatId';
    protected $fillable = [
        'taiKhoanId',
        'phienDangNhapId',
        'sessionId',
        'suKien',
        'moTa',
        'ipAddress',
        'userAgent',
        'duLieu',
        'thoiGian',
    ];

    protected $casts = [
        'duLieu' => 'array',
        'thoiGian' => 'datetime',
    ];

    public function taiKhoan(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function phienDangNhap(): BelongsTo
    {
        return $this->belongsTo(PhienDangNhap::class, 'phienDangNhapId', 'phienDangNhapId');
    }
}
