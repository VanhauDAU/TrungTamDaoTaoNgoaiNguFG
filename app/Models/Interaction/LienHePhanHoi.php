<?php

namespace App\Models\Interaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienHePhanHoi extends Model
{
    protected $table = 'lienhe_phanhoi';
    protected $primaryKey = 'phanHoiId';

    protected $fillable = [
        'lienHeId',
        'noiDung',
        'loai',
        'nguoiGuiId',
        'tenNguoiGui',
        'daGuiEmail',
    ];

    protected $casts = [
        'daGuiEmail' => 'boolean',
        'created_at' => 'datetime',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function lienHe(): BelongsTo
    {
        return $this->belongsTo(LienHe::class, 'lienHeId', 'lienHeId');
    }

    public function nguoiGui(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'nguoiGuiId', 'id');
    }
}
