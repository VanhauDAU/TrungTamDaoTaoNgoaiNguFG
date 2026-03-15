<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createSupplementalFeeTables();
        $this->addTuitionColumns();
        $this->addInvoiceColumns();
        $this->backfillTuitionDueDates();
        $this->backfillLegacyOptionalInstallmentsToSupplementalFees();
        $this->dropLegacyColumns();
    }

    public function down(): void
    {
        if (Schema::hasTable('lophoc_chinhsachgia')) {
            Schema::table('lophoc_chinhsachgia', function (Blueprint $table) {
                if (!Schema::hasColumn('lophoc_chinhsachgia', 'hieuLucTu')) {
                    $table->dateTime('hieuLucTu')->nullable()->after('ghiChuChinhSach');
                }
                if (!Schema::hasColumn('lophoc_chinhsachgia', 'hieuLucDen')) {
                    $table->dateTime('hieuLucDen')->nullable()->after('hieuLucTu');
                }
                if (Schema::hasColumn('lophoc_chinhsachgia', 'hanThanhToanHocPhi')) {
                    $table->dropColumn('hanThanhToanHocPhi');
                }
            });
        }

        if (Schema::hasTable('lophoc_dotthu')) {
            Schema::table('lophoc_dotthu', function (Blueprint $table) {
                if (!Schema::hasColumn('lophoc_dotthu', 'batBuoc')) {
                    $table->tinyInteger('batBuoc')->default(1)->after('hanThanhToan');
                }
            });
        }

        if (Schema::hasTable('hoadon')) {
            $this->dropForeignKeyIfExists('hoadon', 'dangKyLopHocPhuPhiId');

            Schema::table('hoadon', function (Blueprint $table) {
                $drops = [];
                foreach (['nguonThu', 'dangKyLopHocPhuPhiId'] as $column) {
                    if (Schema::hasColumn('hoadon', $column)) {
                        $drops[] = $column;
                    }
                }
                if (!empty($drops)) {
                    $table->dropColumn($drops);
                }
            });
        }

        if (Schema::hasTable('dangkylophoc_phuphi')) {
            Schema::drop('dangkylophoc_phuphi');
        }

        if (Schema::hasTable('lophoc_phuphi')) {
            Schema::drop('lophoc_phuphi');
        }
    }

    private function createSupplementalFeeTables(): void
    {
        if (!Schema::hasTable('lophoc_phuphi')) {
            Schema::create('lophoc_phuphi', function (Blueprint $table) {
                $table->increments('lopHocPhuPhiId');
                $table->integer('lopHocId');
                $table->string('tenKhoanThu', 255);
                $table->string('nhomPhi', 50)->default('khac');
                $table->decimal('soTien', 15, 2)->default(0);
                $table->date('hanThanhToanMau')->nullable();
                $table->tinyInteger('apDungMacDinh')->default(1);
                $table->tinyInteger('trangThai')->default(1);
                $table->timestamps();
            });
        }

        $this->dropForeignKeyIfExists('lophoc_phuphi', 'lopHocId');

        Schema::table('lophoc_phuphi', function (Blueprint $table) {
            $table->foreign('lopHocId', 'fk_lophoc_phuphi_lophoc')
                ->references('lopHocId')
                ->on('lophoc')
                ->cascadeOnDelete();
        });

        if (!Schema::hasTable('dangkylophoc_phuphi')) {
            Schema::create('dangkylophoc_phuphi', function (Blueprint $table) {
                $table->increments('dangKyLopHocPhuPhiId');
                // Bang dangkylophoc hien tai dung INT co dau cho dangKyLopHocId, nen can khop kieu de FK tao duoc.
                $table->integer('dangKyLopHocId');
                $table->unsignedInteger('lopHocPhuPhiId')->nullable();
                $table->string('tenKhoanThuSnapshot', 255);
                $table->string('nhomPhiSnapshot', 50)->default('khac');
                $table->decimal('soTienSnapshot', 15, 2)->default(0);
                $table->date('hanThanhToan')->nullable();
                $table->tinyInteger('trangThai')->default(1);
                $table->dateTime('ngayApDung')->nullable();
                $table->timestamps();
            });
        }

        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('dangkylophoc_phuphi', 'dangKyLopHocId')) {
            DB::statement('ALTER TABLE `dangkylophoc_phuphi` MODIFY `dangKyLopHocId` INT NOT NULL');
        }

        $this->dropForeignKeyIfExists('dangkylophoc_phuphi', 'dangKyLopHocId');
        $this->dropForeignKeyIfExists('dangkylophoc_phuphi', 'lopHocPhuPhiId');

        Schema::table('dangkylophoc_phuphi', function (Blueprint $table) {
            $table->foreign('dangKyLopHocId', 'fk_dkphuphi_dangky')
                ->references('dangKyLopHocId')
                ->on('dangkylophoc')
                ->cascadeOnDelete();

            $table->foreign('lopHocPhuPhiId', 'fk_dkphuphi_lophocphuphi')
                ->references('lopHocPhuPhiId')
                ->on('lophoc_phuphi')
                ->nullOnDelete();
        });
    }

    private function addTuitionColumns(): void
    {
        if (Schema::hasTable('lophoc_chinhsachgia') && !Schema::hasColumn('lophoc_chinhsachgia', 'hanThanhToanHocPhi')) {
            Schema::table('lophoc_chinhsachgia', function (Blueprint $table) {
                $table->date('hanThanhToanHocPhi')->nullable()->after('soBuoiCamKet');
            });
        }
    }

    private function addInvoiceColumns(): void
    {
        if (!Schema::hasTable('hoadon')) {
            return;
        }

        Schema::table('hoadon', function (Blueprint $table) {
            if (!Schema::hasColumn('hoadon', 'nguonThu')) {
                $table->string('nguonThu', 20)->default('hoc_phi')->after('lopHocDotThuId');
            }
            if (!Schema::hasColumn('hoadon', 'dangKyLopHocPhuPhiId')) {
                $table->unsignedInteger('dangKyLopHocPhuPhiId')->nullable()->after('dangKyLopHocId');
            }
        });

        $this->dropForeignKeyIfExists('hoadon', 'dangKyLopHocPhuPhiId');

        Schema::table('hoadon', function (Blueprint $table) {
            $table->foreign('dangKyLopHocPhuPhiId', 'fk_hoadon_dkphuphi')
                ->references('dangKyLopHocPhuPhiId')
                ->on('dangkylophoc_phuphi')
                ->nullOnDelete();
        });
    }

    private function backfillTuitionDueDates(): void
    {
        if (!Schema::hasTable('lophoc_chinhsachgia') || !Schema::hasColumn('lophoc_chinhsachgia', 'hanThanhToanHocPhi')) {
            return;
        }

        $policies = DB::table('lophoc_chinhsachgia')
            ->join('lophoc', 'lophoc.lopHocId', '=', 'lophoc_chinhsachgia.lopHocId')
            ->select([
                'lophoc_chinhsachgia.lopHocChinhSachGiaId',
                'lophoc_chinhsachgia.loaiThu',
                'lophoc_chinhsachgia.hanThanhToanHocPhi',
                'lophoc.ngayBatDau',
            ])
            ->get();

        foreach ($policies as $policy) {
            if ((int) $policy->loaiThu !== 0 || !empty($policy->hanThanhToanHocPhi)) {
                continue;
            }

            $fallbackDate = $policy->ngayBatDau
                ? Carbon::parse($policy->ngayBatDau)->toDateString()
                : Carbon::today()->toDateString();

            DB::table('lophoc_chinhsachgia')
                ->where('lopHocChinhSachGiaId', $policy->lopHocChinhSachGiaId)
                ->update(['hanThanhToanHocPhi' => $fallbackDate]);
        }
    }

    private function backfillLegacyOptionalInstallmentsToSupplementalFees(): void
    {
        if (
            !Schema::hasTable('lophoc_dotthu')
            || !Schema::hasColumn('lophoc_dotthu', 'batBuoc')
            || !Schema::hasTable('lophoc_phuphi')
        ) {
            return;
        }

        $legacyRows = DB::table('lophoc_dotthu')
            ->join('lophoc_chinhsachgia', 'lophoc_chinhsachgia.lopHocChinhSachGiaId', '=', 'lophoc_dotthu.lopHocChinhSachGiaId')
            ->select([
                'lophoc_dotthu.lopHocDotThuId',
                'lophoc_chinhsachgia.lopHocId',
                'lophoc_dotthu.tenDotThu',
                'lophoc_dotthu.soTien',
                'lophoc_dotthu.hanThanhToan',
                'lophoc_dotthu.trangThai',
            ])
            ->where('lophoc_dotthu.batBuoc', 0)
            ->get();

        foreach ($legacyRows as $legacyRow) {
            $exists = DB::table('lophoc_phuphi')
                ->where('lopHocId', $legacyRow->lopHocId)
                ->where('tenKhoanThu', $legacyRow->tenDotThu)
                ->where('soTien', $legacyRow->soTien)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('lophoc_phuphi')->insert([
                'lopHocId' => $legacyRow->lopHocId,
                'tenKhoanThu' => $legacyRow->tenDotThu,
                'nhomPhi' => 'khac',
                'soTien' => $legacyRow->soTien,
                'hanThanhToanMau' => $legacyRow->hanThanhToan,
                'apDungMacDinh' => 1,
                'trangThai' => $legacyRow->trangThai ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function dropLegacyColumns(): void
    {
        if (Schema::hasTable('lophoc_chinhsachgia')) {
            Schema::table('lophoc_chinhsachgia', function (Blueprint $table) {
                $drops = [];
                foreach (['hieuLucTu', 'hieuLucDen'] as $column) {
                    if (Schema::hasColumn('lophoc_chinhsachgia', $column)) {
                        $drops[] = $column;
                    }
                }
                if (!empty($drops)) {
                    $table->dropColumn($drops);
                }
            });
        }

        if (Schema::hasTable('lophoc_dotthu') && Schema::hasColumn('lophoc_dotthu', 'batBuoc')) {
            Schema::table('lophoc_dotthu', function (Blueprint $table) {
                $table->dropColumn('batBuoc');
            });
        }
    }

    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
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
