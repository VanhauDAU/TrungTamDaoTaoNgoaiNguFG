<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\GiaoVienTaiLieu;
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

        // Request thẳng web URL /storage/... phải 404 (hoặc 403 tùy config server, nhưng không được 200)
        $this->get('/storage/' . $path)->assertStatus(403);
    }

    /* ── Case 5: Giáo viên chỉnh sửa tài liệu thư viện ─────────────────────── */
    public function test_teacher_can_edit_library_item()
    {
        Storage::fake('local');
        [$teacher] = $this->createTeacherWithClass('GV_EDIT', 'edit@example.com', 'lop-edit');
        
        $item = GiaoVienTaiLieu::create([
            'tieuDe' => 'Old Title',
            'nhomTaiLieu' => 'tai_lieu',
            'nguoiTaiLenId' => $teacher->taiKhoanId,
            'disk' => 'local',
            'duongDan' => 'old/path.pdf',
            'tenGoc' => 'old.pdf',
            'mime' => 'application/pdf',
            'kichThuoc' => 100,
        ]);

        $this->actingAs($teacher)
            ->put(route('teacher.materials.update', $item->giaoVienTaiLieuId), [
                'tieuDe' => 'New Title',
                'nhomTaiLieu' => 'bai_tap',
            ])
            ->assertRedirect(route('teacher.materials.index'));

        $this->assertDatabaseHas('giao_vien_tai_lieu', [
            'giaoVienTaiLieuId' => $item->giaoVienTaiLieuId,
            'tieuDe' => 'New Title',
            'nhomTaiLieu' => 'bai_tap',
        ]);
    }

    /* ── Schema bootstrap ───────────────────────────────────────────────────── */

    private function buildSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'lophoc_tai_lieu',
            'giao_vien_tai_lieu',
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
            $table->unsignedInteger('giaoVienTaiLieuId')->nullable(); // tracking source
            $table->string('dotChiaSeKey', 64)->nullable();
            $table->string('dotChiaSeTieuDe', 255)->nullable();
            $table->timestamp('dotChiaSeAt')->nullable();
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

        Schema::create('giao_vien_tai_lieu', function (Blueprint $table) {
            $table->increments('giaoVienTaiLieuId');
            $table->unsignedInteger('nguoiTaiLenId');
            $table->string('tieuDe', 255);
            $table->text('moTa')->nullable();
            $table->string('nhomTaiLieu', 40)->default('tai_lieu');
            $table->string('disk', 30)->default('local');
            $table->string('duongDan', 500);
            $table->string('tenGoc', 255);
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('kichThuoc')->default(0);
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

    private function createTaiLieu(LopHoc $lopHoc, ?int $giaoVienTaiLieuId = null): LopHocTaiLieu
    {
        Storage::disk('local')->put(
            'lop-hoc/' . $lopHoc->lopHocId . '/test.pdf',
            'fake-content'
        );

        return LopHocTaiLieu::create([
            'lopHocId'          => $lopHoc->lopHocId,
            'giaoVienTaiLieuId' => $giaoVienTaiLieuId,
            'tieuDe'            => 'Tài liệu test',
            'nhomTaiLieu'       => LopHocTaiLieu::NHOM_TAI_LIEU,
            'disk'              => 'local',
            'duongDan'          => 'lop-hoc/' . $lopHoc->lopHocId . '/test.pdf',
            'tenGoc'            => 'test.pdf',
            'mime'              => 'application/pdf',
            'kichThuoc'         => 1024,
            'trangThai'         => LopHocTaiLieu::TRANG_THAI_ACTIVE,
        ]);
    }

    /* ── Case 5: Giáo viên upload vào thư viện cá nhân ─────────────────────── */

    public function test_teacher_can_upload_to_personal_library(): void
    {
        Storage::fake('local');

        [$teacher] = $this->createTeacherWithClass('GVF001', 'gvf@example.com', 'lop-f');

        $fakeFile = UploadedFile::fake()->create('lecture.pdf', 100, 'application/pdf');

        $this->actingAs($teacher)
            ->post(route('teacher.materials.store'), [
                'tieuDe'      => 'Bài giảng ngữ pháp',
                'nhomTaiLieu' => GiaoVienTaiLieu::NHOM_TAI_LIEU,
                'tep'         => $fakeFile,
            ])
            ->assertRedirect(route('teacher.materials.index'))
            ->assertSessionHas('success');

        // Record phải tồn tại trong DB
        $this->assertDatabaseHas('giao_vien_tai_lieu', [
            'nguoiTaiLenId' => $teacher->taiKhoanId,
            'tieuDe'        => 'Bài giảng ngữ pháp',
        ]);

        // File phải được lưu trên disk local
        $record = GiaoVienTaiLieu::where('nguoiTaiLenId', $teacher->taiKhoanId)->first();
        Storage::disk('local')->assertExists($record->duongDan);
    }

    public function test_teacher_can_upload_multiple_files_to_personal_library(): void
    {
        Storage::fake('local');

        [$teacher] = $this->createTeacherWithClass('GV_MULTI', 'gvmulti@example.com', 'lop-multi');

        $fileA = UploadedFile::fake()->create('grammar.pdf', 100, 'application/pdf');
        $fileB = UploadedFile::fake()->create('worksheet.docx', 80, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->actingAs($teacher)
            ->post(route('teacher.materials.store'), [
                'nhomTaiLieu' => GiaoVienTaiLieu::NHOM_TAI_LIEU,
                'teps'        => [$fileA, $fileB],
            ])
            ->assertRedirect(route('teacher.materials.index'))
            ->assertSessionHas('success', 'Đã tải 2 tài liệu lên thư viện thành công.');

        $records = GiaoVienTaiLieu::where('nguoiTaiLenId', $teacher->taiKhoanId)
            ->orderBy('giaoVienTaiLieuId')
            ->get();

        $this->assertCount(2, $records);
        $this->assertSame('grammar', $records[0]->tieuDe);
        $this->assertSame('worksheet', $records[1]->tieuDe);

        foreach ($records as $record) {
            Storage::disk('local')->assertExists($record->duongDan);
        }
    }

    /* ── Case 6: Chia sẻ tài liệu từ thư viện vào lớp ─────────────────────── */

    public function test_teacher_can_share_library_item_to_class(): void
    {
        Storage::fake('local');

        [$teacher, $lopHoc] = $this->createTeacherWithClass('GVG001', 'gvg@example.com', 'lop-g');

        // Tạo tài liệu trong thư viện cá nhân
        $duongDan = 'giao-vien/' . $teacher->taiKhoanId . '/file.pdf';
        Storage::disk('local')->put($duongDan, 'content');

        $giaoVienTaiLieu = GiaoVienTaiLieu::create([
            'nguoiTaiLenId' => $teacher->taiKhoanId,
            'tieuDe'        => 'Tài liệu gốc',
            'nhomTaiLieu'   => GiaoVienTaiLieu::NHOM_TAI_LIEU,
            'disk'          => 'local',
            'duongDan'      => $duongDan,
            'tenGoc'        => 'file.pdf',
            'mime'          => 'application/pdf',
            'kichThuoc'     => 1024,
        ]);

        // Chia sẻ vào lớp
        $this->actingAs($teacher)
            ->post(route('teacher.classes.materials.share', $lopHoc->slug), [
                'giaoVienTaiLieuId' => $giaoVienTaiLieu->giaoVienTaiLieuId,
                'tieuDe'            => 'Tài liệu lớp học',
                'trangThai'         => LopHocTaiLieu::TRANG_THAI_ACTIVE,
            ])
            ->assertRedirect(route('teacher.classes.materials.index', $lopHoc->slug))
            ->assertSessionHas('success');

        // Bản ghi chia sẻ phải tồn tại, reference đúng tài liệu gốc
        $this->assertDatabaseHas('lophoc_tai_lieu', [
            'lopHocId'          => $lopHoc->lopHocId,
            'giaoVienTaiLieuId' => $giaoVienTaiLieu->giaoVienTaiLieuId,
            'tieuDe'            => 'Tài liệu lớp học',
        ]);

        // File gốc vẫn còn trong thư viện
        Storage::disk('local')->assertExists($duongDan);
    }

    public function test_teacher_can_share_multiple_library_items_to_class(): void
    {
        Storage::fake('local');

        [$teacher, $lopHoc] = $this->createTeacherWithClass('GV_BATCH', 'gvbatch@example.com', 'lop-batch');

        $firstPath = 'giao-vien/' . $teacher->taiKhoanId . '/grammar.pdf';
        $secondPath = 'giao-vien/' . $teacher->taiKhoanId . '/slides.pdf';
        Storage::disk('local')->put($firstPath, 'grammar-content');
        Storage::disk('local')->put($secondPath, 'slides-content');

        $first = GiaoVienTaiLieu::create([
            'nguoiTaiLenId' => $teacher->taiKhoanId,
            'tieuDe'        => 'Grammar Pack',
            'nhomTaiLieu'   => GiaoVienTaiLieu::NHOM_TAI_LIEU,
            'disk'          => 'local',
            'duongDan'      => $firstPath,
            'tenGoc'        => 'grammar.pdf',
            'mime'          => 'application/pdf',
            'kichThuoc'     => 1024,
        ]);

        $second = GiaoVienTaiLieu::create([
            'nguoiTaiLenId' => $teacher->taiKhoanId,
            'tieuDe'        => 'Slides Week 1',
            'nhomTaiLieu'   => GiaoVienTaiLieu::NHOM_SLIDE,
            'disk'          => 'local',
            'duongDan'      => $secondPath,
            'tenGoc'        => 'slides.pdf',
            'mime'          => 'application/pdf',
            'kichThuoc'     => 2048,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.classes.materials.share', $lopHoc->slug), [
                'giaoVienTaiLieuIds' => [$first->giaoVienTaiLieuId, $second->giaoVienTaiLieuId],
                'trangThai'          => LopHocTaiLieu::TRANG_THAI_ACTIVE,
                'sortOrder'          => 3,
            ])
            ->assertRedirect(route('teacher.classes.materials.index', $lopHoc->slug))
            ->assertSessionHas('success', 'Đã chia sẻ 2 tài liệu vào lớp học thành công.');

        $this->assertDatabaseHas('lophoc_tai_lieu', [
            'lopHocId'          => $lopHoc->lopHocId,
            'giaoVienTaiLieuId' => $first->giaoVienTaiLieuId,
            'tieuDe'            => 'Grammar Pack',
            'sortOrder'         => 3,
        ]);

        $this->assertDatabaseHas('lophoc_tai_lieu', [
            'lopHocId'          => $lopHoc->lopHocId,
            'giaoVienTaiLieuId' => $second->giaoVienTaiLieuId,
            'tieuDe'            => 'Slides Week 1',
            'sortOrder'         => 4,
        ]);

        $sharedRows = LopHocTaiLieu::where('lopHocId', $lopHoc->lopHocId)
            ->orderBy('lopHocTaiLieuId')
            ->get();

        $this->assertSame($sharedRows[0]->dotChiaSeKey, $sharedRows[1]->dotChiaSeKey);
        $this->assertNotNull($sharedRows[0]->dotChiaSeTieuDe);
        $this->assertNotNull($sharedRows[0]->dotChiaSeAt);
    }

    /* ── Case 7: Giáo viên không chia sẻ tài liệu của người khác ───────────── */

    public function test_teacher_cannot_share_another_teachers_library_item(): void
    {
        Storage::fake('local');

        [$teacherA, $lopHocA] = $this->createTeacherWithClass('GVH001', 'gvh@example.com', 'lop-h');
        [$teacherB]           = $this->createTeacherWithClass('GVI001', 'gvi@example.com', 'lop-i');

        // Tài liệu của Teacher B
        $giaoVienTaiLieu = GiaoVienTaiLieu::create([
            'nguoiTaiLenId' => $teacherB->taiKhoanId,
            'tieuDe'        => 'File của B',
            'nhomTaiLieu'   => GiaoVienTaiLieu::NHOM_TAI_LIEU,
            'disk'          => 'local',
            'duongDan'      => 'giao-vien/' . $teacherB->taiKhoanId . '/file-b.pdf',
            'tenGoc'        => 'file-b.pdf',
            'kichThuoc'     => 512,
        ]);

        // Teacher A cố chia sẻ file của Teacher B vào lớp của A → 404
        $this->actingAs($teacherA)
            ->post(route('teacher.classes.materials.share', $lopHocA->slug), [
                'giaoVienTaiLieuId' => $giaoVienTaiLieu->giaoVienTaiLieuId,
                'tieuDe'            => 'Cố chia sẻ',
                'trangThai'         => 1,
            ])
            ->assertStatus(404);
    }
}
