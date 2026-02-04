<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

    class HoaDon extends Model
    {
        //
        protected $table = 'hoadon';
        protected $primaryKey = 'hoaDonId';
        protected $fillable = [
            'ngayLap',
            'tongTien',
            'daTra',
            'taiKhoanId',
            'dangKyLopHocId',
            'phuongThucThanhToan',
            'coSoId',
            'trangThai',
            'ghiChu'
        ];
        public $timestamps = false;

        public function taiKhoan()
        {
            return $this->belongsTo(TaiKhoan::class, 'taiKhoanId', 'taiKhoanId');
        }

        public function dangKyLopHoc()
        {
            return $this->belongsTo(DangKyLopHoc::class, 'dangKyLopHocId', 'dangKyLopHocId');
        }

        public function coSo()
        {
            return $this->belongsTo(CoSo::class, 'coSoId', 'coSoId');
        }

        public function phieuThus()
        {
            return $this->hasMany(PhieuThu::class, 'hoaDonId', 'hoaDonId');
        }
}
