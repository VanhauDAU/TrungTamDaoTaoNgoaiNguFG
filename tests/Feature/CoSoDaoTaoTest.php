<?php

namespace Tests\Feature;

use App\Models\Facility\CoSoDaoTao;
use Tests\TestCase;

class CoSoDaoTaoTest extends TestCase
{
    /**
     * Test hàm kiểm tra xem cơ sở có hợp lệ tọa độ hiển thị bản đồ hay không.
     */
    public function test_has_coordinates_returns_true_if_both_exist()
    {
        // 1. Arrange (Chuẩn bị dữ liệu)
        $coSo = new CoSoDaoTao();
        $coSo->viDo = 15.850983;
        $coSo->kinhDo = 108.252697;

        // 2. Act (Thực thi)
        $result = $coSo->hasCoordinates();

        // 3. Assert (Kiểm chứng)
        $this->assertTrue($result);
    }

    /**
     * Test hàm kiểm tra tọa độ sẽ trả về false nếu thiếu 1 trong 2.
     */
    public function test_has_coordinates_returns_false_if_missing()
    {
        $coSo = new CoSoDaoTao();
        
        // Chỉ có vĩ độ
        $coSo->viDo = 15.850983;
        $coSo->kinhDo = null;
        $this->assertFalse($coSo->hasCoordinates());

        // Chỉ có kinh độ
        $coSo->viDo = null;
        $coSo->kinhDo = 108.252697;
        $this->assertFalse($coSo->hasCoordinates());

        // Thiếu cả hai
        $coSo->viDo = null;
        $coSo->kinhDo = null;
        $this->assertFalse($coSo->hasCoordinates());
    }
}
