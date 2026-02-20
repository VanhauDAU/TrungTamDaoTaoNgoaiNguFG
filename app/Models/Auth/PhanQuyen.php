<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class PhanQuyen extends Model
{
    protected $table      = 'phanQuyen';
    protected $primaryKey = 'phanQuyenId';

    protected $fillable = [
        'nhomQuyenId',
        'tinhNang',
        'coXem',
        'coThem',
        'coSua',
        'coXoa',
    ];

    protected $casts = [
        'coXem'  => 'boolean',
        'coThem' => 'boolean',
        'coSua'  => 'boolean',
        'coXoa'  => 'boolean',
    ];

    public function nhomQuyen()
    {
        return $this->belongsTo(NhomQuyen::class, 'nhomQuyenId', 'nhomQuyenId');
    }
}
