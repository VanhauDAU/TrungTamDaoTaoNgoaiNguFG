<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherScheduleProposal extends Model
{
    protected $table = 'teacher_schedule_proposals';
    protected $primaryKey = 'proposalId';

    protected $fillable = [
        'buoiHocId',
        'taiKhoanId',
        'loaiDeXuat',
        'lyDo',
        'ngayBu',
        'caHocId',
        'trangThai',
        'ghiChuAdmin',
    ];

    public function buoiHoc()
    {
        return $this->belongsTo(\App\Models\Education\BuoiHoc::class, 'buoiHocId', 'buoiHocId');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(\App\Models\Auth\TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
    }

    public function caHoc()
    {
        return $this->belongsTo(\App\Models\Education\CaHoc::class, 'caHocId', 'caHocId');
    }
}

