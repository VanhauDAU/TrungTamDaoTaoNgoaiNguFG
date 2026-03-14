<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lophoc_chinhsachgia')) {
            Schema::create('lophoc_chinhsachgia', function (Blueprint $table) {
                $table->increments('lopHocChinhSachGiaId');
                // Bang lophoc hien tai dung INT co dau cho lopHocId, nen can khop kieu de FK tao duoc.
                $table->integer('lopHocId')->unique();
                $table->tinyInteger('loaiThu')->default(0)->comment('0=Tron goi, 1=Theo thang, 2=Theo dot');
                $table->decimal('hocPhiNiemYet', 15, 2)->default(0);
                $table->unsignedInteger('soBuoiCamKet')->nullable();
                $table->text('ghiChuChinhSach')->nullable();
                $table->dateTime('hieuLucTu')->nullable();
                $table->dateTime('hieuLucDen')->nullable();
                $table->tinyInteger('trangThai')->default(1);
                $table->timestamps();

                $table->foreign('lopHocId')
                    ->references('lopHocId')
                    ->on('lophoc')
                    ->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('lophoc_dotthu')) {
            Schema::create('lophoc_dotthu', function (Blueprint $table) {
                $table->increments('lopHocDotThuId');
                $table->unsignedInteger('lopHocChinhSachGiaId');
                $table->string('tenDotThu', 255);
                $table->unsignedInteger('thuTu')->default(1);
                $table->decimal('soTien', 15, 2)->default(0);
                $table->date('hanThanhToan')->nullable();
                $table->tinyInteger('batBuoc')->default(1);
                $table->tinyInteger('trangThai')->default(1);
                $table->timestamps();

                $table->foreign('lopHocChinhSachGiaId', 'fk_lophoc_dotthu_chinhsachgia')
                    ->references('lopHocChinhSachGiaId')
                    ->on('lophoc_chinhsachgia')
                    ->cascadeOnDelete();

                $table->unique(['lopHocChinhSachGiaId', 'thuTu'], 'uq_lophoc_dotthu_policy_order');
            });
        }

        if (Schema::hasTable('dangkylophoc')) {
            Schema::table('dangkylophoc', function (Blueprint $table) {
                if (!Schema::hasColumn('dangkylophoc', 'lopHocChinhSachGiaId')) {
                    $table->unsignedInteger('lopHocChinhSachGiaId')->nullable()->after('lopHocId');
                }
                if (!Schema::hasColumn('dangkylophoc', 'loaiThuSnapshot')) {
                    $table->tinyInteger('loaiThuSnapshot')->nullable()->after('lopHocChinhSachGiaId');
                }
                if (!Schema::hasColumn('dangkylophoc', 'hocPhiNiemYetSnapshot')) {
                    $table->decimal('hocPhiNiemYetSnapshot', 15, 2)->nullable()->after('loaiThuSnapshot');
                }
                if (!Schema::hasColumn('dangkylophoc', 'giamGiaSnapshot')) {
                    $table->decimal('giamGiaSnapshot', 15, 2)->default(0)->after('hocPhiNiemYetSnapshot');
                }
                if (!Schema::hasColumn('dangkylophoc', 'hocPhiPhaiThuSnapshot')) {
                    $table->decimal('hocPhiPhaiThuSnapshot', 15, 2)->nullable()->after('giamGiaSnapshot');
                }
                if (!Schema::hasColumn('dangkylophoc', 'soBuoiCamKetSnapshot')) {
                    $table->unsignedInteger('soBuoiCamKetSnapshot')->nullable()->after('hocPhiPhaiThuSnapshot');
                }
                if (!Schema::hasColumn('dangkylophoc', 'ghiChuGiaSnapshot')) {
                    $table->text('ghiChuGiaSnapshot')->nullable()->after('soBuoiCamKetSnapshot');
                }
            });

            $this->dropForeignKeyIfExists('dangkylophoc', 'lopHocChinhSachGiaId');

            Schema::table('dangkylophoc', function (Blueprint $table) {
                $table->foreign('lopHocChinhSachGiaId', 'fk_dangkylophoc_pricing_policy')
                    ->references('lopHocChinhSachGiaId')
                    ->on('lophoc_chinhsachgia')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('hoadon')) {
            Schema::table('hoadon', function (Blueprint $table) {
                if (!Schema::hasColumn('hoadon', 'lopHocDotThuId')) {
                    $table->unsignedInteger('lopHocDotThuId')->nullable()->after('dangKyLopHocId');
                }
            });

            $this->dropForeignKeyIfExists('hoadon', 'lopHocDotThuId');

            Schema::table('hoadon', function (Blueprint $table) {
                $table->foreign('lopHocDotThuId', 'fk_hoadon_lophoc_dotthu')
                    ->references('lopHocDotThuId')
                    ->on('lophoc_dotthu')
                    ->nullOnDelete();
            });
        }

        $this->migrateLegacyHocPhiData();

        if (Schema::hasTable('lophoc') && Schema::hasColumn('lophoc', 'hocPhiId')) {
            $this->dropForeignKeyIfExists('lophoc', 'hocPhiId');

            Schema::table('lophoc', function (Blueprint $table) {
                $table->dropColumn('hocPhiId');
            });
        }

        if (Schema::hasTable('hocphi')) {
            Schema::drop('hocphi');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('hocphi')) {
            Schema::create('hocphi', function (Blueprint $table) {
                $table->increments('hocPhiId');
                $table->unsignedInteger('khoaHocId');
                $table->unsignedInteger('soBuoi')->default(1);
                $table->decimal('donGia', 15, 0)->default(0);
                $table->tinyInteger('trangThai')->default(1);
                $table->timestamps();

                $table->foreign('khoaHocId')
                    ->references('khoaHocId')
                    ->on('khoahoc')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('lophoc') && !Schema::hasColumn('lophoc', 'hocPhiId')) {
            Schema::table('lophoc', function (Blueprint $table) {
                $table->unsignedInteger('hocPhiId')->nullable()->after('taiKhoanId');
            });
        }

        if (Schema::hasTable('lophoc_chinhsachgia')) {
            $policies = DB::table('lophoc_chinhsachgia')
                ->join('lophoc', 'lophoc.lopHocId', '=', 'lophoc_chinhsachgia.lopHocId')
                ->select([
                    'lophoc_chinhsachgia.lopHocChinhSachGiaId',
                    'lophoc_chinhsachgia.lopHocId',
                    'lophoc.khoaHocId',
                    'lophoc_chinhsachgia.soBuoiCamKet',
                    'lophoc_chinhsachgia.hocPhiNiemYet',
                    'lophoc_chinhsachgia.trangThai',
                ])
                ->get();

            foreach ($policies as $policy) {
                $soBuoi = max(1, (int) ($policy->soBuoiCamKet ?: 1));
                $donGia = round(((float) $policy->hocPhiNiemYet) / $soBuoi);

                $hocPhiId = DB::table('hocphi')->insertGetId([
                    'khoaHocId' => $policy->khoaHocId,
                    'soBuoi' => $soBuoi,
                    'donGia' => $donGia,
                    'trangThai' => $policy->trangThai,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('lophoc')
                    ->where('lopHocId', $policy->lopHocId)
                    ->update(['hocPhiId' => $hocPhiId]);
            }

            $this->dropForeignKeyIfExists('lophoc', 'hocPhiId');

            Schema::table('lophoc', function (Blueprint $table) {
                $table->foreign('hocPhiId')
                    ->references('hocPhiId')
                    ->on('hocphi')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('hoadon') && Schema::hasColumn('hoadon', 'lopHocDotThuId')) {
            $this->dropForeignKeyIfExists('hoadon', 'lopHocDotThuId');

            Schema::table('hoadon', function (Blueprint $table) {
                $table->dropColumn('lopHocDotThuId');
            });
        }

        if (Schema::hasTable('dangkylophoc')) {
            $this->dropForeignKeyIfExists('dangkylophoc', 'lopHocChinhSachGiaId');

            Schema::table('dangkylophoc', function (Blueprint $table) {
                foreach ([
                    'lopHocChinhSachGiaId',
                    'loaiThuSnapshot',
                    'hocPhiNiemYetSnapshot',
                    'giamGiaSnapshot',
                    'hocPhiPhaiThuSnapshot',
                    'soBuoiCamKetSnapshot',
                    'ghiChuGiaSnapshot',
                ] as $column) {
                    if (Schema::hasColumn('dangkylophoc', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('lophoc_dotthu')) {
            Schema::drop('lophoc_dotthu');
        }

        if (Schema::hasTable('lophoc_chinhsachgia')) {
            Schema::drop('lophoc_chinhsachgia');
        }
    }

    private function migrateLegacyHocPhiData(): void
    {
        if (!Schema::hasTable('hocphi') || !Schema::hasTable('lophoc') || !Schema::hasTable('lophoc_chinhsachgia')) {
            return;
        }

        if (Schema::hasColumn('lophoc', 'hocPhiId')) {
            $legacyPolicies = DB::table('lophoc')
                ->join('hocphi', 'hocphi.hocPhiId', '=', 'lophoc.hocPhiId')
                ->leftJoin('lophoc_chinhsachgia', 'lophoc_chinhsachgia.lopHocId', '=', 'lophoc.lopHocId')
                ->select([
                    'lophoc.lopHocId',
                    'hocphi.soBuoi',
                    'hocphi.donGia',
                    'hocphi.trangThai',
                    'lophoc_chinhsachgia.lopHocChinhSachGiaId as existingPolicyId',
                ])
                ->whereNotNull('lophoc.hocPhiId')
                ->get();

            foreach ($legacyPolicies as $legacyPolicy) {
                if ($legacyPolicy->existingPolicyId) {
                    continue;
                }

                DB::table('lophoc_chinhsachgia')->insert([
                    'lopHocId' => $legacyPolicy->lopHocId,
                    'loaiThu' => 0,
                    'hocPhiNiemYet' => (float) $legacyPolicy->soBuoi * (float) $legacyPolicy->donGia,
                    'soBuoiCamKet' => $legacyPolicy->soBuoi,
                    'ghiChuChinhSach' => 'Du lieu duoc chuyen tu bang hocphi cu.',
                    'hieuLucTu' => null,
                    'hieuLucDen' => null,
                    'trangThai' => $legacyPolicy->trangThai ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('dangkylophoc')) {
            $registrations = DB::table('dangkylophoc')
                ->join('lophoc', 'lophoc.lopHocId', '=', 'dangkylophoc.lopHocId')
                ->leftJoin('lophoc_chinhsachgia', 'lophoc_chinhsachgia.lopHocId', '=', 'lophoc.lopHocId')
                ->select([
                    'dangkylophoc.dangKyLopHocId',
                    'lophoc_chinhsachgia.lopHocChinhSachGiaId',
                    'lophoc_chinhsachgia.loaiThu',
                    'lophoc_chinhsachgia.hocPhiNiemYet',
                    'lophoc_chinhsachgia.soBuoiCamKet',
                    'lophoc_chinhsachgia.ghiChuChinhSach',
                    'dangkylophoc.lopHocChinhSachGiaId as currentPolicyId',
                    'dangkylophoc.hocPhiNiemYetSnapshot',
                ])
                ->get();

            foreach ($registrations as $registration) {
                if (!$registration->lopHocChinhSachGiaId) {
                    continue;
                }

                DB::table('dangkylophoc')
                    ->where('dangKyLopHocId', $registration->dangKyLopHocId)
                    ->update([
                        'lopHocChinhSachGiaId' => $registration->currentPolicyId ?: $registration->lopHocChinhSachGiaId,
                        'loaiThuSnapshot' => $registration->loaiThu ?? 0,
                        'hocPhiNiemYetSnapshot' => $registration->hocPhiNiemYetSnapshot ?? $registration->hocPhiNiemYet,
                        'giamGiaSnapshot' => DB::raw('COALESCE(giamGiaSnapshot, 0)'),
                        'hocPhiPhaiThuSnapshot' => $registration->hocPhiNiemYetSnapshot ?? $registration->hocPhiNiemYet,
                        'soBuoiCamKetSnapshot' => $registration->soBuoiCamKet,
                        'ghiChuGiaSnapshot' => $registration->ghiChuChinhSach,
                    ]);
            }
        }
    }

    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        $database = DB::getDatabaseName();
        $foreignKeys = DB::select(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        foreach ($foreignKeys as $foreignKey) {
            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                $table,
                $foreignKey->CONSTRAINT_NAME
            ));
        }
    }
};
