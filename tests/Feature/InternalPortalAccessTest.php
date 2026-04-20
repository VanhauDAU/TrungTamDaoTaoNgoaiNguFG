<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InternalPortalAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        $this->createMinimalAuthSchema();
        $this->registerTestRoutes();
    }

    public function test_teacher_can_access_teacher_portal_route(): void
    {
        $teacher = $this->createAccount(TaiKhoan::ROLE_GIAO_VIEN, true);

        $this->actingAs($teacher)
            ->withSession(['auth_portal' => 'teacher'])
            ->get('/__test__/teacher-only')
            ->assertOk()
            ->assertSee('teacher-ok');
    }

    public function test_staff_cannot_access_teacher_portal_route(): void
    {
        $staff = $this->createAccount(TaiKhoan::ROLE_NHAN_VIEN, true);

        $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->get('/__test__/teacher-only')
            ->assertRedirect(route('staff.dashboard'))
            ->assertSessionHas('warning', 'Phiên đăng nhập giảng viên không còn hợp lệ vì trình duyệt hiện đang dùng cổng nhân viên ở tab khác.');
    }

    public function test_admin_can_access_admin_portal_route(): void
    {
        $admin = $this->createAccount(TaiKhoan::ROLE_ADMIN, true);

        $this->actingAs($admin)
            ->withSession(['auth_portal' => 'admin'])
            ->get('/__test__/admin-only-v2')
            ->assertOk()
            ->assertSee('admin-ok');
    }

    public function test_staff_cannot_access_admin_portal_route(): void
    {
        $staff = $this->createAccount(TaiKhoan::ROLE_NHAN_VIEN, true);

        $this->actingAs($staff)
            ->withSession(['auth_portal' => 'staff'])
            ->get('/__test__/admin-only-v2')
            ->assertRedirect(route('staff.dashboard'))
            ->assertSessionHas('warning', 'Phiên đăng nhập quản trị không còn hợp lệ vì trình duyệt hiện đang dùng cổng nhân viên ở tab khác.');
    }

    private function registerTestRoutes(): void
    {
        if (!Route::has('test.teacher-only')) {
            Route::middleware(['web', 'auth', 'portal:teacher'])
                ->get('/__test__/teacher-only', fn () => response('teacher-ok'))
                ->name('test.teacher-only');
        }

        if (!Route::has('test.admin-only-v2')) {
            Route::middleware(['web', 'auth', 'portal:admin'])
                ->get('/__test__/admin-only-v2', fn () => response('admin-ok'))
                ->name('test.admin-only-v2');
        }
    }

    private function createMinimalAuthSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'password_reset_tokens',
            'sessions',
            'taikhoan',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('taikhoan', function (Blueprint $table) {
            $table->increments('taiKhoanId');
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

    private function createAccount(int $role, bool $verified): TaiKhoan
    {
        $prefix = TaiKhoan::prefixForRole($role);

        return TaiKhoan::query()->create([
            'taiKhoan' => $prefix . fake()->unique()->numerify('######'),
            'matKhau' => bcrypt('password'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => $verified ? now() : null,
            'role' => $role,
            'trangThai' => 1,
            'phaiDoiMatKhau' => 0,
        ]);
    }
}
