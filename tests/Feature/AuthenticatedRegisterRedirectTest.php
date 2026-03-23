<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthenticatedRegisterRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        $this->createMinimalAuthSchema();
    }

    public function test_authenticated_student_visiting_register_is_redirected_to_student_portal(): void
    {
        $student = $this->createAccount(TaiKhoan::ROLE_HOC_VIEN, true);

        $this->actingAs($student)
            ->get(route('register'))
            ->assertRedirect(route('home.student.index'));
    }

    public function test_authenticated_staff_visiting_register_is_redirected_to_admin_dashboard(): void
    {
        $staff = $this->createAccount(TaiKhoan::ROLE_NHAN_VIEN, true);

        $this->actingAs($staff)
            ->get(route('register'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_unverified_student_visiting_register_is_redirected_to_verification_notice(): void
    {
        $student = $this->createAccount(TaiKhoan::ROLE_HOC_VIEN, false);

        $this->actingAs($student)
            ->get(route('register'))
            ->assertRedirect(route('verification.notice'));
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
