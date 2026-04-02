<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReusableImageUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        $this->createMinimalSchema();
        Storage::fake('public');
    }

    public function test_authenticated_user_can_upload_image_via_generic_api(): void
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->postJson(route('api.uploads.images.store'), [
            'preset' => 'avatar',
            'file' => UploadedFile::fake()->image('chan-dung.png', 1200, 900),
        ]);

        $response->assertOk()
            ->assertJsonPath('file.preset', 'avatar')
            ->assertJsonPath('file.extension', 'jpg');

        $path = (string) $response->json('file.path');

        $this->assertStringStartsWith('anh-dai-dien/', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertLessThanOrEqual(400, (int) $response->json('file.width'));
        $this->assertLessThanOrEqual(400, (int) $response->json('file.height'));
    }

    public function test_student_avatar_endpoint_updates_profile_and_replaces_previous_file(): void
    {
        $student = $this->createStudent();
        $oldPath = 'anh-dai-dien/avatar-cu.jpg';

        Storage::disk('public')->put($oldPath, 'old-avatar');
        $student->hoSoNguoiDung()->create([
            'taiKhoanId' => $student->taiKhoanId,
            'hoTen' => 'Hoc Vien Test',
            'anhDaiDien' => $oldPath,
        ]);

        $response = $this->actingAs($student)->postJson(route('home.student.update-avatar'), [
            'anhDaiDien' => UploadedFile::fake()->image('avatar-moi.webp', 800, 600),
        ]);

        $response->assertOk()
            ->assertJsonPath('file.preset', 'avatar');

        $student->refresh()->load('hoSoNguoiDung');
        $newPath = (string) $student->hoSoNguoiDung?->anhDaiDien;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
        $this->assertSame($student->getAvatarUrl(), $response->json('avatarUrl'));
    }

    private function createMinimalSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'password_reset_tokens',
            'sessions',
            'hosonguoidung',
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

        Schema::create('hosonguoidung', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->string('hoTen')->nullable();
            $table->string('soDienThoai')->nullable();
            $table->string('zalo')->nullable();
            $table->date('ngaySinh')->nullable();
            $table->tinyInteger('gioiTinh')->nullable();
            $table->string('diaChi')->nullable();
            $table->string('cccd')->nullable();
            $table->string('anhDaiDien')->nullable();
            $table->string('nguoiGiamHo')->nullable();
            $table->string('sdtGuardian')->nullable();
            $table->string('moiQuanHe')->nullable();
            $table->string('trinhDoHienTai')->nullable();
            $table->string('ngonNguMucTieu')->nullable();
            $table->string('nguonBietDen')->nullable();
            $table->text('ghiChu')->nullable();
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

    private function createStudent(): TaiKhoan
    {
        return TaiKhoan::query()->create([
            'taiKhoan' => 'HV' . fake()->unique()->numerify('######'),
            'matKhau' => bcrypt('password'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role' => TaiKhoan::ROLE_HOC_VIEN,
            'trangThai' => 1,
            'phaiDoiMatKhau' => 0,
        ]);
    }
}
