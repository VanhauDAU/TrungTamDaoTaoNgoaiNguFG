<?php

namespace Tests\Unit;

use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use App\Models\Education\LopHocChinhSachGia;
use PHPUnit\Framework\TestCase;

class LopHocPricingTest extends TestCase
{
    public function test_class_pricing_accessors_use_fixed_price_model(): void
    {
        $pricing = new LopHocChinhSachGia([
            'loaiThu' => LopHocChinhSachGia::LOAI_THU_TRON_GOI,
            'hocPhiNiemYet' => 4500000,
            'soBuoiCamKet' => 24,
            'trangThai' => 1,
        ]);

        $this->assertSame('Một lần', $pricing->loaiThuLabel);
        $this->assertSame(4500000.0, $pricing->tongHocPhi);
        $this->assertSame('4.500.000 đ', $pricing->tongHocPhiFormat);
    }

    public function test_effective_committed_sessions_falls_back_to_expected_sessions_when_override_is_empty(): void
    {
        $pricing = new LopHocChinhSachGia([
            'loaiThu' => LopHocChinhSachGia::LOAI_THU_TRON_GOI,
            'hocPhiNiemYet' => 4500000,
            'soBuoiCamKet' => null,
        ]);

        $pricing->setRelation('lopHoc', new LopHoc([
            'soBuoiDuKien' => 24,
        ]));

        $this->assertSame(24, $pricing->soBuoiCamKetHieuDung);
    }

    public function test_class_detects_valid_pricing_policy_from_loaded_relation(): void
    {
        $class = new LopHoc(['trangThai' => LopHoc::TRANG_THAI_DANG_TUYEN_SINH]);
        $class->setRelation('chinhSachGia', new LopHocChinhSachGia([
            'hocPhiNiemYet' => 3200000,
            'trangThai' => 1,
        ]));

        $this->assertTrue($class->hasValidPricingPolicy());
    }

    public function test_registration_snapshot_accessor_returns_snapshot_total(): void
    {
        $registration = new DangKyLopHoc([
            'hocPhiNiemYetSnapshot' => 3500000,
            'giamGiaSnapshot' => 500000,
            'hocPhiPhaiThuSnapshot' => 3000000,
        ]);

        $this->assertSame(3000000.0, $registration->hocPhiTongTien);
    }

    public function test_class_status_transition_matrix_blocks_invalid_backward_move_from_in_progress(): void
    {
        $class = new LopHoc(['trangThai' => LopHoc::TRANG_THAI_DANG_HOC]);

        $this->assertFalse($class->canTransitionTo(LopHoc::TRANG_THAI_DANG_TUYEN_SINH));
        $this->assertTrue($class->canTransitionTo(LopHoc::TRANG_THAI_DA_KET_THUC));
    }
}
