<?php

namespace Tests\Unit;

use App\Models\Auth\TaiKhoan;
use App\Services\Auth\DeviceSessionService;
use App\Services\Auth\LoginService;
use Mockery;
use Tests\TestCase;

class LoginServiceTest extends TestCase
{
    private LoginService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LoginService(
            Mockery::mock(DeviceSessionService::class)
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_student_login_view_data_uses_public_auth_routes_by_default(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
            'services.recaptcha.secret_key' => null,
        ]);

        $viewData = $this->service->getLoginViewData('student');

        $this->assertSame(route('login'), $viewData['submitRoute']);
        $this->assertSame(route('staff.login'), $viewData['alternateRoute']);
        $this->assertSame(route('teacher.login'), $viewData['secondaryAlternateRoute']);
        $this->assertSame(route('register'), $viewData['registerRoute']);
        $this->assertNull($viewData['googleRoute']);
        $this->assertFalse($viewData['recaptchaEnabled']);
    }

    public function test_student_login_view_data_exposes_google_and_recaptcha_when_configured(): void
    {
        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-client-secret',
            'services.recaptcha.secret_key' => 'recaptcha-secret',
        ]);

        $viewData = $this->service->getLoginViewData('student');

        $this->assertSame(route('auth.google.redirect'), $viewData['googleRoute']);
        $this->assertTrue($viewData['recaptchaEnabled']);
        $this->assertSame('student_login', $viewData['recaptchaAction']);
    }

    public function test_teacher_login_view_data_uses_teacher_portal_routes(): void
    {
        $viewData = $this->service->getLoginViewData('teacher');

        $this->assertSame(route('teacher.login.submit'), $viewData['submitRoute']);
        $this->assertSame(route('staff.login'), $viewData['alternateRoute']);
        $this->assertSame(route('login'), $viewData['secondaryAlternateRoute']);
        $this->assertNull($viewData['registerRoute']);
        $this->assertNull($viewData['googleRoute']);
        $this->assertFalse($viewData['recaptchaEnabled']);
    }

    public function test_matches_portal_only_accepts_expected_roles(): void
    {
        $student = new TaiKhoan(['role' => TaiKhoan::ROLE_HOC_VIEN]);
        $teacher = new TaiKhoan(['role' => TaiKhoan::ROLE_GIAO_VIEN]);
        $staff = new TaiKhoan(['role' => TaiKhoan::ROLE_NHAN_VIEN]);

        $this->assertTrue($this->service->matchesPortal($student, 'student'));
        $this->assertTrue($this->service->matchesPortal($teacher, 'teacher'));
        $this->assertTrue($this->service->matchesPortal($staff, 'staff'));

        $this->assertFalse($this->service->matchesPortal($teacher, 'student'));
        $this->assertFalse($this->service->matchesPortal($student, 'teacher'));
        $this->assertFalse($this->service->matchesPortal($student, 'staff'));
    }

    public function test_staff_dashboard_route_falls_back_to_admin_dashboard_when_specific_routes_are_missing(): void
    {
        $teacher = new TaiKhoan(['role' => TaiKhoan::ROLE_GIAO_VIEN]);
        $admin = new TaiKhoan(['role' => TaiKhoan::ROLE_ADMIN]);

        $this->assertSame('admin.dashboard', $this->service->staffDashboardRouteFor($teacher));
        $this->assertSame('admin.dashboard', $this->service->staffDashboardRouteFor($admin));
    }

    public function test_logout_redirect_route_matches_user_role(): void
    {
        $student = new TaiKhoan(['role' => TaiKhoan::ROLE_HOC_VIEN]);
        $teacher = new TaiKhoan(['role' => TaiKhoan::ROLE_GIAO_VIEN]);
        $staff = new TaiKhoan(['role' => TaiKhoan::ROLE_NHAN_VIEN]);
        $admin = new TaiKhoan(['role' => TaiKhoan::ROLE_ADMIN]);

        $this->assertSame('login', $this->service->logoutRedirectRouteFor($student));
        $this->assertSame('teacher.login', $this->service->logoutRedirectRouteFor($teacher));
        $this->assertSame('staff.login', $this->service->logoutRedirectRouteFor($staff));
        $this->assertSame('staff.login', $this->service->logoutRedirectRouteFor($admin));
    }
}
