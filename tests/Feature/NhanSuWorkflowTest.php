<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\NhanSu;
use App\Models\Auth\NhanSuGoiLuong;
use App\Models\Auth\NhanSuHoSo;
use App\Models\Auth\NhanSuMauQuyDinh;
use App\Models\Auth\NhanSuTaiLieu;
use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NhanSuWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        $this->createNhanSuSchema();
    }

    public function test_teacher_creation_redirects_to_profile_with_handover_flow(): void
    {
        $admin = $this->createAdmin();
        $branch = $this->createBranch();
        $template = $this->createPolicyTemplate();

        $response = $this->actingAs($admin)->post(route('admin.giao-vien.store'), [
            'email' => 'gv.a@example.com',
            'hoTen' => 'Giáo viên A',
            'soDienThoai' => '0900000001',
            'cccd' => '123456789012',
            'chucVu' => 'Giáo viên chính',
            'chuyenMon' => 'Tiếng Anh',
            'bangCap' => 'Thạc sĩ',
            'hocVi' => 'IELTS 8.0',
            'loaiHopDong' => 'FULL_TIME',
            'ngayVaoLam' => '2026-03-15',
            'coSoId' => $branch->coSoId,
            'nhanSuMauQuyDinhId' => $template->nhanSuMauQuyDinhId,
            'loaiLuong' => NhanSuGoiLuong::LOAI_LUONG_MONTHLY,
            'luongChinh' => 12000000,
            'hieuLucTu' => '2026-03-15',
        ]);

        $location = (string) $response->headers->get('Location');

        $response->assertStatus(302);
        $this->assertStringContainsString('/admin/giao-vien/GV000002', $location);
        $this->assertStringContainsString('handover=', $location);

        $teacher = TaiKhoan::where('role', TaiKhoan::ROLE_GIAO_VIEN)->where('email', 'gv.a@example.com')->firstOrFail();

        $this->assertSame('GV000002', $teacher->taiKhoan);
        $this->assertSame(1, (int) $teacher->phaiDoiMatKhau);
        $this->assertNotSame('123456789012', $teacher->matKhau);
        $this->assertDatabaseHas('nhansu_hoso', ['taiKhoanId' => $teacher->taiKhoanId]);
        $this->assertDatabaseHas('nhansu_goi_luong', [
            'taiKhoanId' => $teacher->taiKhoanId,
            'loaiLuong' => NhanSuGoiLuong::LOAI_LUONG_MONTHLY,
        ]);

        $this->actingAs($admin)
            ->get($location)
            ->assertOk()
            ->assertSee('Phiếu bàn giao tài khoản')
            ->assertSee('GV000002');

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $token = $query['handover'] ?? null;

        $handoverResponse = $this->actingAs($admin)->get(route('admin.giao-vien.handover.pdf', [
            'taiKhoan' => $teacher->taiKhoan,
            'token' => $token,
        ]));

        $handoverResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $handoverResponse->headers->get('content-type'));

        $this->actingAs($admin)->get(route('admin.giao-vien.handover.pdf', [
            'taiKhoan' => $teacher->taiKhoan,
            'token' => $token,
        ]))->assertNotFound();
    }

    public function test_edit_views_for_teacher_and_employee_render_successfully(): void
    {
        $admin = $this->createAdmin();
        $teacher = $this->createStaffAccount(TaiKhoan::ROLE_GIAO_VIEN, 'GV000010', 'teacher@example.com', 'Giáo viên Test');
        $employee = $this->createStaffAccount(TaiKhoan::ROLE_NHAN_VIEN, 'NV000011', 'staff@example.com', 'Nhân viên Test');

        $this->actingAs($admin)
            ->get(route('admin.giao-vien.edit', $teacher->taiKhoan))
            ->assertOk()
            ->assertSee('Cập nhật giáo viên')
            ->assertSee('GV000010');

        $this->actingAs($admin)
            ->get(route('admin.nhan-vien.edit', $employee->taiKhoan))
            ->assertOk()
            ->assertSee('Cập nhật nhân viên')
            ->assertSee('NV000011');
    }

    public function test_employee_update_keeps_password_when_left_blank(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createStaffAccount(TaiKhoan::ROLE_NHAN_VIEN, 'NV000021', 'cu@example.com', 'Nhân viên Cũ', 'old-password');
        $oldHash = $employee->matKhau;
        $oldRememberToken = $employee->remember_token;

        $this->actingAs($admin)->put(route('admin.nhan-vien.update', $employee->taiKhoan), [
            'email' => 'moi@example.com',
            'trangThai' => 1,
            'hoTen' => 'Nhân viên Mới',
            'soDienThoai' => '0901111111',
            'cccd' => '111122223333',
            'chucVu' => 'Quản lý',
            'chuyenMon' => 'Hành chính nhân sự',
            'bangCap' => 'Cử nhân',
            'hocVi' => 'MOS',
            'loaiHopDong' => 'PART_TIME',
            'ngayVaoLam' => '2026-03-15',
            'coSoId' => 1,
        ])->assertRedirect(route('admin.nhan-vien.index'));

        $employee->refresh();

        $this->assertSame('moi@example.com', $employee->email);
        $this->assertTrue(Hash::check('old-password', $employee->matKhau));
        $this->assertSame($oldHash, $employee->matKhau);
        $this->assertSame($oldRememberToken, $employee->remember_token);
        $this->assertSame('Nhân viên Mới', $employee->hoSoNguoiDung->hoTen);
        $this->assertSame('PART_TIME', $employee->nhanSu->loaiHopDong);
    }

    public function test_employee_update_accepts_avatar_upload_without_crashing(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $employee = $this->createStaffAccount(TaiKhoan::ROLE_NHAN_VIEN, 'NV000022', 'avatar@example.com', 'Nhân viên Ảnh');

        $this->actingAs($admin)->put(route('admin.nhan-vien.update', $employee->taiKhoan), [
            'email' => 'avatar-updated@example.com',
            'trangThai' => 1,
            'hoTen' => 'Nhân viên Ảnh',
            'anhDaiDien' => UploadedFile::fake()->image('avatar.png', 200, 200),
            'chucVu' => 'Nhân viên',
            'chuyenMon' => 'IT',
            'bangCap' => 'Cử nhân',
            'hocVi' => 'AWS',
            'loaiHopDong' => 'FULL_TIME',
            'ngayVaoLam' => '2026-03-15',
            'coSoId' => 1,
        ])->assertRedirect(route('admin.nhan-vien.index'));

        $employee->refresh();

        $this->assertSame('avatar-updated@example.com', $employee->email);
        $this->assertNotNull($employee->hoSoNguoiDung?->anhDaiDien);
        Storage::disk('public')->assertExists($employee->hoSoNguoiDung->anhDaiDien);
    }

    public function test_updating_password_and_locking_account_rotates_remember_token(): void
    {
        $admin = $this->createAdmin();
        $teacher = $this->createStaffAccount(TaiKhoan::ROLE_GIAO_VIEN, 'GV000031', 'gv-lock@example.com', 'Giáo viên Khóa', 'old-password');
        $oldRememberToken = $teacher->remember_token;

        $this->actingAs($admin)->put(route('admin.giao-vien.update', $teacher->taiKhoan), [
            'email' => 'gv-lock@example.com',
            'trangThai' => 0,
            'matKhau' => 'new-password',
            'matKhau_confirmation' => 'new-password',
            'hoTen' => 'Giáo viên Khóa',
            'cccd' => '222233334444',
            'chucVu' => 'Giáo viên',
            'chuyenMon' => 'Tiếng Anh',
            'bangCap' => 'Cử nhân',
            'hocVi' => 'TESOL',
            'loaiHopDong' => 'FULL_TIME',
            'ngayVaoLam' => '2026-03-15',
            'coSoId' => 1,
        ])->assertRedirect(route('admin.giao-vien.index'));

        $teacher->refresh();

        $this->assertSame(0, (int) $teacher->trangThai);
        $this->assertTrue(Hash::check('new-password', $teacher->matKhau));
        $this->assertNotSame($oldRememberToken, $teacher->remember_token);
        $this->assertDatabaseHas('nhatky_bao_mat', [
            'taiKhoanId' => $teacher->taiKhoanId,
            'suKien' => 'remember_token_rotated',
        ]);
    }

    public function test_update_rejects_duplicate_email_and_cccd(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createStaffAccount(TaiKhoan::ROLE_NHAN_VIEN, 'NV000041', 'target@example.com', 'Nhân viên Mục Tiêu', 'password-1', '123123123123');
        $other = $this->createStaffAccount(TaiKhoan::ROLE_NHAN_VIEN, 'NV000042', 'other@example.com', 'Nhân viên Khác', 'password-2', '999988887777');

        $response = $this->from(route('admin.nhan-vien.edit', $target->taiKhoan))
            ->actingAs($admin)
            ->put(route('admin.nhan-vien.update', $target->taiKhoan), [
                'email' => $other->email,
                'trangThai' => 1,
                'hoTen' => 'Nhân viên Mục Tiêu',
                'cccd' => $other->hoSoNguoiDung->cccd,
                'chucVu' => 'Nhân viên',
                'chuyenMon' => 'IT',
                'bangCap' => 'Cử nhân',
                'hocVi' => 'AWS',
                'loaiHopDong' => 'FULL_TIME',
                'ngayVaoLam' => '2026-03-15',
                'coSoId' => 1,
            ]);

        $response
            ->assertRedirect(route('admin.nhan-vien.edit', $target->taiKhoan))
            ->assertSessionHasErrors(['email', 'cccd']);
    }

    public function test_document_upload_versions_files_and_archives_previous_cv(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin();
        $employee = $this->createStaffAccount(TaiKhoan::ROLE_NHAN_VIEN, 'NV000051', 'docs@example.com', 'Nhân viên Hồ Sơ');

        $this->actingAs($admin)->post(route('admin.nhan-vien.documents.store', $employee->taiKhoan), [
            'loaiTaiLieu' => NhanSuTaiLieu::LOAI_CV,
            'tenHienThi' => 'CV chính thức',
            'tep' => UploadedFile::fake()->create('cv-1.pdf', 30, 'application/pdf'),
        ])->assertRedirect(route('admin.nhan-vien.show', $employee->taiKhoan));

        $first = NhanSuTaiLieu::where('taiKhoanId', $employee->taiKhoanId)->firstOrFail();
        Storage::disk('local')->assertExists($first->duongDan);
        $this->assertSame(NhanSuTaiLieu::TRANG_THAI_ACTIVE, $first->trangThai);

        $this->actingAs($admin)->post(route('admin.nhan-vien.documents.store', $employee->taiKhoan), [
            'loaiTaiLieu' => NhanSuTaiLieu::LOAI_CV,
            'tenHienThi' => 'CV chính thức',
            'tep' => UploadedFile::fake()->create('cv-2.pdf', 30, 'application/pdf'),
        ])->assertRedirect(route('admin.nhan-vien.show', $employee->taiKhoan));

        $this->assertDatabaseCount('nhansu_tai_lieu', 2);
        $this->assertDatabaseHas('nhansu_tai_lieu', [
            'taiKhoanId' => $employee->taiKhoanId,
            'trangThai' => NhanSuTaiLieu::TRANG_THAI_ARCHIVED,
            'phienBan' => 1,
        ]);
        $this->assertDatabaseHas('nhansu_tai_lieu', [
            'taiKhoanId' => $employee->taiKhoanId,
            'trangThai' => NhanSuTaiLieu::TRANG_THAI_ACTIVE,
            'phienBan' => 2,
        ]);
    }

    private function createNhanSuSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'thongbaonguoidung',
            'thongbao',
            'nhatky_bao_mat',
            'nhansu_tai_lieu',
            'nhansu_goi_luong_chi_tiet',
            'nhansu_goi_luong',
            'nhansu_hoso',
            'nhansu_mau_quydinh',
            'nhansu',
            'hosonguoidung',
            'cosodaotao',
            'tinhthanh',
            'nhomQuyen',
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
            $table->integer('nhomQuyenId')->nullable();
            $table->integer('trangThai')->default(1);
            $table->integer('phaiDoiMatKhau')->default(0);
            $table->string('auth_provider')->nullable();
            $table->string('google_id')->nullable();
            $table->string('google_avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamp('lastLogin')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('thongbao', function (Blueprint $table) {
            $table->increments('thongBaoId');
            $table->integer('nguoiGuiId')->nullable();
            $table->string('tieuDe')->nullable();
            $table->text('noiDung')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('thongbaonguoidung', function (Blueprint $table) {
            $table->increments('thongBaoNguoiDungId');
            $table->unsignedInteger('thongBaoId');
            $table->unsignedInteger('taiKhoanId');
            $table->boolean('daDoc')->default(false);
            $table->timestamps();
        });

        Schema::create('nhomQuyen', function (Blueprint $table) {
            $table->increments('nhomQuyenId');
            $table->string('tenNhom')->nullable();
            $table->string('moTa')->nullable();
            $table->timestamps();
        });

        Schema::create('tinhthanh', function (Blueprint $table) {
            $table->increments('tinhThanhId');
            $table->string('tenTinhThanh');
            $table->string('slug')->nullable();
            $table->string('maAPI')->nullable();
            $table->string('division_type')->nullable();
            $table->string('codename')->nullable();
        });

        Schema::create('cosodaotao', function (Blueprint $table) {
            $table->increments('coSoId');
            $table->string('maCoSo')->nullable();
            $table->string('slug')->nullable();
            $table->string('tenCoSo');
            $table->string('diaChi')->nullable();
            $table->string('soDienThoai')->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('tinhThanhId')->nullable();
            $table->string('maPhuongXa')->nullable();
            $table->string('tenPhuongXa')->nullable();
            $table->decimal('viDo', 10, 7)->nullable();
            $table->decimal('kinhDo', 10, 7)->nullable();
            $table->string('banDoGoogle')->nullable();
            $table->date('ngayKhaiTruong')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('hosonguoidung', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->string('hoTen')->nullable();
            $table->string('soDienThoai')->nullable();
            $table->string('zalo')->nullable();
            $table->date('ngaySinh')->nullable();
            $table->tinyInteger('gioiTinh')->nullable();
            $table->string('diaChi')->nullable();
            $table->string('cccd')->nullable()->unique();
            $table->string('anhDaiDien')->nullable();
            $table->text('ghiChu')->nullable();
            $table->timestamps();
        });

        Schema::create('nhansu', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->string('maNhanVien')->nullable();
            $table->string('chucVu')->nullable();
            $table->decimal('luongCoBan', 15, 2)->default(0);
            $table->date('ngayVaoLam')->nullable();
            $table->string('chuyenMon')->nullable();
            $table->string('bangCap')->nullable();
            $table->string('hocVi')->nullable();
            $table->unsignedInteger('coSoId')->nullable();
            $table->string('loaiHopDong')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('nhansu_mau_quydinh', function (Blueprint $table) {
            $table->increments('nhanSuMauQuyDinhId');
            $table->string('maMau')->unique();
            $table->string('tieuDe');
            $table->string('phamViApDung')->default('both');
            $table->string('loaiHopDongApDung')->nullable();
            $table->longText('noiDung');
            $table->unsignedInteger('phienBan')->default(1);
            $table->tinyInteger('trangThai')->default(1);
            $table->integer('createdById')->nullable();
            $table->integer('updatedById')->nullable();
            $table->timestamps();
        });

        Schema::create('nhansu_hoso', function (Blueprint $table) {
            $table->increments('nhanSuHoSoId');
            $table->unsignedInteger('taiKhoanId')->unique();
            $table->string('maHoSo')->unique();
            $table->unsignedInteger('nhanSuMauQuyDinhId')->nullable();
            $table->string('tieuDeMauSnapshot')->nullable();
            $table->longText('noiDungQuyDinhSnapshot')->nullable();
            $table->string('trangThaiHoSo')->default('draft');
            $table->text('ghiChuHoSo')->nullable();
            $table->timestamps();
        });

        Schema::create('nhansu_goi_luong', function (Blueprint $table) {
            $table->increments('nhanSuGoiLuongId');
            $table->unsignedInteger('taiKhoanId');
            $table->string('loaiLuong', 40);
            $table->decimal('luongChinh', 15, 2)->default(0);
            $table->date('hieuLucTu');
            $table->date('hieuLucDen')->nullable();
            $table->text('ghiChu')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('nhansu_goi_luong_chi_tiet', function (Blueprint $table) {
            $table->increments('nhanSuGoiLuongChiTietId');
            $table->unsignedInteger('nhanSuGoiLuongId');
            $table->string('loai', 30);
            $table->string('tenKhoan');
            $table->decimal('soTien', 15, 2)->default(0);
            $table->text('ghiChu')->nullable();
            $table->unsignedInteger('sortOrder')->default(0);
            $table->timestamps();
        });

        Schema::create('nhansu_tai_lieu', function (Blueprint $table) {
            $table->increments('nhanSuTaiLieuId');
            $table->unsignedInteger('taiKhoanId');
            $table->string('loaiTaiLieu', 30);
            $table->string('tenHienThi');
            $table->string('tenGoc');
            $table->string('duongDan');
            $table->string('disk')->default('local');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('kichThuoc')->default(0);
            $table->string('checksum', 128)->nullable();
            $table->unsignedInteger('phienBan')->default(1);
            $table->integer('duocTaiLenBoiId')->nullable();
            $table->string('trangThai')->default('active');
            $table->text('ghiChu')->nullable();
            $table->timestamp('archivedAt')->nullable();
            $table->timestamps();
        });

        Schema::create('nhatky_bao_mat', function (Blueprint $table) {
            $table->increments('nhatKyBaoMatId');
            $table->unsignedInteger('taiKhoanId');
            $table->unsignedInteger('phienDangNhapId')->nullable();
            $table->string('sessionId')->nullable();
            $table->string('suKien');
            $table->text('moTa');
            $table->string('ipAddress')->nullable();
            $table->text('userAgent')->nullable();
            $table->json('duLieu')->nullable();
            $table->timestamp('thoiGian')->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }

    private function createAdmin(): TaiKhoan
    {
        $admin = TaiKhoan::create([
            'taiKhoan' => 'AD000001',
            'email' => 'admin@example.com',
            'matKhau' => Hash::make('secret'),
            'role' => TaiKhoan::ROLE_ADMIN,
            'trangThai' => 1,
            'phaiDoiMatKhau' => 0,
            'auth_provider' => 'local',
            'email_verified_at' => now(),
            'remember_token' => 'admin-remember-token',
        ]);

        HoSoNguoiDung::create([
            'taiKhoanId' => $admin->taiKhoanId,
            'hoTen' => 'Admin Test',
            'cccd' => '000000000001',
        ]);

        return $admin;
    }

    private function createBranch()
    {
        \App\Models\Facility\TinhThanh::query()->create([
            'tenTinhThanh' => 'Hồ Chí Minh',
            'slug' => 'ho-chi-minh',
            'maAPI' => '79',
        ]);

        return \App\Models\Facility\CoSoDaoTao::query()->create([
            'tenCoSo' => 'Cơ sở Quận 1',
            'diaChi' => '12 Nguyễn Huệ',
            'tinhThanhId' => 1,
            'maPhuongXa' => '00123',
            'tenPhuongXa' => 'Phường Bến Nghé',
            'trangThai' => 1,
        ]);
    }

    private function createPolicyTemplate(): NhanSuMauQuyDinh
    {
        return NhanSuMauQuyDinh::create([
            'maMau' => 'QD-TEST-001',
            'tieuDe' => 'Quy định chuẩn cho nhân sự',
            'phamViApDung' => NhanSuMauQuyDinh::PHAM_VI_BOTH,
            'loaiHopDongApDung' => 'ALL',
            'noiDung' => '<p>Nhân sự phải tuân thủ nội quy trung tâm.</p>',
            'phienBan' => 1,
            'trangThai' => 1,
        ]);
    }

    private function createStaffAccount(
        int $role,
        string $username,
        string $email,
        string $fullName,
        string $plainPassword = 'secret-password',
        ?string $cccd = null
    ): TaiKhoan {
        if (!\App\Models\Facility\CoSoDaoTao::query()->exists()) {
            $this->createBranch();
        }

        $cccd ??= str_pad((string) preg_replace('/\D+/', '', $username), 12, '1');

        $account = TaiKhoan::create([
            'taiKhoan' => $username,
            'email' => $email,
            'matKhau' => Hash::make($plainPassword),
            'role' => $role,
            'trangThai' => 1,
            'phaiDoiMatKhau' => 0,
            'auth_provider' => 'local',
            'email_verified_at' => now(),
            'remember_token' => 'remember-' . $username,
        ]);

        HoSoNguoiDung::create([
            'taiKhoanId' => $account->taiKhoanId,
            'hoTen' => $fullName,
            'cccd' => $cccd,
        ]);

        NhanSu::create([
            'taiKhoanId' => $account->taiKhoanId,
            'chucVu' => $role === TaiKhoan::ROLE_GIAO_VIEN ? 'Giáo viên' : 'Nhân viên',
            'coSoId' => 1,
            'loaiHopDong' => 'FULL_TIME',
            'ngayVaoLam' => '2026-03-15',
            'trangThai' => 1,
        ]);

        NhanSuHoSo::create([
            'taiKhoanId' => $account->taiKhoanId,
            'maHoSo' => 'HS' . $username,
            'trangThaiHoSo' => 'complete',
        ]);

        return $account;
    }
}
