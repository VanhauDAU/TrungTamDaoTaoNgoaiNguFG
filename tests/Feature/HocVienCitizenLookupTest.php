<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HocVienCitizenLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        $this->createMinimalAuthSchema();

        config()->set('services.vietqr.client_id', 'test-client-id');
        config()->set('services.vietqr.api_key', 'test-api-key');
        config()->set('services.vietqr.citizen_url', 'https://api.vietqr.io/v2/citizen');
    }

    public function test_admin_can_lookup_citizen_information_realtime(): void
    {
        Http::fake([
            'https://api.vietqr.io/v2/citizen' => Http::response([
                'code' => '00',
                'desc' => 'Success - Thành công',
                'data' => [
                    'taxId' => '012345678901',
                    'name' => 'Nguyễn Văn A',
                ],
            ], 200),
        ]);

        $admin = $this->createAdminAccount();

        $this->actingAs($admin)
            ->postJson(route('admin.hoc-vien.lookup-citizen'), [
                'cccd' => '012345678901',
                'hoTen' => 'Nguyễn Văn A',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'matched',
                'data' => [
                    'legalId' => '012345678901',
                    'name' => 'Nguyễn Văn A',
                ],
            ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.vietqr.io/v2/citizen'
                && $request->hasHeader('x-client-id', 'test-client-id')
                && $request->hasHeader('x-api-key', 'test-api-key')
                && $request['legalId'] === '012345678901'
                && $request['legalName'] === 'NGUYEN VAN A';
        });
    }

    public function test_lookup_returns_mismatched_when_names_do_not_match(): void
    {
        Http::fake([
            'https://api.vietqr.io/v2/citizen' => Http::response([
                'code' => '00',
                'desc' => 'Success - Thành công',
                'data' => [
                    'taxId' => '012345678901',
                    'name' => 'Nguyễn Văn B',
                ],
            ], 200),
        ]);

        $admin = $this->createAdminAccount();

        $this->actingAs($admin)
            ->postJson(route('admin.hoc-vien.lookup-citizen'), [
                'cccd' => '012345678901',
                'hoTen' => 'Nguyễn Văn A',
            ])
            ->assertOk()
            ->assertJson([
                'success' => false,
                'status' => 'mismatched',
            ]);
    }

    public function test_lookup_validates_required_inputs(): void
    {
        Http::fake();
        $admin = $this->createAdminAccount();

        $this->actingAs($admin)
            ->postJson(route('admin.hoc-vien.lookup-citizen'), [
                'cccd' => '1234',
                'hoTen' => '',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cccd', 'hoTen']);
    }

    private function createMinimalAuthSchema(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('taikhoan');

        Schema::create('taikhoan', function (Blueprint $table) {
            $table->increments('taiKhoanId');
            $table->string('taiKhoan')->unique();
            $table->string('matKhau');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('role')->default(TaiKhoan::ROLE_ADMIN);
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

        Schema::enableForeignKeyConstraints();
    }

    private function createAdminAccount(): TaiKhoan
    {
        return TaiKhoan::query()->create([
            'taiKhoan' => 'AD' . fake()->unique()->numerify('######'),
            'matKhau' => bcrypt('password'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role' => TaiKhoan::ROLE_ADMIN,
            'trangThai' => 1,
            'phaiDoiMatKhau' => 0,
        ]);
    }
}
