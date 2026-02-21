<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Model;

class TinhThanh extends Model
{
    protected $table = 'tinhthanh';
    protected $primaryKey = 'tinhThanhId';
    public $timestamps = false;

    protected $fillable = [
        'tenTinhThanh',
        'slug',
        'maAPI',
        'division_type',
        'codename',
    ];

    public function coSoDaoTao()
    {
        return $this->hasMany(CoSoDaoTao::class, 'tinhThanhId', 'tinhThanhId');
    }
}
