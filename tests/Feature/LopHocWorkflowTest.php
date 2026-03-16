<?php

namespace Tests\Feature;

use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\NhanSu;
use App\Models\Auth\TaiKhoan;
use App\Models\Course\KhoaHoc;
use App\Models\Education\CaHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\PhongHoc;
use App\Services\Admin\KhoaHoc\LopHocService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LopHocWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createClassSchema();
    }

    public function test_store_rejects_teacher_schedule_conflict(): void
    {
        $course = $this->createCourse();
        $branch = $this->createBranch();
        $shift = $this->createShift('Ca tối', '18:00:00', '20:00:00');
        $teacher = $this->createTeacher($branch->coSoId, 'GV000001', 'Giáo viên A');
        $roomOne = $this->createRoom($branch->coSoId, 'P.101');
        $roomTwo = $this->createRoom($branch->coSoId, 'P.102');

        LopHoc::create([
            'slug' => 'lop-cu',
            'maLopHoc' => 'TA001',
            'khoaHocId' => $course->khoaHocId,
            'tenLopHoc' => 'Lớp cũ',
            'phongHocId' => $roomOne->phongHocId,
            'taiKhoanId' => $teacher->taiKhoanId,
            'ngayBatDau' => '2026-03-16',
            'soBuoiDuKien' => 12,
            'soHocVienToiDa' => 20,
            'coSoId' => $branch->coSoId,
            'caHocId' => $shift->caHocId,
            'lichHoc' => '2,4,6',
            'trangThai' => LopHoc::TRANG_THAI_SAP_MO,
        ]);

        $service = new LopHocService();
        $request = Request::create('/admin/lop-hoc', 'POST', [
            'tenLopHoc' => 'Lớp mới bị trùng giáo viên',
            'khoaHocId' => $course->khoaHocId,
            'coSoId' => $branch->coSoId,
            'caHocId' => $shift->caHocId,
            'taiKhoanId' => $teacher->taiKhoanId,
            'phongHocId' => $roomTwo->phongHocId,
            'ngayBatDau' => '2026-03-18',
            'soBuoiDuKien' => 10,
            'soHocVienToiDa' => 18,
            'lichHoc' => '4,6',
            'trangThai' => LopHoc::TRANG_THAI_SAP_MO,
        ]);

        try {
            $service->store($request);
            $this->fail('Expected teacher conflict validation to be thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('taiKhoanId', $e->errors());
            $this->assertStringContainsString('Giáo viên', $e->errors()['taiKhoanId'][0]);
            $this->assertStringContainsString('Lớp cũ', $e->errors()['taiKhoanId'][0]);
        }
    }

    public function test_store_rejects_room_schedule_conflict(): void
    {
        $course = $this->createCourse();
        $branch = $this->createBranch();
        $shift = $this->createShift('Ca sáng', '08:00:00', '10:00:00');
        $teacherOne = $this->createTeacher($branch->coSoId, 'GV000011', 'Giáo viên B');
        $teacherTwo = $this->createTeacher($branch->coSoId, 'GV000012', 'Giáo viên C');
        $room = $this->createRoom($branch->coSoId, 'P.201');

        LopHoc::create([
            'slug' => 'lop-co-phong-trung',
            'maLopHoc' => 'TA002',
            'khoaHocId' => $course->khoaHocId,
            'tenLopHoc' => 'Lớp dùng phòng 201',
            'phongHocId' => $room->phongHocId,
            'taiKhoanId' => $teacherOne->taiKhoanId,
            'ngayBatDau' => '2026-03-17',
            'soBuoiDuKien' => 8,
            'soHocVienToiDa' => 20,
            'coSoId' => $branch->coSoId,
            'caHocId' => $shift->caHocId,
            'lichHoc' => '3,5',
            'trangThai' => LopHoc::TRANG_THAI_SAP_MO,
        ]);

        $service = new LopHocService();
        $request = Request::create('/admin/lop-hoc', 'POST', [
            'tenLopHoc' => 'Lớp mới bị trùng phòng',
            'khoaHocId' => $course->khoaHocId,
            'coSoId' => $branch->coSoId,
            'caHocId' => $shift->caHocId,
            'taiKhoanId' => $teacherTwo->taiKhoanId,
            'phongHocId' => $room->phongHocId,
            'ngayBatDau' => '2026-03-19',
            'soBuoiDuKien' => 6,
            'soHocVienToiDa' => 16,
            'lichHoc' => '5',
            'trangThai' => LopHoc::TRANG_THAI_SAP_MO,
        ]);

        try {
            $service->store($request);
            $this->fail('Expected room conflict validation to be thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('phongHocId', $e->errors());
            $this->assertStringContainsString('Phòng học', $e->errors()['phongHocId'][0]);
            $this->assertStringContainsString('Lớp dùng phòng 201', $e->errors()['phongHocId'][0]);
        }
    }

    public function test_preview_conflicts_reports_teacher_conflict_in_realtime(): void
    {
        $course = $this->createCourse();
        $branch = $this->createBranch();
        $shift = $this->createShift('Ca chiều', '14:00:00', '16:00:00');
        $teacher = $this->createTeacher($branch->coSoId, 'GV000021', 'Giáo viên Preview');
        $room = $this->createRoom($branch->coSoId, 'P.301');

        LopHoc::create([
            'slug' => 'lop-preview-xung-dot',
            'maLopHoc' => 'TA003',
            'khoaHocId' => $course->khoaHocId,
            'tenLopHoc' => 'Lớp preview',
            'phongHocId' => $room->phongHocId,
            'taiKhoanId' => $teacher->taiKhoanId,
            'ngayBatDau' => '2026-03-16',
            'soBuoiDuKien' => 10,
            'soHocVienToiDa' => 15,
            'coSoId' => $branch->coSoId,
            'caHocId' => $shift->caHocId,
            'lichHoc' => '2,4',
            'trangThai' => LopHoc::TRANG_THAI_SAP_MO,
        ]);

        $service = new LopHocService();
        $response = $service->previewSchedulingConflicts(Request::create('/admin/lop-hoc/kiem-tra-xung-dot', 'GET', [
            'coSoId' => $branch->coSoId,
            'caHocId' => $shift->caHocId,
            'taiKhoanId' => $teacher->taiKhoanId,
            'phongHocId' => '',
            'ngayBatDau' => '2026-03-18',
            'soBuoiDuKien' => 8,
            'lichHoc' => '4',
        ]));

        $this->assertTrue($response['ready']);
        $this->assertFalse($response['ok']);
        $this->assertSame('error', $response['fieldStates']['taiKhoanId']['status']);
        $this->assertStringContainsString('Lớp preview', $response['fieldStates']['taiKhoanId']['message']);
    }

    private function createClassSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'lophoc_dotthu',
            'lophoc_phuphi',
            'lophoc_chinhsachgia',
            'dangKyLopHoc',
            'buoihoc',
            'lophoc',
            'nhansu',
            'hosonguoidung',
            'taikhoan',
            'phonghoc',
            'cahoc',
            'cosodaotao',
            'khoahoc',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('khoahoc', function (Blueprint $table) {
            $table->increments('khoaHocId');
            $table->string('maKhoaHoc')->nullable();
            $table->string('tenKhoaHoc');
            $table->tinyInteger('trangThai')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('cosodaotao', function (Blueprint $table) {
            $table->increments('coSoId');
            $table->string('tenCoSo');
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('cahoc', function (Blueprint $table) {
            $table->increments('caHocId');
            $table->string('tenCa');
            $table->time('gioBatDau');
            $table->time('gioKetThuc');
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('phonghoc', function (Blueprint $table) {
            $table->increments('phongHocId');
            $table->unsignedInteger('coSoId');
            $table->string('tenPhong');
            $table->unsignedInteger('sucChua')->default(0);
            $table->tinyInteger('trangThai')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('taikhoan', function (Blueprint $table) {
            $table->increments('taiKhoanId');
            $table->string('taiKhoan')->nullable();
            $table->string('email')->nullable();
            $table->integer('role')->default(TaiKhoan::ROLE_HOC_VIEN);
            $table->integer('trangThai')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('hosonguoidung', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->string('hoTen')->nullable();
            $table->timestamps();
        });

        Schema::create('nhansu', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->unsignedInteger('coSoId')->nullable();
            $table->string('loaiHopDong')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('lophoc', function (Blueprint $table) {
            $table->increments('lopHocId');
            $table->string('slug')->nullable();
            $table->string('maLopHoc')->nullable();
            $table->unsignedInteger('khoaHocId')->nullable();
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
            $table->tinyInteger('trangThai')->default(LopHoc::TRANG_THAI_SAP_MO);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('buoihoc', function (Blueprint $table) {
            $table->increments('buoiHocId');
            $table->unsignedInteger('lopHocId');
            $table->string('tenBuoiHoc')->nullable();
            $table->date('ngayHoc')->nullable();
            $table->unsignedInteger('caHocId')->nullable();
            $table->unsignedInteger('phongHocId')->nullable();
            $table->unsignedInteger('taiKhoanId')->nullable();
            $table->tinyInteger('trangThai')->default(0);
            $table->boolean('daDiemDanh')->default(false);
            $table->boolean('daHoanThanh')->default(false);
            $table->timestamps();
        });

        Schema::create('dangKyLopHoc', function (Blueprint $table) {
            $table->increments('dangKyLopHocId');
            $table->unsignedInteger('lopHocId');
            $table->unsignedInteger('taiKhoanId');
            $table->tinyInteger('trangThai')->default(DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN);
        });

        Schema::create('lophoc_chinhsachgia', function (Blueprint $table) {
            $table->increments('lopHocChinhSachGiaId');
            $table->unsignedInteger('lopHocId');
            $table->tinyInteger('loaiThu')->default(0);
            $table->decimal('hocPhiNiemYet', 15, 2)->default(0);
            $table->unsignedInteger('soBuoiCamKet')->nullable();
            $table->date('hanThanhToanHocPhi')->nullable();
            $table->text('ghiChuChinhSach')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('lophoc_dotthu', function (Blueprint $table) {
            $table->increments('lopHocDotThuId');
            $table->unsignedInteger('lopHocChinhSachGiaId');
            $table->string('tenDotThu');
            $table->unsignedInteger('thuTu')->default(1);
            $table->decimal('soTien', 15, 2)->default(0);
            $table->date('hanThanhToan')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('lophoc_phuphi', function (Blueprint $table) {
            $table->increments('lopHocPhuPhiId');
            $table->unsignedInteger('lopHocId');
            $table->string('tenKhoanThu');
            $table->string('nhomPhi')->default('khac');
            $table->decimal('soTien', 15, 2)->default(0);
            $table->date('hanThanhToanMau')->nullable();
            $table->tinyInteger('apDungMacDinh')->default(0);
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    private function createCourse(): KhoaHoc
    {
        return KhoaHoc::create([
            'maKhoaHoc' => 'TA-001',
            'tenKhoaHoc' => 'Tiếng Anh giao tiếp',
            'trangThai' => 1,
        ]);
    }

    private function createBranch(): CoSoDaoTao
    {
        return CoSoDaoTao::create([
            'tenCoSo' => 'Cơ sở Quận 1',
            'trangThai' => 1,
        ]);
    }

    private function createShift(string $name, string $start, string $end): CaHoc
    {
        return CaHoc::create([
            'tenCa' => $name,
            'gioBatDau' => $start,
            'gioKetThuc' => $end,
            'trangThai' => 1,
        ]);
    }

    private function createRoom(int $branchId, string $name): PhongHoc
    {
        return PhongHoc::create([
            'coSoId' => $branchId,
            'tenPhong' => $name,
            'sucChua' => 25,
            'trangThai' => 1,
        ]);
    }

    private function createTeacher(int $branchId, string $username, string $name): TaiKhoan
    {
        $teacher = TaiKhoan::create([
            'taiKhoan' => $username,
            'email' => strtolower($username) . '@example.com',
            'role' => TaiKhoan::ROLE_GIAO_VIEN,
            'trangThai' => 1,
        ]);

        HoSoNguoiDung::create([
            'taiKhoanId' => $teacher->taiKhoanId,
            'hoTen' => $name,
        ]);

        NhanSu::create([
            'taiKhoanId' => $teacher->taiKhoanId,
            'coSoId' => $branchId,
            'trangThai' => 1,
        ]);

        return $teacher;
    }
}
