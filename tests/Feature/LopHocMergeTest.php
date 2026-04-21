<?php

namespace Tests\Feature;

use App\Models\Auth\TaiKhoan;
use App\Models\Course\KhoaHoc;
use App\Models\Education\BuoiHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use App\Models\Education\LopHocChinhSachGia;
use App\Models\Education\LopHocDotThu;
use App\Models\Facility\CoSoDaoTao;
use App\Services\Admin\KhoaHoc\LopHocService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LopHocMergeTest extends TestCase
{
    use DatabaseTransactions;

    protected LopHocService $service;
    protected KhoaHoc $course;
    protected CoSoDaoTao $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createClassSchema();
        $this->service = new LopHocService();
        
        // Setup basic data
        $this->course = KhoaHoc::create(['tenKhoaHoc' => 'Test Course', 'trangThai' => 1]);
        $this->branch = CoSoDaoTao::create(['tenCoSo' => 'Test Branch', 'trangThai' => 1]);
    }

    public function test_merge_classes_successfully(): void
    {
        // 1. Create Source Class
        $source = $this->createTestClass('Source Class', 20);
        $this->addPricingPolicy($source, 1000000);
        
        // Add 2 students to source (10% of 20, which is < 50% threshold)
        $this->addStudentToClass($source, 2);
        
        // Add 2 upcoming sessions
        $source->buoiHocs()->create(['trangThai' => BuoiHoc::TRANG_THAI_SAP_DIEN_RA, 'ngayHoc' => Carbon::tomorrow()->toDateString()]);
        $source->buoiHocs()->create(['trangThai' => BuoiHoc::TRANG_THAI_SAP_DIEN_RA, 'ngayHoc' => Carbon::tomorrow()->addDay()->toDateString()]);

        // 2. Create Target Class
        $target = $this->createTestClass('Target Class', 30);
        $this->addPricingPolicy($target, 1000000);
        
        // 3. Perform Merge
        $result = $this->service->mergeClass($source->slug, $target->lopHocId);

        // 4. Assertions
        $this->assertEquals(2, $result['transferredCount']);
        $this->assertEquals(2, $result['cancelledSessionsCount']);

        // Check Source state
        $source->refresh();
        $this->assertEquals(LopHoc::TRANG_THAI_DA_HUY, (int)$source->trangThai);
        foreach ($source->buoiHocs as $session) {
            $this->assertEquals(BuoiHoc::TRANG_THAI_DA_HUY, (int)$session->trangThai);
        }

        // Check Target state
        $target->refresh();
        $this->assertEquals(2, $target->dangKyLopHocs()->count());
    }

    public function test_merge_fails_if_different_courses(): void
    {
        $source = $this->createTestClass('Source', 20);
        $otherCourse = KhoaHoc::create(['tenKhoaHoc' => 'Other Course', 'trangThai' => 1]);
        $target = $this->createTestClass('Target', 20, $otherCourse->khoaHocId);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Lớp đích phải thuộc cùng khóa học');

        $this->service->mergeClass($source->slug, $target->lopHocId);
    }

    public function test_merge_fails_if_target_full(): void
    {
        $source = $this->createTestClass('Source', 20);
        $this->addPricingPolicy($source, 1000000);
        $this->addStudentToClass($source, 5);

        $target = $this->createTestClass('Target', 4); // Only 4 seats
        $this->addPricingPolicy($target, 1000000);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Lớp đích đã đủ chỗ');

        $this->service->mergeClass($source->slug, $target->lopHocId);
    }

    private function createTestClass(string $name, int $capacity, $courseId = null): LopHoc
    {
        return LopHoc::create([
            'tenLopHoc' => $name,
            'slug' => \Str::slug($name) . '-' . uniqid(),
            'khoaHocId' => $courseId ?? $this->course->khoaHocId,
            'coSoId' => $this->branch->coSoId,
            'soHocVienToiDa' => $capacity,
            'trangThai' => LopHoc::TRANG_THAI_SAP_MO,
            'ngayBatDau' => Carbon::tomorrow()->toDateString(),
        ]);
    }

    private function addPricingPolicy(LopHoc $lopHoc, float $price): void
    {
        $lopHoc->chinhSachGia()->create([
            'loaiThu' => LopHocChinhSachGia::LOAI_THU_TRON_GOI,
            'hocPhiNiemYet' => $price,
            'trangThai' => 1
        ]);
    }

    private function addStudentToClass(LopHoc $lopHoc, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $student = TaiKhoan::create([
                'taiKhoan' => 'student_' . uniqid(),
                'role' => TaiKhoan::ROLE_HOC_VIEN,
                'trangThai' => 1
            ]);
            
            DangKyLopHoc::create([
                'lopHocId' => $lopHoc->lopHocId,
                'taiKhoanId' => $student->taiKhoanId,
                'trangThai' => DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN
            ]);
        }
    }

    private function createClassSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'lophoc_dotthu',
            'lophoc_phuphi',
            'lophoc_chinhsachgia',
            'dangKyLopHoc',
            'buoihoc',
            'lophoc',
            'taikhoan',
            'cosodaotao',
            'khoahoc',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('khoahoc', function (Blueprint $table) {
            $table->increments('khoaHocId');
            $table->string('maKhoaHoc')->nullable();
            $table->string('tenKhoaHoc');
            $table->tinyInteger('trangThai')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('cosodaotao', function (Blueprint $table) {
            $table->increments('coSoId');
            $table->string('tenCoSo');
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('taikhoan', function (Blueprint $table) {
            $table->increments('taiKhoanId');
            $table->string('taiKhoan')->nullable();
            $table->string('email')->nullable();
            $table->integer('role')->default(TaiKhoan::ROLE_HOC_VIEN);
            $table->integer('trangThai')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('lophoc', function (Blueprint $table) {
            $table->increments('lopHocId');
            $table->string('slug')->unique();
            $table->string('maLopHoc')->nullable();
            $table->unsignedInteger('khoaHocId');
            $table->unsignedInteger('coSoId');
            $table->string('tenLopHoc');
            $table->unsignedInteger('soHocVienToiDa')->default(0);
            $table->tinyInteger('trangThai')->default(LopHoc::TRANG_THAI_SAP_MO);
            $table->date('ngayBatDau')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('buoihoc', function (Blueprint $table) {
            $table->increments('buoiHocId');
            $table->unsignedInteger('lopHocId');
            $table->date('ngayHoc')->nullable();
            $table->tinyInteger('trangThai')->default(0);
            $table->timestamps();
        });

        Schema::create('dangKyLopHoc', function (Blueprint $table) {
            $table->increments('dangKyLopHocId');
            $table->unsignedInteger('lopHocId');
            $table->unsignedInteger('taiKhoanId');
            $table->tinyInteger('trangThai')->default(1);
        });

        Schema::create('lophoc_chinhsachgia', function (Blueprint $table) {
            $table->increments('lopHocChinhSachGiaId');
            $table->unsignedInteger('lopHocId');
            $table->tinyInteger('loaiThu')->default(0);
            $table->decimal('hocPhiNiemYet', 15, 2)->default(0);
            $table->unsignedInteger('soBuoiCamKet')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('lophoc_dotthu', function (Blueprint $table) {
            $table->increments('lopHocDotThuId');
            $table->unsignedInteger('lopHocChinhSachGiaId');
            $table->unsignedInteger('thuTu')->default(1);
            $table->decimal('soTien', 15, 2)->default(0);
            $table->date('hanThanhToan')->nullable();
            $table->tinyInteger('batBuoc')->default(0);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }
}
