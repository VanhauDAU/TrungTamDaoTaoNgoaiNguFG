<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\BaoCaoHocTap;
use App\Models\Education\BaoCaoHocTapDotDanhGia;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProgressReportWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        $this->createMinimalEvaluationSchema();
        $this->runProgressReportMigration();
    }

    public function test_teacher_submit_staff_approve_publish_and_student_can_view(): void
    {
        [$teacher, $staff, $student, $class, $registration] = $this->seedCoreRecords();

        $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->post(route('staff.evaluations.periods.store'), [
                'lopHocId' => $class['lopHocId'],
                'tenDot' => 'Đợt giữa khóa 04/2026',
                'tuNgay' => '2026-04-01',
                'denNgay' => '2026-04-20',
                'hanNop' => '2026-04-21',
                'hanDuyet' => '2026-04-22',
            ])
            ->assertRedirect(route('staff.evaluations.periods.index'));

        $report = BaoCaoHocTap::query()->with('tieuChis')->firstOrFail();

        $payload = ['criteria' => $this->buildCompleteCriteriaPayload($report)];

        $this->actingAs($teacher)
            ->withSession(['auth_portal' => 'teacher'])
            ->post(route('teacher.evaluations.reports.save', $report->baoCaoHocTapId), $payload)
            ->assertRedirect();

        $this->actingAs($teacher)
            ->withSession(['auth_portal' => 'teacher'])
            ->post(route('teacher.evaluations.reports.submit', $report->baoCaoHocTapId))
            ->assertRedirect();

        $report->refresh();
        $this->assertSame(BaoCaoHocTap::TRANG_THAI_SUBMITTED, $report->trangThai);

        $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->post(route('staff.evaluations.reports.approve', $report->baoCaoHocTapId))
            ->assertRedirect();

        $report->refresh();
        $this->assertSame(BaoCaoHocTap::TRANG_THAI_APPROVED, $report->trangThai);

        $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->post(route('staff.evaluations.reports.publish', $report->baoCaoHocTapId))
            ->assertRedirect();

        $report->refresh();
        $this->assertSame(BaoCaoHocTap::TRANG_THAI_PUBLISHED, $report->trangThai);

        $this->actingAs($student)
            ->withSession(['auth_portal' => 'student'])
            ->get(route('home.student.reports.show', $report->baoCaoHocTapId))
            ->assertOk()
            ->assertSee('Đợt giữa khóa 04/2026');
    }

    public function test_teacher_cannot_edit_report_after_submit(): void
    {
        [$teacher, $staff, $student, $class, $registration] = $this->seedCoreRecords();
        $report = $this->createAndSubmitReport($teacher, $staff, $class['lopHocId']);

        $response = $this->actingAs($teacher)
            ->withSession(['auth_portal' => 'teacher'])
            ->post(route('teacher.evaluations.reports.save', $report->baoCaoHocTapId), [
                'criteria' => [],
            ]);

        $response->assertStatus(422);
    }

    public function test_staff_cannot_publish_report_before_approval(): void
    {
        [$teacher, $staff, $student, $class, $registration] = $this->seedCoreRecords();
        $report = $this->createAndSubmitReport($teacher, $staff, $class['lopHocId']);

        $response = $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->post(route('staff.evaluations.reports.publish', $report->baoCaoHocTapId));

        $response->assertStatus(422);
    }

    public function test_student_cannot_view_unpublished_report(): void
    {
        [$teacher, $staff, $student, $class, $registration] = $this->seedCoreRecords();

        $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->post(route('staff.evaluations.periods.store'), [
                'lopHocId' => $class['lopHocId'],
                'tenDot' => 'Đợt cuối khóa 04/2026',
            ])
            ->assertRedirect();

        $report = BaoCaoHocTap::query()->firstOrFail();

        $this->actingAs($student)
            ->withSession(['auth_portal' => 'student'])
            ->get(route('home.student.reports.show', $report->baoCaoHocTapId))
            ->assertNotFound();
    }

    private function createAndSubmitReport(TaiKhoan $teacher, TaiKhoan $staff, int $classId): BaoCaoHocTap
    {
        $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->post(route('staff.evaluations.periods.store'), [
                'lopHocId' => $classId,
                'tenDot' => 'Đợt thao tác thử',
                'hanNop' => '2026-04-21',
            ])
            ->assertRedirect();

        $report = BaoCaoHocTap::query()->with('tieuChis')->firstOrFail();

        $this->actingAs($teacher)
            ->withSession(['auth_portal' => 'teacher'])
            ->post(route('teacher.evaluations.reports.save', $report->baoCaoHocTapId), [
                'criteria' => $this->buildCompleteCriteriaPayload($report),
            ])
            ->assertRedirect();

        $this->actingAs($teacher)
            ->withSession(['auth_portal' => 'teacher'])
            ->post(route('teacher.evaluations.reports.submit', $report->baoCaoHocTapId))
            ->assertRedirect();

        return $report->fresh(['tieuChis']);
    }

    private function buildCompleteCriteriaPayload(BaoCaoHocTap $report): array
    {
        return $report->tieuChis->mapWithKeys(function ($criterion) {
            $payload = [];

            if ($criterion->loaiDuLieu === 'rating') {
                $payload['rating'] = 'Tốt';
                $payload['comment'] = 'Học viên đáp ứng mục tiêu ở tiêu chí này.';
            } elseif (in_array($criterion->loaiDuLieu, ['number', 'ratio'], true)) {
                $payload['number'] = 1;
                $payload['comment'] = 'Số liệu đã được giáo viên cập nhật.';
            } elseif ($criterion->loaiDuLieu === 'text') {
                $payload['comment'] = 'Giáo viên đã hoàn thiện phần nhận xét bắt buộc.';
            }

            return [$criterion->baoCaoHocTapTieuChiId => $payload];
        })->all();
    }

    private function seedCoreRecords(): array
    {
        $teacher = $this->createAccount(TaiKhoan::ROLE_GIAO_VIEN, 'GV', 'Giáo viên A');
        $staff = $this->createAccount(TaiKhoan::ROLE_NHAN_VIEN, 'NV', 'Nhân viên B');
        $student = $this->createAccount(TaiKhoan::ROLE_HOC_VIEN, 'HV', 'Học viên C');

        $facility = [
            'coSoId' => 1,
            'tenCoSo' => 'Cơ sở Quận 1',
            'trangThai' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        \DB::table('cosodaotao')->insert($facility);

        \DB::table('khoahoc')->insert([
            'khoaHocId' => 1,
            'maKhoaHoc' => 'EN-001',
            'tenKhoaHoc' => 'Tiếng Anh Giao Tiếp',
            'slug' => 'tieng-anh-giao-tiep',
            'trangThai' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $class = [
            'lopHocId' => 1,
            'slug' => 'lop-a1',
            'maLopHoc' => '26EN001',
            'khoaHocId' => 1,
            'tenLopHoc' => 'Lớp A1 tối 246',
            'phongHocId' => null,
            'taiKhoanId' => $teacher->taiKhoanId,
            'ngayBatDau' => '2026-04-01',
            'ngayKetThuc' => '2026-06-30',
            'soBuoiDuKien' => 24,
            'soHocVienToiDa' => 20,
            'donGiaDay' => 0,
            'coSoId' => 1,
            'caHocId' => null,
            'lichHoc' => '2,4,6',
            'trangThai' => 4,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];

        \DB::table('lophoc')->insert($class);

        $registration = [
            'dangKyLopHocId' => 1,
            'taiKhoanId' => $student->taiKhoanId,
            'lopHocId' => 1,
            'ngayDangKy' => '2026-04-01',
            'trangThai' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        \DB::table('dangkylophoc')->insert($registration);

        \DB::table('buoihoc')->insert([
            'buoiHocId' => 1,
            'lopHocId' => 1,
            'tenBuoiHoc' => 'Buổi 1',
            'ngayHoc' => '2026-04-05',
            'caHocId' => null,
            'phongHocId' => null,
            'taiKhoanId' => $teacher->taiKhoanId,
            'ghiChu' => null,
            'daDiemDanh' => 1,
            'daHoanThanh' => 1,
            'trangThai' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('diemDanh')->insert([
            'diemDanhId' => 1,
            'buoiHocId' => 1,
            'taiKhoanId' => $student->taiKhoanId,
            'dangKyLopHocId' => 1,
            'trangThai' => 1,
            'nguoiDiemDanhId' => $teacher->taiKhoanId,
            'ghiChu' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$teacher, $staff, $student, $class, $registration];
    }

    private function createAccount(int $role, string $prefix, string $name): TaiKhoan
    {
        $account = TaiKhoan::query()->create([
            'taiKhoan' => $prefix . fake()->unique()->numerify('######'),
            'matKhau' => bcrypt('password'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role' => $role,
            'trangThai' => 1,
            'phaiDoiMatKhau' => 0,
        ]);

        \DB::table('hosonguoidung')->insert([
            'taiKhoanId' => $account->taiKhoanId,
            'hoTen' => $name,
            'trinhDoHienTai' => 'A1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $account;
    }

    private function runProgressReportMigration(): void
    {
        $migration = require base_path('database/migrations/2026_04_22_090000_create_bao_cao_hoc_tap_module_tables.php');
        $migration->up();
    }

    private function createMinimalEvaluationSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'bao_cao_hoc_tap_lich_su',
            'bao_cao_hoc_tap_tieu_chi',
            'bao_cao_hoc_tap',
            'bao_cao_hoc_tap_dot_danh_gia',
            'bao_cao_hoc_tap_mau_tieu_chi',
            'bao_cao_hoc_tap_mau',
            'thongbaonguoidung',
            'thongbao',
            'diemDanh',
            'buoihoc',
            'dangkylophoc',
            'lophoc',
            'khoahoc',
            'cosodaotao',
            'hosonguoidung',
            'taikhoan',
            'password_reset_tokens',
            'sessions',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('taikhoan', function (Blueprint $table) {
            $table->integer('taiKhoanId', true);
            $table->string('taiKhoan')->unique();
            $table->string('matKhau');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('role')->default(TaiKhoan::ROLE_HOC_VIEN);
            $table->unsignedInteger('nhomQuyenId')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->tinyInteger('phaiDoiMatKhau')->default(0);
            $table->string('auth_provider')->nullable();
            $table->string('google_id')->nullable();
            $table->string('google_avatar')->nullable();
            $table->rememberToken();
            $table->timestamp('lastLogin')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('hosonguoidung', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->string('hoTen')->nullable();
            $table->string('anhDaiDien')->nullable();
            $table->string('trinhDoHienTai')->nullable();
            $table->timestamps();
        });

        Schema::create('cosodaotao', function (Blueprint $table) {
            $table->integer('coSoId', true);
            $table->string('tenCoSo');
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('khoahoc', function (Blueprint $table) {
            $table->integer('khoaHocId', true);
            $table->string('maKhoaHoc')->nullable();
            $table->string('tenKhoaHoc');
            $table->string('slug')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('lophoc', function (Blueprint $table) {
            $table->integer('lopHocId', true);
            $table->string('slug')->nullable();
            $table->string('maLopHoc')->nullable();
            $table->integer('khoaHocId');
            $table->string('tenLopHoc');
            $table->integer('phongHocId')->nullable();
            $table->integer('taiKhoanId')->nullable();
            $table->date('ngayBatDau')->nullable();
            $table->date('ngayKetThuc')->nullable();
            $table->integer('soBuoiDuKien')->nullable();
            $table->integer('soHocVienToiDa')->nullable();
            $table->decimal('donGiaDay', 10, 2)->default(0);
            $table->integer('coSoId')->nullable();
            $table->integer('caHocId')->nullable();
            $table->string('lichHoc')->nullable();
            $table->tinyInteger('trangThai')->default(4);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('dangkylophoc', function (Blueprint $table) {
            $table->integer('dangKyLopHocId', true);
            $table->integer('taiKhoanId');
            $table->integer('lopHocId');
            $table->date('ngayDangKy')->nullable();
            $table->tinyInteger('trangThai')->default(2);
            $table->timestamps();
        });

        Schema::create('buoihoc', function (Blueprint $table) {
            $table->integer('buoiHocId', true);
            $table->integer('lopHocId');
            $table->string('tenBuoiHoc')->nullable();
            $table->date('ngayHoc')->nullable();
            $table->integer('caHocId')->nullable();
            $table->integer('phongHocId')->nullable();
            $table->integer('taiKhoanId')->nullable();
            $table->text('ghiChu')->nullable();
            $table->tinyInteger('daDiemDanh')->default(0);
            $table->tinyInteger('daHoanThanh')->default(0);
            $table->tinyInteger('trangThai')->default(2);
            $table->timestamps();
        });

        Schema::create('diemDanh', function (Blueprint $table) {
            $table->integer('diemDanhId', true);
            $table->integer('buoiHocId');
            $table->integer('taiKhoanId');
            $table->integer('dangKyLopHocId')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->integer('nguoiDiemDanhId')->nullable();
            $table->text('ghiChu')->nullable();
            $table->timestamps();
        });

        Schema::create('thongbao', function (Blueprint $table) {
            $table->integer('thongBaoId', true);
            $table->string('tieuDe');
            $table->text('noiDung')->nullable();
            $table->integer('nguoiGuiId')->nullable();
            $table->integer('loaiThongBao')->nullable();
            $table->integer('doiTuongGui')->nullable();
            $table->integer('doiTuongId')->nullable();
            $table->timestamp('ngayGui')->nullable();
            $table->tinyInteger('trangThai')->nullable();
            $table->integer('loaiGui')->nullable();
            $table->integer('uuTien')->nullable();
            $table->tinyInteger('ghim')->default(0);
            $table->integer('sendTrangThai')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('hinhAnh')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('thongbaonguoidung', function (Blueprint $table) {
            $table->integer('thongBaoNguoiDungId', true);
            $table->integer('thongBaoId');
            $table->integer('taiKhoanId');
            $table->tinyInteger('daDoc')->default(0);
            $table->timestamp('ngayDoc')->nullable();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }
}
