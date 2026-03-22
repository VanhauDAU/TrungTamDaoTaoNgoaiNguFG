<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SessionPortalGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        $this->createMinimalAuthSchema();
        $this->registerTestRoutes();
    }

    public function test_session_status_reports_logged_out_for_staff_context(): void
    {
        $this->getJson(route('auth.session-status', ['context' => 'staff']))
            ->assertOk()
            ->assertJson([
                'authenticated' => false,
                'allowed' => false,
                'expectedContext' => 'staff',
                'reason' => 'logged_out',
            ]);
    }

    public function test_session_status_reports_portal_mismatch_for_staff_tab_with_student_session(): void
    {
        $student = $this->createAccount(TaiKhoan::ROLE_HOC_VIEN, true);

        $this->actingAs($student)
            ->withSession(['auth_portal' => 'student'])
            ->getJson(route('auth.session-status', ['context' => 'staff']))
            ->assertOk()
            ->assertJson([
                'authenticated' => true,
                'allowed' => false,
                'expectedContext' => 'staff',
                'actualPortal' => 'student',
                'actualContext' => 'student',
                'reason' => 'portal_mismatch',
            ]);
    }

    public function test_session_status_returns_fresh_csrf_token_for_allowed_student_tab(): void
    {
        $student = $this->createAccount(TaiKhoan::ROLE_HOC_VIEN, true);

        $response = $this->actingAs($student)
            ->withSession(['auth_portal' => 'student'])
            ->getJson(route('auth.session-status', ['context' => 'student']));

        $response
            ->assertOk()
            ->assertJson([
                'authenticated' => true,
                'allowed' => true,
                'expectedContext' => 'student',
                'actualPortal' => 'student',
                'actualContext' => 'student',
                'reason' => 'ok',
            ])
            ->assertJsonStructure(['csrfToken']);
    }

    public function test_admin_middleware_redirects_student_session_to_student_home_instead_of_forbidden(): void
    {
        $student = $this->createAccount(TaiKhoan::ROLE_HOC_VIEN, true);

        $this->actingAs($student)
            ->get('/__test__/admin-only')
            ->assertRedirect(route('home.student.index'))
            ->assertSessionHas('warning', 'Phiên đăng nhập nội bộ không còn hợp lệ vì trình duyệt hiện đang dùng cổng học viên ở tab khác.');
    }

    private function registerTestRoutes(): void
    {
        if (!Route::has('test.admin-only')) {
            Route::middleware(['web', 'auth', 'isAdmin'])
                ->get('/__test__/admin-only', fn () => response('OK'))
                ->name('test.admin-only');
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
