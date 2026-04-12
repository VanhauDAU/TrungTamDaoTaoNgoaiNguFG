<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackAuthenticatedDeviceSession;
use App\Jobs\ProcessThongBaoDelivery;
use App\Mail\FinanceDocumentMail;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\TaiKhoan;
use App\Models\Finance\HoaDon;
use App\Models\Finance\PhieuThu;
use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoNguoiDung;
use App\Models\Interaction\ThongBaoTepDinh;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinanceDocumentWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TrackAuthenticatedDeviceSession::class);
        Queue::fake();
        Storage::fake('local');
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

        $notification = ThongBao::query()->first();
        $this->assertNotNull($notification);
        $this->assertSame(ThongBao::LOAI_TAI_CHINH, (int) $notification->loaiGui);
        $this->assertSame(ThongBao::DOI_TUONG_CA_NHAN, (int) $notification->doiTuongGui);
        $this->assertSame($student->taiKhoanId, (int) $notification->doiTuongId);

        $attachment = ThongBaoTepDinh::query()->first();
        $this->assertNotNull($attachment);
        $this->assertSame($notification->thongBaoId, (int) $attachment->thongBaoId);
        Storage::disk('local')->assertExists($attachment->duongDan);

        Queue::assertPushed(ProcessThongBaoDelivery::class, function (ProcessThongBaoDelivery $job) use ($notification, $admin) {
            return $job->thongBaoId === $notification->thongBaoId
                && $job->taiKhoanId === $admin->taiKhoanId
                && $job->source === 'receipt_created';
        });

        $printResponse = $this->actingAs($admin)->get(route('admin.hoa-don.phieu-thu.print', $receipt->phieuThuId));
        $printResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $printResponse->headers->get('content-type'));
    }

    public function test_each_receipt_must_be_at_least_twenty_five_percent_until_final_payment(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $invoice = $this->createInvoice($student, $admin, [
            'tongTien' => 1200000,
            'giamGia' => 0,
            'daTra' => 0,
            'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
        ]);

        $response = $this->from(route('admin.hoa-don.show', $invoice->hoaDonId))
            ->actingAs($admin)
            ->post(route('admin.hoa-don.phieu-thu.store', $invoice->hoaDonId), [
                'soTien' => 299999,
                'ngayThu' => '2026-04-12',
                'phuongThucThanhToan' => 1,
            ]);

        $response->assertRedirect(route('admin.hoa-don.show', $invoice->hoaDonId));
        $response->assertSessionHasErrors([
            'soTien' => 'Mỗi phiếu thu phải tối thiểu 300.000đ (25% giá trị phải thu).',
        ]);
        $this->assertDatabaseCount('phieuthu', 0);
    }

    public function test_follow_up_receipt_below_twenty_five_percent_is_rejected_when_balance_is_still_high(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $invoice = $this->createInvoice($student, $admin, [
            'tongTien' => 1200000,
            'giamGia' => 0,
            'daTra' => 0,
            'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
        ]);

        $this->createReceipt($invoice, $student, $admin);

        $response = $this->from(route('admin.hoa-don.show', $invoice->hoaDonId))
            ->actingAs($admin)
            ->post(route('admin.hoa-don.phieu-thu.store', $invoice->hoaDonId), [
            'soTien' => 50000,
            'ngayThu' => '2026-04-13',
            'phuongThucThanhToan' => 2,
            'ghiChu' => 'Thu bổ sung',
        ]);

        $response->assertRedirect(route('admin.hoa-don.show', $invoice->hoaDonId));
        $response->assertSessionHasErrors([
            'soTien' => 'Mỗi phiếu thu phải tối thiểu 300.000đ (25% giá trị phải thu).',
        ]);
        $this->assertDatabaseMissing('phieuthu', [
            'hoaDonId' => $invoice->hoaDonId,
            'soTien' => 50000,
        ]);

        $invoice->refresh();
        $this->assertSame(300000.0, (float) $invoice->daTra);
        $this->assertSame(HoaDon::TRANG_THAI_MOT_PHAN, (int) $invoice->trangThai);
    }

    public function test_final_receipt_must_collect_full_remaining_balance_when_below_twenty_five_percent(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $invoice = $this->createInvoice($student, $admin, [
            'tongTien' => 1200000,
            'giamGia' => 0,
            'daTra' => 0,
            'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
        ]);

        PhieuThu::create([
            'maPhieuThu' => PhieuThu::generateMaPhieuThu(),
            'hoaDonId' => $invoice->hoaDonId,
            'soTien' => 1000000,
            'ngayThu' => '2026-04-12',
            'phuongThucThanhToan' => 2,
            'taiKhoanId' => $student->taiKhoanId,
            'nguoiDuyetId' => $admin->taiKhoanId,
            'ghiChu' => 'Thu lớn ban đầu',
            'trangThai' => PhieuThu::TRANG_THAI_HOP_LE,
        ]);
        $invoice->recalculate();

        $invalidResponse = $this->from(route('admin.hoa-don.show', $invoice->hoaDonId))
            ->actingAs($admin)
            ->post(route('admin.hoa-don.phieu-thu.store', $invoice->hoaDonId), [
                'soTien' => 150000,
                'ngayThu' => '2026-04-13',
                'phuongThucThanhToan' => 2,
            ]);

        $invalidResponse->assertRedirect(route('admin.hoa-don.show', $invoice->hoaDonId));
        $invalidResponse->assertSessionHasErrors([
            'soTien' => 'Công nợ còn lại đã thấp hơn mức tối thiểu 25%, vui lòng thu đủ 200.000đ để tất toán.',
        ]);

        $validResponse = $this->actingAs($admin)->post(route('admin.hoa-don.phieu-thu.store', $invoice->hoaDonId), [
            'soTien' => 200000,
            'ngayThu' => '2026-04-13',
            'phuongThucThanhToan' => 2,
            'ghiChu' => 'Thu tất toán',
        ]);

        $validResponse->assertRedirect(route('admin.hoa-don.show', $invoice->hoaDonId));
        $validResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('phieuthu', [
            'hoaDonId' => $invoice->hoaDonId,
            'soTien' => 200000,
            'ghiChu' => 'Thu tất toán',
        ]);

        $invoice->refresh();
        $this->assertSame(1200000.0, (float) $invoice->daTra);
        $this->assertSame(HoaDon::TRANG_THAI_DA_TT, (int) $invoice->trangThai);
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

    public function test_student_portal_only_exposes_owned_receipt_print_and_download_routes(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('HV000002', 'student@example.com', 'Học viên In Mail');
        $otherStudent = $this->createStudent('HV000003', 'other-student@example.com', 'Học viên Khác');
        $invoice = $this->createInvoice($student, $admin);
        $receipt = $this->createReceipt($invoice, $student, $admin);

        $this->actingAs($student)
            ->get(route('home.student.tuition.receipts.print', $receipt->phieuThuId))
            ->assertOk();

        $this->actingAs($student)
            ->get(route('home.student.tuition.receipts.download', $receipt->phieuThuId))
            ->assertOk();

        $this->actingAs($otherStudent)
            ->get(route('home.student.tuition.receipts.print', $receipt->phieuThuId))
            ->assertNotFound();

        $this->actingAs($otherStudent)
            ->get(route('home.student.tuition.receipts.download', $receipt->phieuThuId))
            ->assertNotFound();

        $this->actingAs($student)->get('/hoc-vien/hoa-don/1/in')->assertNotFound();
        $this->actingAs($student)->post('/hoc-vien/hoa-don/1/gui-email')->assertNotFound();
        $this->actingAs($student)->post('/hoc-vien/hoc-phi/phieu-thu/1/gui-email')->assertNotFound();
    }

    public function test_student_can_download_owned_notification_attachment_but_other_student_cannot(): void
    {
        $student = $this->createStudent('HV000010', 'owner@example.com', 'Học viên Sở hữu');
        $otherStudent = $this->createStudent('HV000011', 'other@example.com', 'Học viên Khác');

        $notification = ThongBao::create([
            'tieuDe' => 'Phiếu thu mới',
            'noiDung' => 'Có phiếu thu đính kèm',
            'doiTuongGui' => ThongBao::DOI_TUONG_CA_NHAN,
            'doiTuongId' => $student->taiKhoanId,
            'trangThai' => 1,
            'loaiGui' => ThongBao::LOAI_TAI_CHINH,
            'uuTien' => ThongBao::UU_TIEN_BINH_THUONG,
            'ghim' => false,
            'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DA_GUI,
            'ngayGui' => now(),
            'sent_at' => now(),
        ]);

        ThongBaoNguoiDung::create([
            'thongBaoId' => $notification->thongBaoId,
            'taiKhoanId' => $student->taiKhoanId,
            'daDoc' => false,
        ]);

        Storage::disk('local')->put('finance/receipts/notifications/2026/04/test-receipt.pdf', 'pdf-content');

        $attachment = ThongBaoTepDinh::create([
            'thongBaoId' => $notification->thongBaoId,
            'tenFile' => 'phieu-thu-test.pdf',
            'tenFileLuu' => 'test-receipt.pdf',
            'duongDan' => 'finance/receipts/notifications/2026/04/test-receipt.pdf',
            'loaiFile' => 'application/pdf',
            'kichThuoc' => strlen('pdf-content'),
        ]);

        $this->actingAs($student)
            ->get(route('home.thong-bao.attachments.download', $attachment->tepDinhId))
            ->assertOk();

        $this->actingAs($otherStudent)
            ->get(route('home.thong-bao.attachments.download', $attachment->tepDinhId))
            ->assertNotFound();
    }

    private function createFinanceSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (['thongbao_tepdinh', 'thongbaonguoidung', 'thongbao', 'phieuthu', 'hoadon', 'dangKyLopHoc', 'hosonguoidung', 'taikhoan'] as $table) {
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

        Schema::create('thongbao', function (Blueprint $table) {
            $table->increments('thongBaoId');
            $table->string('tieuDe');
            $table->text('noiDung')->nullable();
            $table->unsignedInteger('nguoiGuiId')->nullable();
            $table->unsignedTinyInteger('doiTuongGui')->default(ThongBao::DOI_TUONG_CA_NHAN);
            $table->unsignedInteger('doiTuongId')->nullable();
            $table->timestamp('ngayGui')->nullable();
            $table->unsignedTinyInteger('trangThai')->default(1);
            $table->unsignedTinyInteger('loaiGui')->default(ThongBao::LOAI_HE_THONG);
            $table->unsignedTinyInteger('uuTien')->default(ThongBao::UU_TIEN_BINH_THUONG);
            $table->boolean('ghim')->default(false);
            $table->unsignedTinyInteger('sendTrangThai')->default(ThongBao::SEND_TRANG_THAI_NHAP);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('hinhAnh')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('thongbaonguoidung', function (Blueprint $table) {
            $table->increments('thongBaoNguoiDungId');
            $table->unsignedInteger('thongBaoId');
            $table->unsignedInteger('taiKhoanId');
            $table->boolean('daDoc')->default(false);
            $table->timestamp('ngayDoc')->nullable();
            $table->timestamps();
        });

        Schema::create('thongbao_tepdinh', function (Blueprint $table) {
            $table->increments('tepDinhId');
            $table->unsignedInteger('thongBaoId');
            $table->string('tenFile');
            $table->string('tenFileLuu');
            $table->string('duongDan');
            $table->string('loaiFile')->nullable();
            $table->unsignedBigInteger('kichThuoc')->default(0);
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
