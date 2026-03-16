<?php

namespace Tests\Feature;

use App\Exceptions\MaintenanceConflictException;
use App\Models\Education\BuoiHoc;
use App\Models\Education\LopHoc;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\CoSoNhatKy;
use App\Models\Facility\PhongHoc;
use App\Services\Admin\CoSo\CoSoService;
use App\Services\Admin\CoSo\PhongHocService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoSoOperationsPhaseOneTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    public function test_toggle_status_requires_confirmation_when_room_has_upcoming_sessions(): void
    {
        $branch = CoSoDaoTao::create([
            'maCoSo' => 'CS01',
            'tenCoSo' => 'Cơ sở trung tâm',
            'diaChi' => '123 Nguyễn Văn Cừ',
            'trangThai' => 1,
        ]);

        $room = PhongHoc::create([
            'coSoId' => $branch->coSoId,
            'tenPhong' => 'P.201',
            'trangThai' => PhongHoc::TRANG_THAI_SAN_SANG,
        ]);

        $this->createCaHoc(1, 'Ca chiều', '14:00:00', '16:00:00');
        $class = $this->createLopHoc($branch->coSoId, $room->phongHocId, 1, 'TA101', 'Lớp tiếng Anh');

        BuoiHoc::create([
            'lopHocId' => $class->lopHocId,
            'tenBuoiHoc' => 'Buổi 1',
            'ngayHoc' => Carbon::today()->toDateString(),
            'caHocId' => 1,
            'phongHocId' => $room->phongHocId,
            'trangThai' => BuoiHoc::TRANG_THAI_SAP_DIEN_RA,
            'daDiemDanh' => 0,
            'daHoanThanh' => 0,
        ]);

        $service = new PhongHocService();

        $this->expectException(MaintenanceConflictException::class);

        $service->toggleStatus(Request::create('/admin/phong-hoc/1/toggle-status', 'PATCH'), $room->phongHocId);
    }

    public function test_operational_snapshot_returns_summary_schedule_and_audit_logs(): void
    {
        $branch = CoSoDaoTao::create([
            'maCoSo' => 'CS02',
            'tenCoSo' => 'Cơ sở phía Đông',
            'diaChi' => '456 Điện Biên Phủ',
            'trangThai' => 1,
        ]);

        $roomOne = PhongHoc::create([
            'coSoId' => $branch->coSoId,
            'tenPhong' => 'P.101',
            'trangThai' => PhongHoc::TRANG_THAI_SAN_SANG,
        ]);
        $roomTwo = PhongHoc::create([
            'coSoId' => $branch->coSoId,
            'tenPhong' => 'P.102',
            'trangThai' => PhongHoc::TRANG_THAI_SAN_SANG,
        ]);
        PhongHoc::create([
            'coSoId' => $branch->coSoId,
            'tenPhong' => 'P.103',
            'trangThai' => PhongHoc::TRANG_THAI_SAN_SANG,
        ]);

        $this->createTaiKhoan(1, 'GV000001', 'Giáo viên Snapshot');
        $this->createCaHoc(1, 'Ca sáng', '08:00:00', '10:00:00');
        $this->createCaHoc(2, 'Ca tối', '18:00:00', '20:00:00');

        $classOne = $this->createLopHoc($branch->coSoId, $roomOne->phongHocId, 1, 'TA201', 'Lớp A');
        $classTwo = $this->createLopHoc($branch->coSoId, $roomTwo->phongHocId, 2, 'TA202', 'Lớp B');

        BuoiHoc::create([
            'lopHocId' => $classOne->lopHocId,
            'tenBuoiHoc' => 'Buổi live',
            'ngayHoc' => Carbon::today()->toDateString(),
            'caHocId' => 1,
            'phongHocId' => $roomOne->phongHocId,
            'taiKhoanId' => 1,
            'trangThai' => BuoiHoc::TRANG_THAI_DANG_DIEN_RA,
            'daDiemDanh' => 1,
            'daHoanThanh' => 0,
        ]);
        BuoiHoc::create([
            'lopHocId' => $classTwo->lopHocId,
            'tenBuoiHoc' => 'Buổi upcoming',
            'ngayHoc' => Carbon::today()->toDateString(),
            'caHocId' => 2,
            'phongHocId' => $roomTwo->phongHocId,
            'taiKhoanId' => 1,
            'trangThai' => BuoiHoc::TRANG_THAI_SAP_DIEN_RA,
            'daDiemDanh' => 0,
            'daHoanThanh' => 0,
        ]);

        CoSoNhatKy::create([
            'coSoId' => $branch->coSoId,
            'phongHocId' => $roomOne->phongHocId,
            'taiKhoanId' => 1,
            'hanhDong' => 'phong_hoc.created',
            'moTa' => 'Đã tạo phòng P.101.',
            'duLieu' => ['test' => true],
        ]);

        $service = new CoSoService();
        $snapshot = $service->getOperationalSnapshot($branch->coSoId);

        $this->assertSame(3, $snapshot['summary']['totalRooms']);
        $this->assertSame(3, $snapshot['summary']['readyRooms']);
        $this->assertSame(1, $snapshot['summary']['liveRooms']);
        $this->assertSame(2, $snapshot['summary']['sessionsToday']);
        $this->assertSame(1, $snapshot['summary']['upcomingSessions']);
        $this->assertSame(1, $snapshot['summary']['liveSessions']);
        $this->assertCount(2, $snapshot['schedule']);
        $this->assertSame('P.101', $snapshot['schedule'][0]['tenPhong']);
        $this->assertSame(['P.101', 'P.102'], array_column($snapshot['schedule'], 'tenPhong'));
        $this->assertSame('Đã tạo phòng P.101.', $snapshot['auditLogs'][0]['message']);
    }

    private function createSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'coso_nhatky',
            'phonghoc_baotri',
            'phonghoc_taisan',
            'buoihoc',
            'lophoc',
            'hosonguoidung',
            'taikhoan',
            'phonghoc',
            'cahoc',
            'cosodaotao',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('cosodaotao', function (Blueprint $table) {
            $table->increments('coSoId');
            $table->string('maCoSo')->nullable();
            $table->string('slug')->nullable();
            $table->string('tenCoSo');
            $table->string('diaChi')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('phonghoc', function (Blueprint $table) {
            $table->increments('phongHocId');
            $table->unsignedInteger('coSoId');
            $table->string('tenPhong');
            $table->unsignedInteger('sucChua')->nullable();
            $table->string('trangThietBi')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->string('ghiChuBaoTri')->nullable();
            $table->dateTime('ngayBaoTri')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('phonghoc_taisan', function (Blueprint $table) {
            $table->increments('phongHocTaiSanId');
            $table->unsignedInteger('phongHocId');
            $table->string('maTaiSan')->nullable();
            $table->string('tenTaiSan');
            $table->string('loaiTaiSan')->nullable();
            $table->unsignedInteger('soLuong')->default(1);
            $table->tinyInteger('tinhTrang')->default(1);
            $table->string('ghiChu')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('phonghoc_baotri', function (Blueprint $table) {
            $table->increments('phongHocBaoTriId');
            $table->unsignedInteger('phongHocId');
            $table->unsignedInteger('coSoId');
            $table->string('maPhieu')->unique();
            $table->string('tieuDe');
            $table->text('moTa')->nullable();
            $table->tinyInteger('mucDoUuTien')->default(1);
            $table->tinyInteger('trangThai')->default(0);
            $table->unsignedInteger('createdById')->nullable();
            $table->unsignedInteger('assignedToId')->nullable();
            $table->dateTime('ngayYeuCau');
            $table->dateTime('ngayBatDau')->nullable();
            $table->dateTime('ngayHoanTat')->nullable();
            $table->text('ketQuaXuLy')->nullable();
            $table->timestamps();
        });

        Schema::create('taikhoan', function (Blueprint $table) {
            $table->increments('taiKhoanId');
            $table->string('taiKhoan')->nullable();
            $table->string('email')->nullable();
            $table->integer('role')->default(1);
            $table->integer('trangThai')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hosonguoidung', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->string('hoTen')->nullable();
            $table->timestamps();
        });

        Schema::create('cahoc', function (Blueprint $table) {
            $table->increments('caHocId');
            $table->string('tenCa');
            $table->time('gioBatDau');
            $table->time('gioKetThuc');
            $table->timestamps();
        });

        Schema::create('lophoc', function (Blueprint $table) {
            $table->increments('lopHocId');
            $table->string('slug')->nullable();
            $table->string('maLopHoc')->nullable();
            $table->string('tenLopHoc')->nullable();
            $table->unsignedInteger('phongHocId')->nullable();
            $table->unsignedInteger('taiKhoanId')->nullable();
            $table->date('ngayBatDau')->nullable();
            $table->date('ngayKetThuc')->nullable();
            $table->unsignedInteger('soBuoiDuKien')->nullable();
            $table->unsignedInteger('soHocVienToiDa')->nullable();
            $table->unsignedInteger('coSoId')->nullable();
            $table->unsignedInteger('caHocId')->nullable();
            $table->string('lichHoc')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('buoihoc', function (Blueprint $table) {
            $table->increments('buoiHocId');
            $table->unsignedInteger('lopHocId');
            $table->string('tenBuoiHoc')->nullable();
            $table->date('ngayHoc');
            $table->unsignedInteger('caHocId');
            $table->unsignedInteger('phongHocId');
            $table->unsignedInteger('taiKhoanId')->nullable();
            $table->text('ghiChu')->nullable();
            $table->boolean('daDiemDanh')->default(false);
            $table->boolean('daHoanThanh')->default(false);
            $table->tinyInteger('trangThai')->default(0);
            $table->timestamps();
        });

        Schema::create('coso_nhatky', function (Blueprint $table) {
            $table->increments('coSoNhatKyId');
            $table->unsignedInteger('coSoId');
            $table->unsignedInteger('phongHocId')->nullable();
            $table->unsignedInteger('taiKhoanId')->nullable();
            $table->string('hanhDong');
            $table->string('moTa');
            $table->json('duLieu')->nullable();
            $table->timestamps();
        });
    }

    private function createCaHoc(int $id, string $tenCa, string $gioBatDau, string $gioKetThuc): void
    {
        \DB::table('cahoc')->insert([
            'caHocId' => $id,
            'tenCa' => $tenCa,
            'gioBatDau' => $gioBatDau,
            'gioKetThuc' => $gioKetThuc,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTaiKhoan(int $id, string $taiKhoan, string $hoTen): void
    {
        \DB::table('taikhoan')->insert([
            'taiKhoanId' => $id,
            'taiKhoan' => $taiKhoan,
            'role' => 1,
            'trangThai' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('hosonguoidung')->insert([
            'taiKhoanId' => $id,
            'hoTen' => $hoTen,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createLopHoc(int $coSoId, int $phongHocId, int $caHocId, string $maLopHoc, string $tenLopHoc): LopHoc
    {
        return LopHoc::create([
            'maLopHoc' => $maLopHoc,
            'tenLopHoc' => $tenLopHoc,
            'phongHocId' => $phongHocId,
            'coSoId' => $coSoId,
            'caHocId' => $caHocId,
            'trangThai' => LopHoc::TRANG_THAI_DANG_HOC,
        ]);
    }
}
