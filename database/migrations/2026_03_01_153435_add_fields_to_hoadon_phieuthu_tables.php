<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hoadon', function (Blueprint $table) {
            $table->string('maHoaDon', 20)->nullable()->unique()->after('hoaDonId');
            $table->tinyInteger('loaiHoaDon')->default(0)->after('phuongThucThanhToan')
                ->comment('0=Đăng ký mới, 1=Gia hạn, 2=Khác');
            $table->date('ngayHetHan')->nullable()->after('ngayLap');
            $table->unsignedBigInteger('nguoiLapId')->nullable()->after('taiKhoanId');
            $table->decimal('giamGia', 15, 2)->default(0)->after('tongTien');
            $table->decimal('thue', 5, 2)->default(0)->after('giamGia');
            $table->decimal('tongTienSauThue', 15, 2)->default(0)->after('thue');
        });

        Schema::table('phieuthu', function (Blueprint $table) {
            $table->string('maPhieuThu', 20)->nullable()->unique()->after('phieuThuId');
            $table->tinyInteger('phuongThucThanhToan')->default(1)->after('soTien')
                ->comment('1=Tiền mặt, 2=Chuyển khoản, 3=VNPay');
            $table->tinyInteger('trangThai')->default(1)->after('ghiChu')
                ->comment('0=Hủy, 1=Hợp lệ');
            $table->unsignedBigInteger('nguoiDuyetId')->nullable()->after('taiKhoanId');
        });
    }

    public function down(): void
    {
        Schema::table('hoadon', function (Blueprint $table) {
            $table->dropColumn([
                'maHoaDon',
                'loaiHoaDon',
                'ngayHetHan',
                'nguoiLapId',
                'giamGia',
                'thue',
                'tongTienSauThue'
            ]);
        });

        Schema::table('phieuthu', function (Blueprint $table) {
            $table->dropColumn([
                'maPhieuThu',
                'phuongThucThanhToan',
                'trangThai',
                'nguoiDuyetId'
            ]);
        });
    }
};
