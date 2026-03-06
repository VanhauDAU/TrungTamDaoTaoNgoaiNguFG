<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('hoadon', 'maHoaDon')) {
            Schema::table('hoadon', function (Blueprint $table) {
                $table->string('maHoaDon', 20)->nullable()->unique()->after('hoaDonId');
            });
        }
        if (!Schema::hasColumn('hoadon', 'loaiHoaDon')) {
            Schema::table('hoadon', function (Blueprint $table) {
                $table->tinyInteger('loaiHoaDon')->default(0)->after('phuongThucThanhToan')
                    ->comment('0=Đăng ký mới, 1=Gia hạn, 2=Khác');
            });
        }
        if (!Schema::hasColumn('hoadon', 'ngayHetHan')) {
            Schema::table('hoadon', function (Blueprint $table) {
                $table->date('ngayHetHan')->nullable()->after('ngayLap');
            });
        }
        if (!Schema::hasColumn('hoadon', 'nguoiLapId')) {
            Schema::table('hoadon', function (Blueprint $table) {
                $table->unsignedBigInteger('nguoiLapId')->nullable()->after('taiKhoanId');
            });
        }
        if (!Schema::hasColumn('hoadon', 'giamGia')) {
            Schema::table('hoadon', function (Blueprint $table) {
                $table->decimal('giamGia', 15, 2)->default(0)->after('tongTien');
            });
        }
        if (!Schema::hasColumn('hoadon', 'thue')) {
            Schema::table('hoadon', function (Blueprint $table) {
                $table->decimal('thue', 5, 2)->default(0)->after('giamGia');
            });
        }
        if (!Schema::hasColumn('hoadon', 'tongTienSauThue')) {
            Schema::table('hoadon', function (Blueprint $table) {
                $table->decimal('tongTienSauThue', 15, 2)->default(0)->after('thue');
            });
        }

        if (!Schema::hasColumn('phieuthu', 'maPhieuThu')) {
            Schema::table('phieuthu', function (Blueprint $table) {
                $table->string('maPhieuThu', 20)->nullable()->unique()->after('phieuThuId');
            });
        }
        if (!Schema::hasColumn('phieuthu', 'phuongThucThanhToan')) {
            Schema::table('phieuthu', function (Blueprint $table) {
                $table->tinyInteger('phuongThucThanhToan')->default(1)->after('soTien')
                    ->comment('1=Tiền mặt, 2=Chuyển khoản, 3=VNPay');
            });
        }
        if (!Schema::hasColumn('phieuthu', 'trangThai')) {
            Schema::table('phieuthu', function (Blueprint $table) {
                $table->tinyInteger('trangThai')->default(1)->after('ghiChu')
                    ->comment('0=Hủy, 1=Hợp lệ');
            });
        }
        if (!Schema::hasColumn('phieuthu', 'nguoiDuyetId')) {
            Schema::table('phieuthu', function (Blueprint $table) {
                $table->unsignedBigInteger('nguoiDuyetId')->nullable()->after('taiKhoanId');
            });
        }
    }

    public function down(): void
    {
        Schema::table('hoadon', function (Blueprint $table) {
            $drops = [];
            foreach (['maHoaDon', 'loaiHoaDon', 'ngayHetHan', 'nguoiLapId', 'giamGia', 'thue', 'tongTienSauThue'] as $col) {
                if (Schema::hasColumn('hoadon', $col)) $drops[] = $col;
            }
            if (!empty($drops)) $table->dropColumn($drops);
        });

        Schema::table('phieuthu', function (Blueprint $table) {
            $drops = [];
            foreach (['maPhieuThu', 'phuongThucThanhToan', 'trangThai', 'nguoiDuyetId'] as $col) {
                if (Schema::hasColumn('phieuthu', $col)) $drops[] = $col;
            }
            if (!empty($drops)) $table->dropColumn($drops);
        });
    }
};
