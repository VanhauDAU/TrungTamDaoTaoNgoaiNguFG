<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use App\Models\Education\LopHocTaiLieu;
use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 4 test case chính cho module LopHocTaiLieu:
 * 1. Giáo viên không truy cập tài liệu lớp của người khác.
 * 2. Học viên đủ điều kiện tải được file.
 * 3. Học viên Chờ thanh toán bị chặn (403).
 * 4. File private không lộ URL public (disk=local, không truy cập qua web).
 */
class LopHocTaiLieuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        $this->buildSchema();
    }

    /* ── Case 1: Giáo viên không truy cập tài liệu lớp người khác ──────────── */

    public function test_teacher_cannot_access_materials_of_another_teachers_class(): void
    {
        Storage::fake('local');

        [$teacherA, $lopHocA] = $this->createTeacherWithClass('GVA001', 'gva@example.com', 'lop-a');
        [$teacherB]           = $this->createTeacherWithClass('GVB001', 'gvb@example.com', 'lop-b');

        $taiLieu = $this->createTaiLieu($lopHocA);

        // Teacher B cố truy cập danh sách lớp của A → 404
        $this->actingAs($teacherB)
            ->get(route('teacher.classes.materials.index', $lopHocA->slug))
            ->assertStatus(404);

        // Teacher B cố download file của A → 404
        $this->actingAs($teacherB)
            ->get(route('teacher.classes.materials.download', [$lopHocA->slug, $taiLieu->lopHocTaiLieuId]))
            ->assertStatus(404);

        // Teacher A được phép xem danh sách lớp của mình → 200
        $this->actingAs($teacherA)
            ->get(route('teacher.classes.materials.index', $lopHocA->slug))
            ->assertOk();
    }

    /* ── Case 2: Học viên đủ điều kiện tải được ────────────────────────────── */

    public function test_eligible_student_can_download_material(): void
    {
        Storage::fake('local');

        [$teacher, $lopHoc] = $this->createTeacherWithClass('GVC001', 'gvc@example.com', 'lop-c');
        $student = $this->createStudent('HV001', 'hv1@example.com');

        // Đăng ký với trạng thái DANG_HOC (đủ điều kiện)
        $this->createDangKy($student, $lopHoc, DangKyLopHoc::TRANG_THAI_DANG_HOC);

        $fakeFile = UploadedFile::fake()->create('bai-giang.pdf', 50, 'application/pdf');
        Storage::disk('local')->put('lop-hoc/' . $lopHoc->lopHocId . '/bai-giang.pdf', $fakeFile->get());

        $taiLieu = LopHocTaiLieu::create([
            'lopHocId'    => $lopHoc->lopHocId,
            'tieuDe'      => 'Bài giảng 1',
            'nhomTaiLieu' => LopHocTaiLieu::NHOM_TAI_LIEU,
            'disk'        => 'local',
            'duongDan'    => 'lop-hoc/' . $lopHoc->lopHocId . '/bai-giang.pdf',
            'tenGoc'      => 'bai-giang.pdf',
            'mime'        => 'application/pdf',
            'kichThuoc'   => 50 * 1024,
            'trangThai'   => LopHocTaiLieu::TRANG_THAI_ACTIVE,
        ]);

        $this->actingAs($student)
            ->get(route('home.student.classes.materials.download', [$lopHoc->lopHocId, $taiLieu->lopHocTaiLieuId]))
            ->assertOk();
    }

    /* ── Case 3: Học viên Chờ thanh toán bị chặn ────────────────────────────── */

    public function test_student_pending_payment_is_blocked_from_downloading(): void
    {
        Storage::fake('local');

        [$teacher, $lopHoc] = $this->createTeacherWithClass('GVD001', 'gvd@example.com', 'lop-d');
        $student = $this->createStudent('HV002', 'hv2@example.com');

        // CHỜ THANH TOÁN → bị chặn
        $this->createDangKy($student, $lopHoc, DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN);

        $taiLieu = LopHocTaiLieu::create([
            'lopHocId'    => $lopHoc->lopHocId,
            'tieuDe'      => 'Tài liệu bí mật',
            'nhomTaiLieu' => LopHocTaiLieu::NHOM_TAI_LIEU,
            'disk'        => 'local',
            'duongDan'    => 'lop-hoc/' . $lopHoc->lopHocId . '/secret.pdf',
            'tenGoc'      => 'secret.pdf',
            'kichThuoc'   => 1024,
            'trangThai'   => LopHocTaiLieu::TRANG_THAI_ACTIVE,
        ]);

        $this->actingAs($student)
            ->get(route('home.student.classes.materials.download', [$lopHoc->lopHocId, $taiLieu->lopHocTaiLieuId]))
            ->assertStatus(403);
    }

    /* ── Case 4: File private không lộ URL public ───────────────────────────── */

    public function test_private_file_is_not_accessible_via_public_url(): void
    {
        // disk=local lưu trong storage/app/private, không route qua web
        // Kiểm tra: disk 'local' không phải 'public', nên URL public trả 404
        Storage::fake('local');

        [$teacher, $lopHoc] = $this->createTeacherWithClass('GVE001', 'gve@example.com', 'lop-e');

        $fakeFile = UploadedFile::fake()->create('private.pdf', 20, 'application/pdf');
        $path = Storage::disk('local')->put('lop-hoc/' . $lopHoc->lopHocId, $fakeFile);

        // File phải tồn tại trên disk local
        Storage::disk('local')->assertExists($path);

        // Nhưng không thể truy cập qua URL storage/ public
        // (disk public sẽ không có file này)
        Storage::disk('public')->assertMissing($path);

        // Request thẳng web URL /storage/... phải 404
        $this->get('/storage/' . $path)->assertStatus(404);
    }

    /* ── Schema bootstrap ───────────────────────────────────────────────────── */

    private function buildSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'lophoc_tai_lieu',
            'dangKyLopHoc',
            'lophoc',
            'hosonguoidung',
            'taikhoan',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('taikhoan', function (Blueprint $table) {
            $table->increments('taiKhoanId');
            $table->string('taiKhoan')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('matKhau')->nullable();
            $table->integer('role')->default(TaiKhoan::ROLE_HOC_VIEN);
            $table->integer('trangThai')->default(1);
            $table->integer('phaiDoiMatKhau')->default(0);
            $table->string('auth_provider')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('hosonguoidung', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->string('hoTen')->nullable();
            $table->string('soDienThoai')->nullable();
            $table->timestamps();
        });

        Schema::create('lophoc', function (Blueprint $table) {
            $table->increments('lopHocId');
            $table->string('slug')->unique();
            $table->string('maLopHoc')->nullable();
            $table->string('tenLopHoc');
            $table->unsignedInteger('taiKhoanId')->nullable(); // giáo viên
            $table->unsignedInteger('khoaHocId')->nullable();
            $table->integer('trangThai')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('dangKyLopHoc', function (Blueprint $table) {
            $table->increments('dangKyLopHocId');
            $table->unsignedInteger('taiKhoanId');
            $table->unsignedInteger('lopHocId');
            $table->integer('trangThai')->default(0);
        });

        Schema::create('lophoc_tai_lieu', function (Blueprint $table) {
            $table->id('lopHocTaiLieuId');
            $table->unsignedInteger('lopHocId');
            $table->string('tieuDe', 255);
            $table->text('moTa')->nullable();
            $table->string('nhomTaiLieu', 40)->default('tai_lieu');
            $table->string('disk', 30)->default('local');
            $table->string('duongDan', 500);
            $table->string('tenGoc', 255)->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('kichThuoc')->default(0);
            $table->unsignedInteger('nguoiTaiLenId')->nullable();
            $table->timestamp('publishedAt')->nullable();
            $table->unsignedSmallInteger('sortOrder')->default(0);
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /* ── Factories ──────────────────────────────────────────────────────────── */

    private function createTeacherWithClass(string $username, string $email, string $slug): array
    {
        $teacher = TaiKhoan::create([
            'taiKhoan'           => $username,
            'email'              => $email,
            'matKhau'            => Hash::make('secret'),
            'role'               => TaiKhoan::ROLE_GIAO_VIEN,
            'trangThai'          => 1,
            'phaiDoiMatKhau'     => 0,
            'auth_provider'      => 'local',
            'email_verified_at'  => now(),
        ]);

        \App\Models\Auth\HoSoNguoiDung::create([
            'taiKhoanId' => $teacher->taiKhoanId,
            'hoTen'      => 'Giáo viên ' . $username,
        ]);

        $lopHoc = LopHoc::create([
            'slug'        => $slug,
            'maLopHoc'    => strtoupper($slug),
            'tenLopHoc'   => 'Lớp ' . $username,
            'taiKhoanId'  => $teacher->taiKhoanId,
            'trangThai'   => LopHoc::TRANG_THAI_DANG_HOC,
        ]);

        return [$teacher, $lopHoc];
    }

    private function createStudent(string $username, string $email): TaiKhoan
    {
        $student = TaiKhoan::create([
            'taiKhoan'          => $username,
            'email'             => $email,
            'matKhau'           => Hash::make('secret'),
            'role'              => TaiKhoan::ROLE_HOC_VIEN,
            'trangThai'         => 1,
            'phaiDoiMatKhau'    => 0,
            'auth_provider'     => 'local',
            'email_verified_at' => now(),
        ]);

        \App\Models\Auth\HoSoNguoiDung::create([
            'taiKhoanId' => $student->taiKhoanId,
            'hoTen'      => 'Học viên ' . $username,
        ]);

        return $student;
    }

    private function createDangKy(TaiKhoan $student, LopHoc $lopHoc, int $trangThai): DangKyLopHoc
    {
        return DangKyLopHoc::create([
            'taiKhoanId' => $student->taiKhoanId,
            'lopHocId'   => $lopHoc->lopHocId,
            'trangThai'  => $trangThai,
        ]);
    }

    private function createTaiLieu(LopHoc $lopHoc): LopHocTaiLieu
    {
        Storage::disk('local')->put(
            'lop-hoc/' . $lopHoc->lopHocId . '/test.pdf',
            'fake-content'
        );

        return LopHocTaiLieu::create([
            'lopHocId'    => $lopHoc->lopHocId,
            'tieuDe'      => 'Tài liệu test',
            'nhomTaiLieu' => LopHocTaiLieu::NHOM_TAI_LIEU,
            'disk'        => 'local',
            'duongDan'    => 'lop-hoc/' . $lopHoc->lopHocId . '/test.pdf',
            'tenGoc'      => 'test.pdf',
            'mime'        => 'application/pdf',
            'kichThuoc'   => 1024,
            'trangThai'   => LopHocTaiLieu::TRANG_THAI_ACTIVE,
        ]);
    }
}
