<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('lienhe', 'loaiLienHe')) {
            Schema::table('lienhe', function (Blueprint $table) {
                $table->enum('loaiLienHe', ['tu_van', 'ho_tro', 'khieu_nai', 'khac'])
                    ->default('tu_van')
                    ->after('trangThai')
                    ->comment('Loại liên hệ: tu_van, ho_tro, khieu_nai, khac');
            });
        }
        if (!Schema::hasColumn('lienhe', 'ghiChuNoiBo')) {
            Schema::table('lienhe', function (Blueprint $table) {
                $table->text('ghiChuNoiBo')->nullable()->after('loaiLienHe');
            });
        }
        if (!Schema::hasColumn('lienhe', 'nguoiPhuTrachId')) {
            Schema::table('lienhe', function (Blueprint $table) {
                $table->unsignedBigInteger('nguoiPhuTrachId')->nullable()->after('ghiChuNoiBo');
            });
        }
        if (!Schema::hasColumn('lienhe', 'thoiGianXuLy')) {
            Schema::table('lienhe', function (Blueprint $table) {
                $table->timestamp('thoiGianXuLy')->nullable()->after('nguoiPhuTrachId');
            });
        }
    }

    public function down(): void
    {
        Schema::table('lienhe', function (Blueprint $table) {
            $drops = [];
            foreach (['loaiLienHe', 'ghiChuNoiBo', 'nguoiPhuTrachId', 'thoiGianXuLy'] as $col) {
                if (Schema::hasColumn('lienhe', $col)) $drops[] = $col;
            }
            if (!empty($drops)) $table->dropColumn($drops);
        });
    }
};
