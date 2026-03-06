<?php

use App\Http\Controllers\Client\BlogController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\AboutController;
use App\Http\Controllers\Client\CourseController;
use App\Http\Controllers\Client\StudentController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\NhomQuyenController;
use App\Http\Controllers\Admin\HocVien\HocVienController as AdminHocVienController;
use App\Http\Controllers\Admin\GiaoVien\GiaoVienController as AdminGiaoVienController;
use App\Http\Controllers\Admin\NhanVien\NhanVienController as AdminNhanVienController;
use App\Http\Controllers\Admin\Auth\TaiKhoanController;
use App\Http\Controllers\Admin\LienHe\LienHeController as AdminLienHeController;
use App\Http\Controllers\Admin\CoSo\CoSoController;
use App\Http\Controllers\Admin\CoSo\PhongHocController;
use App\Http\Controllers\Admin\KhoaHoc\KhoaHocController as AdminKhoaHocController;
use App\Http\Controllers\Admin\KhoaHoc\LopHocController as AdminLopHocController;
use App\Http\Controllers\Admin\KhoaHoc\BuoiHocController as AdminBuoiHocController;
use App\Http\Controllers\Admin\KhoaHoc\CaHocController as AdminCaHocController;
use App\Http\Controllers\Admin\KhoaHoc\HocPhiController as AdminHocPhiController;
use App\Http\Controllers\Admin\KhoaHoc\DanhMucKhoaHocController as AdminDanhMucKhoaHocController;
use App\Http\Controllers\Admin\BaiViet\BaiVietController as AdminBaiVietController;
use App\Http\Controllers\Admin\BaiViet\DanhMucBaiVietController as AdminDanhMucBaiVietController;
use App\Http\Controllers\Admin\BaiViet\TagController as AdminTagController;
use App\Http\Controllers\Admin\ThongBao\ThongBaoController as AdminThongBaoController;
use App\Http\Controllers\Admin\TaiChinh\HoaDonController as AdminHoaDonController;
use App\Http\Controllers\Client\ClientThongBaoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ─── API ROUTES (Public) ────────────────────────────────────────────────────
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
    Route::prefix('hoc-vien')->name('student.')->middleware('auth')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::post('/', [StudentController::class, 'updateProfile'])->name('update-profile');
        Route::post('/anh-dai-dien', [StudentController::class, 'updateAvatar'])->name('update-avatar');
        Route::get('/doi-mat-khau', [StudentController::class, 'changePassword'])->name('change-password');
        Route::post('/doi-mat-khau', [StudentController::class, 'updatePassword'])->name('update-password');
        Route::get('/hoa-don', [StudentController::class, 'invoices'])->name('invoices');
        Route::get('/hoa-don/{id}', [StudentController::class, 'invoiceDetail'])->name('invoices.show');
        Route::get('/lop-hoc', [StudentController::class, 'myClasses'])->name('classes');
        Route::get('/lich-hoc', [StudentController::class, 'schedule'])->name('schedule');
    });

    // ── Thông báo client (auth required) ────────────────────────────────────
    Route::prefix('thong-bao')->name('thong-bao.')->middleware('auth')->group(function () {
        Route::get('/', [ClientThongBaoController::class, 'index'])->name('index');
    });

    // ── Thông báo client API (auth, JSON) ────────────────────────────────────
    Route::prefix('api/thong-bao')->name('api.thong-bao.')->middleware('auth')->group(function () {
        Route::get('/stream', [ClientThongBaoController::class, 'stream'])->name('stream');
        Route::get('/dropdown', [ClientThongBaoController::class, 'getDropdown'])->name('dropdown');
        Route::get('/chua-doc', [ClientThongBaoController::class, 'getUnreadCount'])->name('unread-count');
        Route::patch('/{id}/da-doc', [ClientThongBaoController::class, 'markRead'])->name('mark-read');
        Route::patch('/{id}/chua-doc', [ClientThongBaoController::class, 'markUnread'])->name('mark-unread');
        Route::patch('/da-doc-tat-ca', [ClientThongBaoController::class, 'markAllRead'])->name('mark-all-read');
    });

});

// ─── ADMIN ROUTES ────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'isAdmin'])->group(function () {
    Route::get('/dashboard', [AdminHomeController::class, 'index'])->name('dashboard');

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

    // ── Học viên ─────────────────────────────────────────────────────────────
    Route::prefix('hoc-vien')->name('hoc-vien.')->group(function () {
        Route::get('/', [AdminHocVienController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminHocVienController::class, 'create'])->name('create');
        Route::post('/', [AdminHocVienController::class, 'store'])->name('store');
        Route::get('/thung-rac', [AdminHocVienController::class, 'trash'])->name('trash');
        Route::patch('/{id}/khoi-phuc', [AdminHocVienController::class, 'restore'])->name('restore');
        Route::get('/{taiKhoan}/sua', [AdminHocVienController::class, 'edit'])->name('edit');
        Route::put('/{taiKhoan}', [AdminHocVienController::class, 'update'])->name('update');
        Route::delete('/{taiKhoan}', [AdminHocVienController::class, 'destroy'])->name('destroy');
    });

    // ── Giáo viên ────────────────────────────────────────────────────────────
    Route::prefix('giao-vien')->name('giao-vien.')->group(function () {
        Route::get('/', [AdminGiaoVienController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminGiaoVienController::class, 'create'])->name('create');
        Route::post('/', [AdminGiaoVienController::class, 'store'])->name('store');
        Route::get('/thung-rac', [AdminGiaoVienController::class, 'trash'])->name('trash');
        Route::patch('/{id}/khoi-phuc', [AdminGiaoVienController::class, 'restore'])->name('restore');
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
        Route::get('/{taiKhoan}/sua', [AdminNhanVienController::class, 'edit'])->name('edit');
        Route::put('/{taiKhoan}', [AdminNhanVienController::class, 'update'])->name('update');
        Route::delete('/{taiKhoan}', [AdminNhanVienController::class, 'destroy'])->name('destroy');
    });


    // ── Liên Hệ (CRM) ───────────────────────────────────────────────────────────
    Route::prefix('lien-he')->name('lien-he.')->group(function () {
        Route::get('/', [AdminLienHeController::class, 'index'])->name('index');
        Route::get('/thung-rac', [AdminLienHeController::class, 'trash'])->name('trash');

        // Bulk actions
        Route::delete('/bulk/xoa', [AdminLienHeController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::patch('/bulk/trang-thai', [AdminLienHeController::class, 'bulkUpdateStatus'])->name('bulk-status');

        // Single item
        Route::patch('/{id}/khoi-phuc', [AdminLienHeController::class, 'restore'])->name('restore');
        Route::get('/{id}', [AdminLienHeController::class, 'show'])->name('show');
        Route::put('/{id}', [AdminLienHeController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminLienHeController::class, 'destroy'])->name('destroy');

        // CRM actions
        Route::post('/{id}/phan-hoi', [AdminLienHeController::class, 'storeReply'])->name('reply.store');
        Route::patch('/{id}/gan-phu-trach', [AdminLienHeController::class, 'assign'])->name('assign');
    });

    // ── Cơ sở Đào tạo ────────────────────────────────────────────────────────
    Route::prefix('co-so')->name('co-so.')->group(function () {
        Route::get('/', [CoSoController::class, 'index'])->name('index');
        Route::get('/tao-moi', [CoSoController::class, 'create'])->name('create');
        Route::post('/', [CoSoController::class, 'store'])->name('store');
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
    });

    // ── Danh Mục Khóa Học ────────────────────────────────────────────────────
    Route::prefix('danh-muc-khoa-hoc')->name('danh-muc-khoa-hoc.')->group(function () {
        Route::get('/', [AdminDanhMucKhoaHocController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminDanhMucKhoaHocController::class, 'create'])->name('create');
        Route::post('/', [AdminDanhMucKhoaHocController::class, 'store'])->name('store');
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

    // ── Lớp Học ──────────────────────────────────────────────────────────────
    Route::prefix('lop-hoc')->name('lop-hoc.')->group(function () {
        Route::get('/', [AdminLopHocController::class, 'index'])->name('index');
        Route::get('/tao-moi', [AdminLopHocController::class, 'create'])->name('create');
        Route::post('/', [AdminLopHocController::class, 'store'])->name('store');
        Route::get('/{slug}', [AdminLopHocController::class, 'show'])->name('show');
        Route::get('/{slug}/sua', [AdminLopHocController::class, 'edit'])->name('edit');
        Route::put('/{slug}', [AdminLopHocController::class, 'update'])->name('update');
        Route::delete('/{slug}', [AdminLopHocController::class, 'destroy'])->name('destroy');
    });

    // ── Buổi Học ──────────────────────────────────────────────────────────────
    Route::prefix('buoi-hoc')->name('buoi-hoc.')->group(function () {
        Route::post('/', [AdminBuoiHocController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminBuoiHocController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminBuoiHocController::class, 'destroy'])->name('destroy');
        Route::post('/tu-dong-tao/{lopHocId}', [AdminBuoiHocController::class, 'autoGenerate'])->name('auto-generate');
    });

    // ── Ca Học ────────────────────────────────────────────────────────────
    Route::prefix('ca-hoc')->name('ca-hoc.')->group(function () {
        Route::get('/', [AdminCaHocController::class, 'index'])->name('index');
        Route::post('/', [AdminCaHocController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminCaHocController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminCaHocController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [AdminCaHocController::class, 'toggleStatus'])->name('toggle-status');
    });

    // ── Học Phí (AJAX) ────────────────────────────────────────────────────────
    Route::get('/api/hoc-phi/{khoaHocId}', [AdminLopHocController::class, 'getHocPhiByKhoaHoc'])->name('api.hoc-phi.by-khoa');

    // ── Gói Học Phí (CRUD) ────────────────────────────────────────────────────
    Route::prefix('hoc-phi')->name('hoc-phi.')->group(function () {
        Route::post('/', [AdminHocPhiController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminHocPhiController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminHocPhiController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [AdminHocPhiController::class, 'toggleStatus'])->name('toggle-status');
    });

    // ── Hóa Đơn & Phiếu Thu ─────────────────────────────────────
    Route::prefix('hoa-don')->name('hoa-don.')->group(function () {
        Route::get('/', [AdminHoaDonController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminHoaDonController::class, 'show'])->name('show');
        Route::put('/{id}', [AdminHoaDonController::class, 'update'])->name('update');
        Route::post('/{id}/phieu-thu', [AdminHoaDonController::class, 'storePhieuThu'])->name('phieu-thu.store');
        Route::delete('/phieu-thu/{id}', [AdminHoaDonController::class, 'destroyPhieuThu'])->name('phieu-thu.destroy');
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
    });
});

// ─── AUTH ROUTES ─────────────────────────────────────────────────────────────
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
