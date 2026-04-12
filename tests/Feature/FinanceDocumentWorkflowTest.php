<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Mail\FinanceDocumentMail;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\TaiKhoan;
use App\Models\Finance\HoaDon;
use App\Models\Finance\PhieuThu;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinanceDocumentWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        $this->createFinanceSchema();
    }

    public function test_admin_can_create_receipt_and_prepare_immediate_print(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $invoice = $this->createInvoice($student, $admin, [
            'tongTien' => 1500000,
            'giamGia' => 100000,
            'daTra' => 0,
            'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.hoa-don.phieu-thu.store', $invoice->hoaDonId), [
            'soTien' => 400000,
            'ngayThu' => '2026-04-12',
            'phuongThucThanhToan' => 1,
            'ghiChu' => 'Thu đợt đầu',
            'afterAction' => 'print',
        ]);

        $response->assertRedirect(route('admin.hoa-don.show', $invoice->hoaDonId));

        $receipt = PhieuThu::query()->firstOrFail();

        $response->assertSessionHas('autoPrintReceiptId', $receipt->phieuThuId);
        $this->assertDatabaseHas('phieuthu', [
            'phieuThuId' => $receipt->phieuThuId,
            'hoaDonId' => $invoice->hoaDonId,
            'soTien' => 400000,
        ]);

        $invoice->refresh();
        $this->assertSame(400000.0, (float) $invoice->daTra);
        $this->assertSame(HoaDon::TRANG_THAI_MOT_PHAN, (int) $invoice->trangThai);

        $printResponse = $this->actingAs($admin)->get(route('admin.hoa-don.phieu-thu.print', $receipt->phieuThuId));
        $printResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $printResponse->headers->get('content-type'));
    }

    public function test_admin_can_email_invoice_pdf_attachment(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $invoice = $this->createInvoice($student, $admin);

        $this->actingAs($admin)
            ->post(route('admin.hoa-don.email', $invoice->hoaDonId), [
                'email' => 'family@example.com',
                'message' => 'Trung tâm gửi hóa đơn để đối chiếu.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Đã gửi email hóa đơn thành công.');

        Mail::assertSent(FinanceDocumentMail::class, function (FinanceDocumentMail $mail) {
            return $mail->hasTo('family@example.com')
                && $mail->documentType === 'invoice'
                && str_contains($mail->code, 'HD-');
        });
    }

    public function test_student_can_print_and_email_owned_finance_documents(): void
    {
        Mail::fake();

        $student = $this->createStudent('HV000002', 'student@example.com', 'Học viên In Mail');
        $admin = $this->createAdmin();
        $invoice = $this->createInvoice($student, $admin);
        $receipt = $this->createReceipt($invoice, $student, $admin);

        $printResponse = $this->actingAs($student)->get(route('home.student.tuition.receipts.print', $receipt->phieuThuId));
        $printResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $printResponse->headers->get('content-type'));

        $this->actingAs($student)
            ->post(route('home.student.invoices.email', $invoice->hoaDonId), [
                'email' => 'me@example.com',
                'message' => 'Vui lòng lưu giúp tôi.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Đã gửi email hóa đơn thành công.');

        Mail::assertSent(FinanceDocumentMail::class, function (FinanceDocumentMail $mail) {
            return $mail->hasTo('me@example.com')
                && $mail->documentType === 'invoice';
        });
    }

    private function createFinanceSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (['phieuthu', 'hoadon', 'dangKyLopHoc', 'hosonguoidung', 'taikhoan'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('taikhoan', function (Blueprint $table) {
            $table->increments('taiKhoanId');
            $table->string('taiKhoan')->nullable();
            $table->string('email')->nullable();
            $table->string('matKhau');
            $table->unsignedTinyInteger('role')->default(0);
            $table->unsignedInteger('nhomQuyenId')->nullable();
            $table->unsignedTinyInteger('trangThai')->default(1);
            $table->unsignedTinyInteger('phaiDoiMatKhau')->default(0);
            $table->string('auth_provider')->nullable();
            $table->string('google_id')->nullable();
            $table->string('google_avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamp('lastLogin')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hosonguoidung', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->string('hoTen')->nullable();
            $table->string('soDienThoai')->nullable();
            $table->string('cccd')->nullable();
            $table->string('anhDaiDien')->nullable();
            $table->text('diaChi')->nullable();
            $table->text('ghiChu')->nullable();
            $table->timestamps();
        });

        Schema::create('dangKyLopHoc', function (Blueprint $table) {
            $table->increments('dangKyLopHocId');
            $table->unsignedInteger('taiKhoanId')->nullable();
            $table->unsignedTinyInteger('trangThai')->default(0);
        });

        Schema::create('hoadon', function (Blueprint $table) {
            $table->increments('hoaDonId');
            $table->string('maHoaDon')->nullable()->unique();
            $table->date('ngayLap')->nullable();
            $table->date('ngayHetHan')->nullable();
            $table->decimal('tongTien', 15, 2)->default(0);
            $table->decimal('giamGia', 15, 2)->default(0);
            $table->decimal('thue', 15, 2)->default(0);
            $table->decimal('tongTienSauThue', 15, 2)->default(0);
            $table->decimal('daTra', 15, 2)->default(0);
            $table->unsignedInteger('taiKhoanId');
            $table->unsignedInteger('nguoiLapId')->nullable();
            $table->unsignedInteger('dangKyLopHocId')->nullable();
            $table->unsignedInteger('dangKyLopHocPhuPhiId')->nullable();
            $table->unsignedInteger('lopHocDotThuId')->nullable();
            $table->string('nguonThu')->default(HoaDon::NGUON_THU_HOC_PHI);
            $table->unsignedTinyInteger('phuongThucThanhToan')->nullable();
            $table->unsignedTinyInteger('loaiHoaDon')->default(HoaDon::LOAI_DANG_KY_MOI);
            $table->unsignedInteger('coSoId')->nullable();
            $table->unsignedTinyInteger('trangThai')->default(HoaDon::TRANG_THAI_CHUA_TT);
            $table->text('ghiChu')->nullable();
        });

        Schema::create('phieuthu', function (Blueprint $table) {
            $table->increments('phieuThuId');
            $table->string('maPhieuThu')->nullable()->unique();
            $table->unsignedInteger('hoaDonId');
            $table->decimal('soTien', 15, 2)->default(0);
            $table->date('ngayThu')->nullable();
            $table->unsignedTinyInteger('phuongThucThanhToan')->default(1);
            $table->unsignedInteger('taiKhoanId');
            $table->unsignedInteger('nguoiDuyetId')->nullable();
            $table->text('ghiChu')->nullable();
            $table->unsignedTinyInteger('trangThai')->default(PhieuThu::TRANG_THAI_HOP_LE);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    private function createAdmin(): TaiKhoan
    {
        return $this->createAccount(
            username: 'AD000001',
            email: 'admin@example.com',
            fullName: 'Admin Finance',
            role: TaiKhoan::ROLE_ADMIN,
        );
    }

    private function createStudent(
        string $username = 'HV000001',
        string $email = 'student-default@example.com',
        string $fullName = 'Học viên Test'
    ): TaiKhoan {
        return $this->createAccount(
            username: $username,
            email: $email,
            fullName: $fullName,
            role: TaiKhoan::ROLE_HOC_VIEN,
        );
    }

    private function createAccount(string $username, string $email, string $fullName, int $role): TaiKhoan
    {
        $account = TaiKhoan::create([
            'taiKhoan' => $username,
            'email' => $email,
            'matKhau' => Hash::make('secret'),
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
            'soDienThoai' => '0900000000',
            'cccd' => str_pad((string) $account->taiKhoanId, 12, '0', STR_PAD_LEFT),
        ]);

        return $account;
    }

    private function createInvoice(TaiKhoan $student, TaiKhoan $admin, array $overrides = []): HoaDon
    {
        return HoaDon::create([
            'maHoaDon' => HoaDon::generateMaHoaDon(),
            'ngayLap' => '2026-04-12',
            'ngayHetHan' => '2026-04-20',
            'tongTien' => 1200000,
            'giamGia' => 0,
            'thue' => 0,
            'tongTienSauThue' => 0,
            'daTra' => 0,
            'taiKhoanId' => $student->taiKhoanId,
            'nguoiLapId' => $admin->taiKhoanId,
            'nguonThu' => HoaDon::NGUON_THU_HOC_PHI,
            'phuongThucThanhToan' => 2,
            'loaiHoaDon' => HoaDon::LOAI_DANG_KY_MOI,
            'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
            'ghiChu' => 'Hóa đơn học phí kiểm thử',
            ...$overrides,
        ]);
    }

    private function createReceipt(HoaDon $invoice, TaiKhoan $student, TaiKhoan $admin): PhieuThu
    {
        $receipt = PhieuThu::create([
            'maPhieuThu' => PhieuThu::generateMaPhieuThu(),
            'hoaDonId' => $invoice->hoaDonId,
            'soTien' => 300000,
            'ngayThu' => '2026-04-12',
            'phuongThucThanhToan' => 2,
            'taiKhoanId' => $student->taiKhoanId,
            'nguoiDuyetId' => $admin->taiKhoanId,
            'ghiChu' => 'Phiếu thu kiểm thử',
            'trangThai' => PhieuThu::TRANG_THAI_HOP_LE,
        ]);

        $invoice->recalculate();

        return $receipt;
    }
}
