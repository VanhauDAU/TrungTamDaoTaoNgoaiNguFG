<?php

use App\Http\Controllers\Client\Blog\BlogController;
use App\Http\Controllers\Client\LienHe\ContactController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\Blog\AboutController;
use App\Http\Controllers\Client\KhoaHoc\CourseController;
use App\Http\Controllers\Client\HocVien\StudentController;
use App\Http\Controllers\Client\Chat\ClientChatController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\CauHinhController;
use App\Http\Controllers\Admin\NhomQuyenController;
use App\Http\Controllers\Admin\HocVien\HocVienController as AdminHocVienController;
use App\Http\Controllers\Admin\HocVien\DangKyHocController as AdminDangKyHocController;
use App\Http\Controllers\Admin\GiaoVien\GiaoVienController as AdminGiaoVienController;
use App\Http\Controllers\Admin\NhanVien\NhanVienController as AdminNhanVienController;
use App\Http\Controllers\Admin\NhanVien\NhanSuMauQuyDinhController;
use App\Http\Controllers\Admin\Auth\TaiKhoanController;
use App\Http\Controllers\Internal\LienHe\LienHeController as InternalLienHeController;
use App\Http\Controllers\Admin\CoSo\CoSoController;
use App\Http\Controllers\Admin\CoSo\PhongHocController;
use App\Http\Controllers\Admin\KhoaHoc\KhoaHocController as AdminKhoaHocController;
use App\Http\Controllers\Admin\KhoaHoc\LopHocController as AdminLopHocController;
use App\Http\Controllers\Admin\KhoaHoc\BuoiHocController as AdminBuoiHocController;
use App\Http\Controllers\Admin\KhoaHoc\CaHocController as AdminCaHocController;
use App\Http\Controllers\Admin\KhoaHoc\DanhMucKhoaHocController as AdminDanhMucKhoaHocController;
use App\Http\Controllers\Admin\BaiViet\BaiVietController as AdminBaiVietController;
use App\Http\Controllers\Admin\BaiViet\DanhMucBaiVietController as AdminDanhMucBaiVietController;
use App\Http\Controllers\Admin\BaiViet\TagController as AdminTagController;
use App\Http\Controllers\Admin\ThongBao\ThongBaoController as AdminThongBaoController;
use App\Http\Controllers\Admin\TaiChinh\HoaDonController as AdminHoaDonController;
use App\Http\Controllers\Client\ThongBao\ClientThongBaoController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\HocVien\DangKyHocController as StaffDangKyHocController;
use App\Http\Controllers\Staff\HocVien\HocVienController as StaffHocVienController;
use App\Http\Controllers\Staff\KhoaHoc\BuoiHocController as StaffBuoiHocController;
use App\Http\Controllers\Staff\KhoaHoc\LopHocController as StaffLopHocController;
use App\Http\Controllers\Staff\TaiChinh\HoaDonController as StaffHoaDonController;
use App\Http\Controllers\Staff\ThongBao\ThongBaoController as StaffThongBaoController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\DiemDanh\DiemDanhController as TeacherDiemDanhController;
use App\Http\Controllers\Teacher\LichDay\LichDayController as TeacherLichDayController;
use App\Http\Controllers\Teacher\LopHoc\LopHocController as TeacherLopHocController;
use App\Http\Controllers\Teacher\NhanXet\NhanXetController as TeacherNhanXetController;
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;
use App\Http\Controllers\Teacher\TaiLieu\TaiLieuController as TeacherTaiLieuController;
use App\Http\Controllers\Teacher\ThongBao\ThongBaoController as TeacherThongBaoController;
use App\Http\Controllers\Upload\ImageUploadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ─── API ROUTES (Public) ────────────────────────────────────────────────────
// Note: API bảo mật
Route::prefix('api')->name('api.')->group(function () {
    // Lấy danh sách phường/xã theo tỉnh (từ provinces.open-api.vn proxy)
    Route::get('/phuong-xa/{maTinh}', [CoSoController::class, 'getPhuongXa'])->name('phuongxa');
    // Danh sách cơ sở có filter (dùng cho client contact page)
    Route::get('/co-so', [CoSoController::class, 'apiList'])->name('coso');
    // Phòng học theo cơ sở (dùng cho form lớp học)
    Route::get('/phong-hoc/{coSoId}', [AdminLopHocController::class, 'getPhongByCoso'])->name('phong-hoc-by-coso');
    // Giáo viên theo cơ sở
    Route::get('/giao-vien/{coSoId}', [AdminLopHocController::class, 'getGiaoVienByCoso'])->name('giao-vien-by-coso');
});

Route::prefix('api/uploads')->name('api.uploads.')->middleware('auth')->group(function () {
    Route::post('/images', [ImageUploadController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('images.store');
});

// ─── CLIENT ROUTES ──────────────────────────────────────────────────────────
Route::prefix('/')->name('home.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('index');
    Route::prefix('lien-he')->name('contact.')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('index');
        Route::post('/tu-van', [ContactController::class, 'storeConsultation'])->name('consultation.store');
    });
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
    });
    Route::prefix('ve-chung-toi')->name('about.')->group(function () {
        Route::get('/', [AboutController::class, 'index'])->name('index');
    });
    Route::prefix('khoa-hoc')->name('courses.')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/{slug}', [CourseController::class, 'show'])->name('show');
    });
    Route::prefix('lop-hoc')->name('classes.')->group(function () {
        Route::get('/{slug}/{slugLopHoc}', [CourseController::class, 'showClass'])->name('show');
        Route::get('/{slug}/{slugLopHoc}/dang-ky', [CourseController::class, 'confirmRegistration'])->name('confirm');
        Route::post('/{slug}/{slugLopHoc}/xac-nhan-dang-ky', [CourseController::class, 'processRegistration'])->name('process');
    });
    Route::prefix('hoc-vien')->name('student.')->middleware(['auth', 'verified.student'])->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::post('/', [StudentController::class, 'updateProfile'])->name('update-profile');
        Route::post('/anh-dai-dien', [StudentController::class, 'updateAvatar'])
            ->middleware('throttle:5,1')   // tối đa 5 lần upload/phút/người dùng
            ->name('update-avatar');
        Route::get('/thiet-bi-dang-nhap', [StudentController::class, 'devices'])->name('devices');
        Route::post('/thiet-bi-dang-nhap/dang-xuat-tat-ca', [StudentController::class, 'logoutAllDevices'])->name('devices.logout-all');
        Route::post('/thiet-bi-dang-nhap/{sessionId}/dang-xuat', [StudentController::class, 'revokeDeviceSession'])->name('devices.logout');
        Route::post('/thiet-lap-mat-khau', [StudentController::class, 'sendPasswordSetupLink'])->name('setup-password');
        Route::get('/doi-mat-khau', [StudentController::class, 'changePassword'])->name('change-password');
        Route::post('/doi-mat-khau', [StudentController::class, 'updatePassword'])->name('update-password');
        Route::get('/hoa-don', [StudentController::class, 'invoices'])->name('invoices');
        Route::get('/hoa-don/{id}', [StudentController::class, 'invoiceDetail'])->name('invoices.show');
        Route::prefix('hoc-phi')->name('tuition.')->group(function () {
            Route::get('/', [StudentController::class, 'tuitionIndex'])->name('index');
            Route::get('/cong-no', [StudentController::class, 'tuitionDebts'])->name('debts');
            Route::get('/phieu-thu', [StudentController::class, 'tuitionReceipts'])->name('receipts');
            Route::get('/phieu-thu/{id}/in', [StudentController::class, 'printReceipt'])->name('receipts.print');
            Route::get('/phieu-thu/{id}/tai-xuong', [StudentController::class, 'downloadReceipt'])->name('receipts.download');
            Route::get('/thanh-toan-truc-tuyen', [StudentController::class, 'tuitionPayments'])->name('payments');
            Route::get('/hoa-don/{id}', [StudentController::class, 'invoiceDetail'])->name('invoices.show');
        });
        Route::get('/lop-hoc', [StudentController::class, 'myClasses'])->name('classes');
        Route::get('/lich-hoc', [StudentController::class, 'schedule'])->name('schedule');
        Route::get('/chat', [ClientChatController::class, 'index'])->name('chat');
    });

    // ── Thông báo client (auth required) ────────────────────────────────────
    Route::prefix('thong-bao')->name('thong-bao.')->middleware(['auth', 'verified.student'])->group(function () {
        Route::get('/', [ClientThongBaoController::class, 'index'])->name('index');
        Route::get('/tep-dinh/{id}/tai-xuong', [ClientThongBaoController::class, 'downloadAttachment'])->name('attachments.download');
    });

    // ── Thông báo client API (auth, JSON) ────────────────────────────────────
    Route::prefix('api/thong-bao')->name('api.thong-bao.')->middleware(['auth', 'verified.student'])->group(function () {
        Route::get('/stream', [ClientThongBaoController::class, 'stream'])->name('stream');
        Route::get('/dropdown', [ClientThongBaoController::class, 'getDropdown'])->name('dropdown');
        Route::get('/chua-doc', [ClientThongBaoController::class, 'getUnreadCount'])->name('unread-count');
        Route::patch('/{id}/da-doc', [ClientThongBaoController::class, 'markRead'])->name('mark-read');
        Route::patch('/{id}/chua-doc', [ClientThongBaoController::class, 'markUnread'])->name('mark-unread');
        Route::patch('/da-doc-tat-ca', [ClientThongBaoController::class, 'markAllRead'])->name('mark-all-read');
    });

    Route::prefix('api/chat')->name('api.chat.')->middleware(['auth', 'verified.student'])->group(function () {
        Route::get('/poll', [ClientChatController::class, 'poll'])->name('poll');
        Route::get('/attachments/{id}/view', [ClientChatController::class, 'viewAttachment'])->name('attachments.view');
        Route::get('/attachments/{id}/download', [ClientChatController::class, 'downloadAttachment'])->name('attachments.download');
        Route::get('/rooms', [ClientChatController::class, 'rooms'])->name('rooms');
        Route::get('/rooms/{id}/messages', [ClientChatController::class, 'messages'])->name('messages');
        Route::get('/rooms/{id}/members', [ClientChatController::class, 'members'])->name('members');
        Route::get('/rooms/{id}/search', [ClientChatController::class, 'search'])->name('search');
        Route::post('/rooms/{id}/join', [ClientChatController::class, 'join'])->name('join');
        Route::delete('/rooms/{id}/leave', [ClientChatController::class, 'leave'])->name('leave');
        Route::post('/rooms/{id}/typing', [ClientChatController::class, 'typing'])->name('typing');
        Route::post('/rooms/direct', [ClientChatController::class, 'direct'])->name('direct');
        Route::post('/rooms/{id}/read', [ClientChatController::class, 'markRead'])->name('read');
        Route::post('/messages', [ClientChatController::class, 'send'])->name('send');
        Route::post('/messages/{id}/recall', [ClientChatController::class, 'recall'])->name('recall');
        Route::post('/messages/{id}/react', [ClientChatController::class, 'react'])->name('react');
        Route::post('/messages/{id}/delete-for-me', [ClientChatController::class, 'deleteForMe'])->name('delete-for-me');
    });

});

// ─── TEACHER ROUTES ──────────────────────────────────────────────────────────
Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'portal:teacher'])->group(function () {
    Route::get('/dashboard', TeacherDashboardController::class)->name('dashboard');
    Route::get('/ho-so', TeacherProfileController::class)->name('profile');

    Route::prefix('lop-hoc-cua-toi')->name('classes.')->group(function () {
        Route::get('/', [TeacherLopHocController::class, 'index'])->name('index');
        Route::get('/{slug}', [TeacherLopHocController::class, 'show'])->name('show');
    });

    Route::prefix('lich-day')->name('schedule.')->group(function () {
        Route::get('/', [TeacherLichDayController::class, 'index'])->name('index');

        // ── Đề xuất (proposals) ───────────────────────────────────────────────
        Route::post('/de-xuat/day-bu/{buoiHocId}', [TeacherLichDayController::class, 'proposeCompensation'])
            ->name('propose.compensation');
        Route::post('/de-xuat/tam-ngung/{buoiHocId}', [TeacherLichDayController::class, 'proposeSuspension'])
            ->name('propose.suspension');
        Route::post('/de-xuat/doi-lich/{buoiHocId}', [TeacherLichDayController::class, 'proposeReschedule'])
            ->name('propose.reschedule');
    });

    Route::prefix('thong-bao')->name('notifications.')->group(function () {
        Route::get('/', [TeacherThongBaoController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminThongBaoController::class, 'create'])->name('create');
        Route::post('/', [AdminThongBaoController::class, 'store'])->name('store');
    });

    Route::prefix('tai-lieu')->name('materials.')->group(function () {
        Route::get('/', [TeacherTaiLieuController::class, 'index'])->name('index');
    });

    Route::prefix('nhan-xet')->name('evaluations.')->group(function () {
        Route::get('/', [TeacherNhanXetController::class, 'index'])->name('index');
    });

    Route::prefix('diem-danh')->name('attendance.')->group(function () {
        Route::get('/', [TeacherDiemDanhController::class, 'index'])->name('index');
        Route::get('/classes', [TeacherDiemDanhController::class, 'classes'])->name('classes');
        Route::get('/sessions', [TeacherDiemDanhController::class, 'sessions'])->name('sessions');
        Route::post('/{buoiHocId}', [TeacherDiemDanhController::class, 'store'])->name('store');
        Route::get('/{buoiHocId}/xuat-danh-sach', [TeacherDiemDanhController::class, 'export'])->name('export');
    });

    Route::prefix('api/notifications')->name('api.notifications.')->group(function () {
        Route::get('/recipients', [AdminThongBaoController::class, 'getRecipients'])->name('recipients');
        Route::get('/dropdown', [AdminThongBaoController::class, 'getDropdown'])->name('dropdown');
        Route::get('/unread-count', [AdminThongBaoController::class, 'getUnreadCount'])->name('unread-count');
        Route::patch('/mark-all-read', [AdminThongBaoController::class, 'markAllRead'])->name('mark-all-read');
        Route::patch('/{id}/mark-read', [AdminThongBaoController::class, 'markAsRead'])->name('mark-read');
        Route::patch('/{id}/mark-unread', [AdminThongBaoController::class, 'markAsUnread'])->name('mark-unread');
    });
});

// ─── STAFF ROUTES ────────────────────────────────────────────────────────────
Route::prefix('staff')->name('staff.')->middleware(['auth', 'portal:staff'])->group(function () {
    Route::get('/dashboard', StaffDashboardController::class)->name('dashboard');

    Route::prefix('hoc-vien')->name('hoc-vien.')->group(function () {
        Route::get('/', [StaffHocVienController::class, 'index'])->name('index');
        Route::get('/xuat-excel', [StaffHocVienController::class, 'export'])->name('export');
        Route::post('/tra-cuu-cccd', [StaffHocVienController::class, 'lookupCitizen'])
            ->middleware('throttle:30,1')
            ->name('lookup-citizen');
        Route::get('/tao-moi', [StaffHocVienController::class, 'create'])->name('create');
        Route::post('/', [StaffHocVienController::class, 'store'])->name('store');
        Route::get('/thung-rac', [StaffHocVienController::class, 'trash'])->name('trash');
        Route::patch('/{id}/khoi-phuc', [StaffHocVienController::class, 'restore'])->name('restore');
        Route::get('/{taiKhoan}/sua', [StaffHocVienController::class, 'edit'])->name('edit');
        Route::put('/{taiKhoan}', [StaffHocVienController::class, 'update'])->name('update');
        Route::delete('/{taiKhoan}', [StaffHocVienController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('dang-ky')->name('dang-ky.')->group(function () {
        Route::get('/', [StaffDangKyHocController::class, 'index'])->name('index');
        Route::get('/tao-moi', [StaffDangKyHocController::class, 'create'])->name('create');
        Route::post('/', [StaffDangKyHocController::class, 'store'])->name('store');
        Route::patch('/{id}/xac-nhan', [StaffDangKyHocController::class, 'confirm'])->name('confirm');
        Route::patch('/{id}/huy', [StaffDangKyHocController::class, 'cancel'])->name('cancel');
        Route::patch('/{id}/bao-luu', [StaffDangKyHocController::class, 'hold'])->name('hold');
        Route::patch('/{id}/khoi-phuc', [StaffDangKyHocController::class, 'restore'])->name('restore');
        Route::patch('/{id}/chuyen-lop', [StaffDangKyHocController::class, 'transfer'])->name('transfer');
    });

    Route::prefix('lop-hoc')->name('lop-hoc.')->group(function () {
        Route::get('/', [StaffLopHocController::class, 'index'])->name('index');
        Route::get('/thung-rac', [StaffLopHocController::class, 'trash'])->name('trash');
        Route::get('/tao-moi', [StaffLopHocController::class, 'create'])->name('create');
        Route::post('/', [StaffLopHocController::class, 'store'])->name('store');
        Route::get('/kiem-tra-xung-dot', [StaffLopHocController::class, 'previewSchedulingConflicts'])->name('preview-conflicts');
        Route::get('/{slug}/hoc-vien-goi-y', [StaffLopHocController::class, 'searchStudents'])->name('search-students');
        Route::post('/{slug}/hoc-vien', [StaffLopHocController::class, 'quickAddStudents'])->name('quick-add-students');
        Route::post('/{slug}/hoc-vien-moi', [StaffLopHocController::class, 'createStudentAndEnroll'])->name('create-student-and-enroll');
        Route::post('/{slug}/len-lop', [StaffLopHocController::class, 'promoteStudents'])->name('promote-students');
        Route::patch('/{slug}/trang-thai', [StaffLopHocController::class, 'updateStatus'])->name('update-status');
        Route::get('/{slug}', [StaffLopHocController::class, 'show'])->name('show');
        Route::get('/{slug}/sua', [StaffLopHocController::class, 'edit'])->name('edit');
        Route::put('/{slug}', [StaffLopHocController::class, 'update'])->name('update');
        Route::delete('/{slug}', [StaffLopHocController::class, 'destroy'])->name('destroy');
        Route::patch('/{slug}/khoi-phuc', [StaffLopHocController::class, 'restore'])->name('restore');
    });

    Route::prefix('buoi-hoc')->name('buoi-hoc.')->group(function () {
        Route::post('/', [StaffBuoiHocController::class, 'store'])->name('store');
        Route::put('/{id}', [StaffBuoiHocController::class, 'update'])->name('update');
        Route::delete('/{id}', [StaffBuoiHocController::class, 'destroy'])->name('destroy');
        Route::post('/tu-dong-tao/{lopHocId}', [StaffBuoiHocController::class, 'autoGenerate'])->name('auto-generate');
    });

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/phuong-xa-co-so/{tinhThanhId}', [CoSoController::class, 'getPhuongXaCoCoSo'])->name('phuong-xa-co-so');
        Route::get('/co-so-by-location', [CoSoController::class, 'getCoSoByLocation'])->name('co-so-by-location');
    });

    Route::prefix('hoa-don')->name('hoa-don.')->group(function () {
        Route::get('/', [StaffHoaDonController::class, 'index'])->name('index');
        Route::get('/tra-cuu-cong-no', [StaffHoaDonController::class, 'debtLookup'])->name('debt-lookup');
        Route::post('/tra-cuu-cong-no/thanh-toan', [StaffHoaDonController::class, 'settleAllDebts'])->name('debt-lookup.settle');
        Route::get('/phieu-thu/{id}/in', [StaffHoaDonController::class, 'printReceipt'])->name('phieu-thu.print');
        Route::post('/phieu-thu/{id}/gui-email', [StaffHoaDonController::class, 'emailReceipt'])->name('phieu-thu.email');
        Route::get('/{id}', [StaffHoaDonController::class, 'show'])->name('show');
        Route::get('/{id}/in', [StaffHoaDonController::class, 'printInvoice'])->name('print');
        Route::post('/{id}/gui-email', [StaffHoaDonController::class, 'emailInvoice'])->name('email');
        Route::put('/{id}', [StaffHoaDonController::class, 'update'])->name('update');
        Route::post('/{id}/phieu-thu', [StaffHoaDonController::class, 'storePhieuThu'])->name('phieu-thu.store');
        Route::delete('/phieu-thu/{id}', [StaffHoaDonController::class, 'destroyPhieuThu'])->name('phieu-thu.destroy');
    });

    Route::prefix('lien-he')->name('lien-he.')->group(function () {
        Route::get('/', [InternalLienHeController::class, 'index'])->name('index');
        Route::get('/thung-rac', [InternalLienHeController::class, 'trash'])->name('trash');
        Route::delete('/bulk/xoa', [InternalLienHeController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::patch('/bulk/trang-thai', [InternalLienHeController::class, 'bulkUpdateStatus'])->name('bulk-status');
        Route::patch('/{id}/khoi-phuc', [InternalLienHeController::class, 'restore'])->name('restore');
        Route::get('/{id}', [InternalLienHeController::class, 'show'])->name('show');
        Route::put('/{id}', [InternalLienHeController::class, 'update'])->name('update');
        Route::delete('/{id}', [InternalLienHeController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/phan-hoi', [InternalLienHeController::class, 'storeReply'])->name('reply.store');
        Route::patch('/{id}/gan-phu-trach', [InternalLienHeController::class, 'assign'])->name('assign');
    });

    Route::prefix('bai-viet')->name('bai-viet.')->group(function () {
        Route::get('/', [AdminBaiVietController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminBaiVietController::class, 'create'])->name('create');
        Route::post('/', [AdminBaiVietController::class, 'store'])->name('store');
        Route::get('/thung-rac', [AdminBaiVietController::class, 'trash'])->name('trash');
        Route::post('/xoa-nhieu', [AdminBaiVietController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/khoi-phuc-nhieu', [AdminBaiVietController::class, 'bulkRestore'])->name('bulk-restore');
        Route::get('/{id}', [AdminBaiVietController::class, 'show'])->name('show');
        Route::get('/{id}/sua', [AdminBaiVietController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminBaiVietController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminBaiVietController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [AdminBaiVietController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/upload-image', [AdminBaiVietController::class, 'uploadImage'])->name('upload-image');
        Route::post('/{id}/khoi-phuc', [AdminBaiVietController::class, 'restore'])->name('restore');
        Route::delete('/{id}/xoa-vinh-vien', [AdminBaiVietController::class, 'forceDestroy'])->name('force-destroy');
    });

    Route::prefix('danh-muc-bai-viet')->name('danh-muc-bai-viet.')->group(function () {
        Route::get('/', [AdminDanhMucBaiVietController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminDanhMucBaiVietController::class, 'create'])->name('create');
        Route::post('/', [AdminDanhMucBaiVietController::class, 'store'])->name('store');
        Route::get('/{id}/sua', [AdminDanhMucBaiVietController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminDanhMucBaiVietController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminDanhMucBaiVietController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('thong-bao')->name('notifications.')->group(function () {
        Route::get('/', [StaffThongBaoController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminThongBaoController::class, 'create'])->name('create');
        Route::post('/', [AdminThongBaoController::class, 'store'])->name('store');
    });

    Route::prefix('api/tags')->name('api.tags.')->group(function () {
        Route::get('/', [AdminTagController::class, 'index'])->name('index');
        Route::post('/', [AdminTagController::class, 'store'])->name('store');
        Route::delete('/{id}', [AdminTagController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('api/notifications')->name('api.notifications.')->group(function () {
        Route::get('/recipients', [AdminThongBaoController::class, 'getRecipients'])->name('recipients');
        Route::get('/dropdown', [AdminThongBaoController::class, 'getDropdown'])->name('dropdown');
        Route::get('/unread-count', [AdminThongBaoController::class, 'getUnreadCount'])->name('unread-count');
        Route::patch('/mark-all-read', [AdminThongBaoController::class, 'markAllRead'])->name('mark-all-read');
        Route::patch('/{id}/mark-read', [AdminThongBaoController::class, 'markAsRead'])->name('mark-read');
        Route::patch('/{id}/mark-unread', [AdminThongBaoController::class, 'markAsUnread'])->name('mark-unread');
    });
});

// ─── LEGACY ADMIN REDIRECTS FOR STAFF MODULES ───────────────────────────────
Route::prefix('admin')->middleware(['auth', 'portal:staff'])->group(function () {
    $legacyModules = ['hoc-vien', 'dang-ky', 'lop-hoc', 'buoi-hoc', 'hoa-don'];

    foreach ($legacyModules as $module) {
        Route::get("/{$module}/{path?}", function (?string $path = null) use ($module) {
            $target = '/staff/' . $module . ($path ? '/' . $path : '');
            $query = request()->getQueryString();

            return redirect()->to($query ? $target . '?' . $query : $target);
        })->where('path', '.*');
    }
});

// ─── ADMIN ROUTES ────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'portal:admin'])->group(function () {
    Route::get('/dashboard', [AdminHomeController::class, 'index'])->name('dashboard');

    // ── Cấu hình hệ thống ────────────────────────────────────────
    Route::prefix('cau-hinh')->name('cau-hinh.')->group(function () {
        Route::get('/', [CauHinhController::class, 'index'])->name('index');
        Route::post('/', [CauHinhController::class, 'update'])->name('update');
        Route::post('/reset', [CauHinhController::class, 'reset'])->name('reset');
    });

    // ── Phân quyền (chỉ Admin role=3 mới vào được) ──────────────────────────
    Route::prefix('phan-quyen')->name('phan-quyen.')->group(function () {
        Route::get('/', [NhomQuyenController::class, 'index'])->name('index');
        Route::get('/tao-moi', [NhomQuyenController::class, 'create'])->name('create');
        Route::post('/', [NhomQuyenController::class, 'store'])->name('store');
        Route::get('/{id}/sua', [NhomQuyenController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NhomQuyenController::class, 'update'])->name('update');
        Route::delete('/{id}', [NhomQuyenController::class, 'destroy'])->name('destroy');
    });

    // ── Danh sách Tài khoản Hệ thống ──────────────────────────────────────────
    Route::prefix('tai-khoan')->name('tai-khoan.')->group(function () {
        Route::get('/', [TaiKhoanController::class, 'index'])->name('index');
        Route::post('/{id}/nhom-quyen', [TaiKhoanController::class, 'updateNhomQuyen'])->name('update-nhom-quyen');
        Route::post('/{id}/toggle-status', [TaiKhoanController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/reset-password', [TaiKhoanController::class, 'resetPassword'])->name('reset-password');
    });

    // ── Giáo viên ────────────────────────────────────────────────────────────
    Route::prefix('giao-vien')->name('giao-vien.')->group(function () {
        Route::get('/', [AdminGiaoVienController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminGiaoVienController::class, 'create'])->name('create');
        Route::post('/', [AdminGiaoVienController::class, 'store'])->name('store');
        Route::get('/thung-rac', [AdminGiaoVienController::class, 'trash'])->name('trash');
        Route::patch('/{id}/khoi-phuc', [AdminGiaoVienController::class, 'restore'])->name('restore');
        Route::get('/{taiKhoan}', [AdminGiaoVienController::class, 'show'])->name('show');
        Route::post('/{taiKhoan}/anh-dai-dien', [AdminGiaoVienController::class, 'updateAvatar'])->name('avatar.update');
        Route::post('/{taiKhoan}/tai-lieu', [AdminGiaoVienController::class, 'storeDocument'])->name('documents.store');
        Route::get('/{taiKhoan}/tai-lieu/{documentId}/tai-xuong', [AdminGiaoVienController::class, 'downloadDocument'])->name('documents.download');
        Route::patch('/{taiKhoan}/tai-lieu/{documentId}/luu-tru', [AdminGiaoVienController::class, 'archiveDocument'])->name('documents.archive');
        Route::post('/{taiKhoan}/goi-luong', [AdminGiaoVienController::class, 'storeSalaryPackage'])->name('salary.store');
        Route::get('/{taiKhoan}/ho-so.pdf', [AdminGiaoVienController::class, 'downloadProfilePdf'])->name('profile.pdf');
        Route::get('/{taiKhoan}/ban-giao-tai-khoan.pdf', [AdminGiaoVienController::class, 'downloadHandoverPdf'])->name('handover.pdf');
        Route::get('/{taiKhoan}/sua', [AdminGiaoVienController::class, 'edit'])->name('edit');
        Route::put('/{taiKhoan}', [AdminGiaoVienController::class, 'update'])->name('update');
        Route::delete('/{taiKhoan}', [AdminGiaoVienController::class, 'destroy'])->name('destroy');
    });

    // ── Nhân viên ─────────────────────────────────────────────────────────────
    Route::prefix('nhan-vien')->name('nhan-vien.')->group(function () {
        Route::get('/', [AdminNhanVienController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminNhanVienController::class, 'create'])->name('create');
        Route::post('/', [AdminNhanVienController::class, 'store'])->name('store');
        Route::get('/thung-rac', [AdminNhanVienController::class, 'trash'])->name('trash');
        Route::patch('/{id}/khoi-phuc', [AdminNhanVienController::class, 'restore'])->name('restore');
        Route::get('/{taiKhoan}', [AdminNhanVienController::class, 'show'])->name('show');
        Route::post('/{taiKhoan}/anh-dai-dien', [AdminNhanVienController::class, 'updateAvatar'])->name('avatar.update');
        Route::post('/{taiKhoan}/tai-lieu', [AdminNhanVienController::class, 'storeDocument'])->name('documents.store');
        Route::get('/{taiKhoan}/tai-lieu/{documentId}/tai-xuong', [AdminNhanVienController::class, 'downloadDocument'])->name('documents.download');
        Route::patch('/{taiKhoan}/tai-lieu/{documentId}/luu-tru', [AdminNhanVienController::class, 'archiveDocument'])->name('documents.archive');
        Route::post('/{taiKhoan}/goi-luong', [AdminNhanVienController::class, 'storeSalaryPackage'])->name('salary.store');
        Route::get('/{taiKhoan}/ho-so.pdf', [AdminNhanVienController::class, 'downloadProfilePdf'])->name('profile.pdf');
        Route::get('/{taiKhoan}/ban-giao-tai-khoan.pdf', [AdminNhanVienController::class, 'downloadHandoverPdf'])->name('handover.pdf');
        Route::get('/{taiKhoan}/sua', [AdminNhanVienController::class, 'edit'])->name('edit');
        Route::put('/{taiKhoan}', [AdminNhanVienController::class, 'update'])->name('update');
        Route::delete('/{taiKhoan}', [AdminNhanVienController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('nhan-su/mau-quy-dinh')->name('nhan-su.mau-quy-dinh.')->group(function () {
        Route::get('/', [NhanSuMauQuyDinhController::class, 'index'])->name('index');
        Route::get('/tao-moi', [NhanSuMauQuyDinhController::class, 'create'])->name('create');
        Route::post('/', [NhanSuMauQuyDinhController::class, 'store'])->name('store');
        Route::get('/{id}/sua', [NhanSuMauQuyDinhController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NhanSuMauQuyDinhController::class, 'update'])->name('update');
        Route::delete('/{id}', [NhanSuMauQuyDinhController::class, 'destroy'])->name('destroy');
    });


    // ── Liên Hệ (CRM) ───────────────────────────────────────────────────────────
    Route::prefix('lien-he')->name('lien-he.')->group(function () {
        Route::get('/', [InternalLienHeController::class, 'index'])->name('index');
        Route::get('/thung-rac', [InternalLienHeController::class, 'trash'])->name('trash');
        Route::get('/{id}', [InternalLienHeController::class, 'show'])->name('show');
    });

    // ── Cơ sở Đào tạo ────────────────────────────────────────────────────────
    Route::prefix('co-so')->name('co-so.')->group(function () {
        Route::get('/', [CoSoController::class, 'index'])->name('index');
        Route::get('/tao-moi', [CoSoController::class, 'create'])->name('create');
        Route::post('/', [CoSoController::class, 'store'])->name('store');
        Route::get('/{id}/van-hanh', [CoSoController::class, 'operationalSnapshot'])->name('operational-snapshot');
        Route::get('/{id}', [CoSoController::class, 'show'])->name('show');
        Route::get('/{id}/sua', [CoSoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CoSoController::class, 'update'])->name('update');
        Route::delete('/{id}', [CoSoController::class, 'destroy'])->name('destroy');
    });

    // ── API: Cascading location (Tỉnh → Phường → Cơ sở) ──────────────────────
    Route::get('/api/phuong-xa-co-so/{tinhThanhId}', [CoSoController::class, 'getPhuongXaCoCoSo'])->name('api.phuong-xa-co-so');
    Route::get('/api/co-so-by-location', [CoSoController::class, 'getCoSoByLocation'])->name('api.co-so-by-location');

    // ── Phòng Học ─────────────────────────────────────────────────────────────
    Route::prefix('phong-hoc')->name('phong-hoc.')->group(function () {
        Route::post('/', [PhongHocController::class, 'store'])->name('store');
        Route::put('/{id}', [PhongHocController::class, 'update'])->name('update');
        Route::delete('/{id}', [PhongHocController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [PhongHocController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{id}/lich-su', [PhongHocController::class, 'lichSu'])->name('lich-su');
        Route::get('/{id}/qr', [PhongHocController::class, 'qr'])->name('qr');
        Route::get('/{id}/bao-tri', [PhongHocController::class, 'listMaintenanceTickets'])->name('bao-tri.index');
        Route::post('/{id}/bao-tri', [PhongHocController::class, 'storeMaintenanceTicket'])->name('bao-tri.store');
        Route::patch('/bao-tri/{ticketId}', [PhongHocController::class, 'updateMaintenanceTicket'])->name('bao-tri.update');
    });

    // ── Danh Mục Khóa Học ────────────────────────────────────────────────────
    Route::prefix('danh-muc-khoa-hoc')->name('danh-muc-khoa-hoc.')->group(function () {
        Route::get('/', [AdminDanhMucKhoaHocController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminDanhMucKhoaHocController::class, 'create'])->name('create');
        Route::post('/', [AdminDanhMucKhoaHocController::class, 'store'])->name('store');
        Route::post('/sap-xep', [AdminDanhMucKhoaHocController::class, 'reorder'])->name('reorder');
        Route::get('/{slug}/sua', [AdminDanhMucKhoaHocController::class, 'edit'])->name('edit');
        Route::put('/{slug}', [AdminDanhMucKhoaHocController::class, 'update'])->name('update');
        Route::delete('/{slug}', [AdminDanhMucKhoaHocController::class, 'destroy'])->name('destroy');
    });

    // ── Khóa Học ─────────────────────────────────────────────────────────────
    Route::prefix('khoa-hoc')->name('khoa-hoc.')->group(function () {
        Route::get('/', [AdminKhoaHocController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminKhoaHocController::class, 'create'])->name('create');
        Route::post('/', [AdminKhoaHocController::class, 'store'])->name('store');
        Route::get('/{slug}', [AdminKhoaHocController::class, 'show'])->name('show');
        Route::get('/{slug}/sua', [AdminKhoaHocController::class, 'edit'])->name('edit');
        Route::put('/{slug}', [AdminKhoaHocController::class, 'update'])->name('update');
        Route::delete('/{slug}', [AdminKhoaHocController::class, 'destroy'])->name('destroy');
        Route::patch('/{slug}/khoi-phuc', [AdminKhoaHocController::class, 'restore'])->name('restore');
    });
    // ── Ca Học ────────────────────────────────────────────────────────────
    Route::prefix('ca-hoc')->name('ca-hoc.')->group(function () {
        Route::get('/', [AdminCaHocController::class, 'index'])->name('index');
        Route::post('/', [AdminCaHocController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminCaHocController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminCaHocController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [AdminCaHocController::class, 'toggleStatus'])->name('toggle-status');
    });

    // ── Bài Viết / Blog ──────────────────────────────────────────
    Route::prefix('bai-viet')->name('bai-viet.')->group(function () {
        Route::get('/', [AdminBaiVietController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminBaiVietController::class, 'create'])->name('create');
        Route::post('/', [AdminBaiVietController::class, 'store'])->name('store');
        Route::get('/thung-rac', [AdminBaiVietController::class, 'trash'])->name('trash');
        Route::post('/xoa-nhieu', [AdminBaiVietController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/khoi-phuc-nhieu', [AdminBaiVietController::class, 'bulkRestore'])->name('bulk-restore');
        Route::get('/{id}', [AdminBaiVietController::class, 'show'])->name('show');
        Route::get('/{id}/sua', [AdminBaiVietController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminBaiVietController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminBaiVietController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [AdminBaiVietController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/upload-image', [AdminBaiVietController::class, 'uploadImage'])->name('upload-image');
        Route::post('/{id}/khoi-phuc', [AdminBaiVietController::class, 'restore'])->name('restore');
        Route::delete('/{id}/xoa-vinh-vien', [AdminBaiVietController::class, 'forceDestroy'])->name('force-destroy');
    });

    // ── Danh Mục Bài Viết ────────────────────────────────────────
    Route::prefix('danh-muc-bai-viet')->name('danh-muc-bai-viet.')->group(function () {
        Route::get('/', [AdminDanhMucBaiVietController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminDanhMucBaiVietController::class, 'create'])->name('create');
        Route::post('/', [AdminDanhMucBaiVietController::class, 'store'])->name('store');
        Route::get('/{id}/sua', [AdminDanhMucBaiVietController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminDanhMucBaiVietController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminDanhMucBaiVietController::class, 'destroy'])->name('destroy');
    });

    // ── API Tags (AJAX) ──────────────────────────────────────────
    Route::prefix('api/tags')->name('api.tags.')->group(function () {
        Route::get('/', [AdminTagController::class, 'index'])->name('index');
        Route::post('/', [AdminTagController::class, 'store'])->name('store');
        Route::delete('/{id}', [AdminTagController::class, 'destroy'])->name('destroy');
    });

    // ── Thông Báo ────────────────────────────────────────────────
    Route::prefix('thong-bao')->name('thong-bao.')->group(function () {
        Route::get('/', [AdminThongBaoController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminThongBaoController::class, 'create'])->name('create');
        Route::get('/thung-rac', [AdminThongBaoController::class, 'trash'])->name('trash');
        Route::post('/', [AdminThongBaoController::class, 'store'])->name('store');
        Route::get('/thung-rac', [AdminThongBaoController::class, 'trash'])->name('trash');
        Route::post('/xoa-nhieu', [AdminThongBaoController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/khoi-phuc-nhieu', [AdminThongBaoController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/nhan-ban', [AdminThongBaoController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/gui-thu', [AdminThongBaoController::class, 'sendTest'])->name('send-test');
        Route::patch('/{id}/khoi-phuc', [AdminThongBaoController::class, 'restore'])->name('restore');
        Route::delete('/{id}/xoa-vinh-vien', [AdminThongBaoController::class, 'forceDestroy'])->name('force-destroy');
        Route::get('/{id}', [AdminThongBaoController::class, 'show'])->name('show');
        Route::get('/{id}/sua', [AdminThongBaoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminThongBaoController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminThongBaoController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/ghim', [AdminThongBaoController::class, 'togglePin'])->name('toggle-pin');
    });

    // ── API: Thông báo (AJAX) ────────────────────────────────────
    Route::prefix('api/thong-bao')->name('api.thong-bao.')->group(function () {
        Route::get('/nguoi-nhan', [AdminThongBaoController::class, 'getRecipients'])->name('recipients');
        Route::get('/chua-doc', [AdminThongBaoController::class, 'getUnreadCount'])->name('unread-count');
        Route::get('/dropdown', [AdminThongBaoController::class, 'getDropdown'])->name('dropdown');
        Route::patch('/da-doc-tat-ca', [AdminThongBaoController::class, 'markAllRead'])->name('mark-all-read');
        Route::patch('/{id}/da-doc', [AdminThongBaoController::class, 'markAsRead'])->name('mark-read');
        Route::patch('/{id}/chua-doc', [AdminThongBaoController::class, 'markAsUnread'])->name('mark-unread');
    });
});

// ─── AUTH ROUTES ─────────────────────────────────────────────────────────────
Auth::routes(['verify' => true]);

Route::get('/auth/session-status', [LoginController::class, 'sessionStatus'])
    ->name('auth.session-status');

Route::middleware('guest')->group(function () {
    Route::get('/register/check-email', [RegisterController::class, 'checkEmail'])
        ->name('register.check-email');

    Route::get('/teacher/login', [LoginController::class, 'showTeacherLoginForm'])->name('teacher.login');
    Route::post('/teacher/login', [LoginController::class, 'teacherLogin'])->name('teacher.login.submit');

    Route::get('/staff/login', [LoginController::class, 'showStaffLoginForm'])->name('staff.login');
    Route::post('/staff/login', [LoginController::class, 'staffLogin'])->name('staff.login.submit');

    // Legacy internal login URL, giữ lại để không gãy bookmark cũ
    Route::get('/admin/login', [LoginController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'adminLogin'])->name('admin.login.submit');

    Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback'])->name('auth.google.callback');
});

// ─── ĐỔI MẬT KHẨU BẮT BUỘC (lần đầu đăng nhập) ─────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/doi-mat-khau-bat-buoc', [App\Http\Controllers\Auth\LoginController::class, 'showForceChangePassword'])
        ->name('force-change-password');
    Route::post('/doi-mat-khau-bat-buoc', [App\Http\Controllers\Auth\LoginController::class, 'processForceChangePassword'])
        ->name('force-change-password.process');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
