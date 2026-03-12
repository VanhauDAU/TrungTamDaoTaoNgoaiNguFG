<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhienDangNhap extends Model
{
    protected $table = 'phien_dang_nhap';
    protected $primaryKey = 'phienDangNhapId';
    protected $fillable = [
        'taiKhoanId',
        'sessionId',
        'portal',
        'loginMethod',
        'remembered',
        'ipAddress',
        'userAgent',
        'deviceName',
        'platform',
        'browser',
        'lastSeenAt',
        'revokedAt',
        'revokeReason',
    ];

    protected $casts = [
        'remembered' => 'boolean',
        'lastSeenAt' => 'datetime',
        'revokedAt' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function taiKhoan(): BelongsTo
    {
        return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt !== null;
    }
}
