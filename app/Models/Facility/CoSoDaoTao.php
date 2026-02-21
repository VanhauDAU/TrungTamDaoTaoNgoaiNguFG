<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\TaiKhoan;
use App\Models\Auth\NhanSu;

class CoSoDaoTao extends Model
{
    protected $table = 'cosodaotao';
    protected $primaryKey = 'coSoId';
    public $timestamps = true;

    protected $fillable = [
        'maCoSo',
        'slug',
        'tenCoSo',
        'diaChi',
        'soDienThoai',
        'email',
        'tinhThanhId',
        'maPhuongXa',
        'tenPhuongXa',
        'viDo',
        'kinhDo',
        'banDoGoogle',
        'ngayKhaiTruong',
        'trangThai',
    ];

    protected $casts = [
        'trangThai'      => 'integer',
        'ngayKhaiTruong' => 'date',
        'viDo'           => 'float',
        'kinhDo'         => 'float',
    ];

    public function tinhThanh()
    {
        return $this->belongsTo(TinhThanh::class, 'tinhThanhId', 'tinhThanhId');
    }
    public function phongHocs()
    {
        return $this->hasMany(PhongHoc::class, 'coSoId', 'coSoId');
    }

    public function nhanSus()
    {
        return $this->hasMany(NhanSu::class, 'coSoId', 'coSoId');
    }

    /**
     * Trả về địa chỉ đầy đủ gồm diaChi + tenPhuongXa + tenTinhThanh
     */
    public function getDiaChiDayDuAttribute(): string
    {
        $parts = array_filter([
            $this->diaChi,
            $this->tenPhuongXa,
            optional($this->tinhThanh)->tenTinhThanh,
        ]);
        return implode(', ', $parts);
    }

    /**
     * Kiểm tra có đủ tọa độ để hiển thị map không
     */
    public function hasCoordinates(): bool
    {
        return !is_null($this->viDo) && !is_null($this->kinhDo);
    }
}
