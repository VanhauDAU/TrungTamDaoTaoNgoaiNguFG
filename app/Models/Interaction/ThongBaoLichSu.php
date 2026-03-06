<?php

namespace App\Models\Interaction;

use Illuminate\Database\Eloquent\Model;

class ThongBaoLichSu extends Model
{
    protected $table = 'thongbao_lichsu';
    public $timestamps = false;

    protected $fillable = [
        'thongBaoId',
        'taiKhoanId',
        'hanhDong',
        'moTa',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];
}

