<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\BaoCaoHocTapMau;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProgressReportTemplateManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        $this->createMinimalSchema();
        $this->runProgressReportMigration();
    }

    public function test_staff_can_create_and_update_template_from_ui(): void
    {
        $staff = $this->createStaffAccount();

        $response = $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->post(route('staff.evaluations.templates.store'), [
                'tenMau' => 'Mẫu giữa khóa nâng cao',
                'moTa' => 'Dùng cho lớp trung cấp và nâng cao.',
                'phienBan' => '2.0',
                'kichHoat' => '1',
                'criteria' => [
                    [
                        'nhom' => 'Kết luận',
                        'maTieuChi' => 'progress_level',
                        'tenTieuChi' => 'Mức độ tiến bộ',
                        'loaiDuLieu' => 'rating',
                        'danhSachMucText' => "Chưa đạt\nKhá\nTốt",
                        'batBuoc' => '1',
                        'thuTu' => 10,
                    ],
                    [
                        'nhom' => 'Kết luận',
                        'maTieuChi' => 'next_recommendation',
                        'tenTieuChi' => 'Khuyến nghị tiếp theo',
                        'loaiDuLieu' => 'text',
                        'thuTu' => 20,
                    ],
                ],
            ]);

        $template = BaoCaoHocTapMau::query()
            ->where('tenMau', 'Mẫu giữa khóa nâng cao')
            ->firstOrFail();

        $response->assertRedirect(route('staff.evaluations.templates.edit', $template->baoCaoHocTapMauId));
        $this->assertSame(2, $template->tieuChis()->count());

        $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->put(route('staff.evaluations.templates.update', $template->baoCaoHocTapMauId), [
                'tenMau' => 'Mẫu giữa khóa nâng cao v2',
                'moTa' => 'Đã cập nhật cho kỳ đánh giá mới.',
                'phienBan' => '2.1',
                'kichHoat' => '1',
                'criteria' => [
                    [
                        'nhom' => 'Kết luận',
                        'maTieuChi' => 'progress_level',
                        'tenTieuChi' => 'Mức độ tiến bộ tổng quan',
                        'loaiDuLieu' => 'rating',
                        'danhSachMucText' => "Chưa đạt\nĐạt tối thiểu\nKhá\nTốt",
                        'batBuoc' => '1',
                        'thuTu' => 10,
                    ],
                ],
            ])
            ->assertRedirect(route('staff.evaluations.templates.edit', $template->baoCaoHocTapMauId));

        $template->refresh();
        $this->assertSame('Mẫu giữa khóa nâng cao v2', $template->tenMau);
        $this->assertSame(1, $template->tieuChis()->count());
        $this->assertDatabaseHas('bao_cao_hoc_tap_mau_tieu_chi', [
            'baoCaoHocTapMauId' => $template->baoCaoHocTapMauId,
            'tenTieuChi' => 'Mức độ tiến bộ tổng quan',
        ]);
    }

    public function test_setting_default_template_keeps_only_one_default(): void
    {
        $staff = $this->createStaffAccount();

        $templateA = BaoCaoHocTapMau::query()->where('macDinh', true)->firstOrFail();
        $templateB = BaoCaoHocTapMau::query()->create([
            'tenMau' => 'Mẫu phụ',
            'moTa' => null,
            'phienBan' => '1.0',
            'macDinh' => false,
            'kichHoat' => true,
        ]);

        $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->patch(route('staff.evaluations.templates.set-default', $templateB->baoCaoHocTapMauId))
            ->assertRedirect();

        $templateA->refresh();
        $templateB->refresh();

        $this->assertFalse((bool) $templateA->macDinh);
        $this->assertTrue((bool) $templateB->macDinh);
        $this->assertSame(1, BaoCaoHocTapMau::query()->where('macDinh', true)->count());
    }

    private function createStaffAccount(): TaiKhoan
    {
        return TaiKhoan::query()->create([
            'taiKhoan' => 'NV' . fake()->unique()->numerify('######'),
            'matKhau' => bcrypt('password'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role' => TaiKhoan::ROLE_NHAN_VIEN,
            'trangThai' => 1,
            'phaiDoiMatKhau' => 0,
        ]);
    }

    private function runProgressReportMigration(): void
    {
        $migration = require base_path('database/migrations/2026_04_22_090000_create_bao_cao_hoc_tap_module_tables.php');
        $migration->up();
    }

    private function createMinimalSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'bao_cao_hoc_tap_lich_su',
            'bao_cao_hoc_tap_tieu_chi',
            'bao_cao_hoc_tap',
            'bao_cao_hoc_tap_dot_danh_gia',
            'bao_cao_hoc_tap_mau_tieu_chi',
            'bao_cao_hoc_tap_mau',
            'dangkylophoc',
            'lophoc',
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

        Schema::create('lophoc', function (Blueprint $table) {
            $table->integer('lopHocId', true);
            $table->string('tenLopHoc')->nullable();
            $table->integer('taiKhoanId')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dangkylophoc', function (Blueprint $table) {
            $table->integer('dangKyLopHocId', true);
            $table->integer('taiKhoanId')->nullable();
            $table->integer('lopHocId')->nullable();
            $table->tinyInteger('trangThai')->default(0);
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
