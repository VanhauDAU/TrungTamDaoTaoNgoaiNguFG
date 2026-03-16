<?php

namespace Tests\Feature;

use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\PhongHoc;
use App\Services\Admin\CoSo\PhongHocService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoSoOperationsPhaseTwoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    public function test_room_can_store_location_metadata_for_floor_plan(): void
    {
        $branch = CoSoDaoTao::create([
            'maCoSo' => 'CS05',
            'tenCoSo' => 'Cơ sở mặt bằng',
            'diaChi' => '99 Nguyễn Huệ',
            'trangThai' => 1,
        ]);

        $service = new PhongHocService();
        $created = $service->store(Request::create('/admin/phong-hoc', 'POST', [
            'coSoId' => $branch->coSoId,
            'tenPhong' => 'A-201',
            'khuBlock' => 'A',
            'tang' => 2,
            'sucChua' => 24,
            'trangThai' => 1,
        ]));

        $updated = $service->update(Request::create('/admin/phong-hoc/' . $created->phongHocId, 'PUT', [
            'tenPhong' => 'A-201',
            'khuBlock' => 'B',
            'tang' => 3,
            'sucChua' => 30,
            'trangThai' => 1,
        ]), $created->phongHocId);

        $this->assertSame('B', $updated->khuBlock);
        $this->assertSame(3, $updated->tang);
        $this->assertSame('Block B · Tầng 3', $updated->viTriLabel);
    }

    public function test_maintenance_ticket_workflow_and_qr_payload_are_available(): void
    {
        $branch = CoSoDaoTao::create([
            'maCoSo' => 'CS04',
            'tenCoSo' => 'Cơ sở bảo trì',
            'diaChi' => '34 Trần Phú',
            'trangThai' => 1,
        ]);

        $room = PhongHoc::create([
            'coSoId' => $branch->coSoId,
            'tenPhong' => 'P.303',
            'trangThai' => 1,
        ]);

        $this->createTaiKhoan(1, 'NV000001', 'Kỹ thuật viên');

        $service = new PhongHocService();
        $createdTicket = $service->storeMaintenanceTicket(Request::create('/admin/phong-hoc/1/bao-tri', 'POST', [
            'tieuDe' => 'Kiểm tra điều hòa',
            'moTa' => 'Điều hòa làm lạnh yếu.',
            'mucDoUuTien' => 2,
            'trangThai' => 1,
            'assignedToId' => 1,
        ]), $room->phongHocId);

        $updatedTicket = $service->updateMaintenanceTicket(Request::create('/admin/phong-hoc/bao-tri/1', 'PATCH', [
            'trangThai' => 2,
            'ketQuaXuLy' => 'Đã vệ sinh và nạp gas.',
        ]), $createdTicket['phongHocBaoTriId']);

        $listed = $service->listMaintenanceTickets($room->phongHocId);
        $qr = $service->getRoomQrData($room->phongHocId);

        $this->assertSame('Kiểm tra điều hòa', $createdTicket['tieuDe']);
        $this->assertSame(2, $updatedTicket['trangThai']);
        $this->assertSame(1, $listed['summary']['completed']);
        $this->assertStringContainsString('?room=' . $room->phongHocId, $qr['targetUrl']);
        $this->assertStringContainsString('api.qrserver.com', $qr['qrImageUrl']);
    }

    private function createSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'coso_nhatky',
            'phonghoc_baotri',
            'phonghoc_taisan',
            'hosonguoidung',
            'taikhoan',
            'phonghoc',
            'cosodaotao',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('cosodaotao', function (Blueprint $table) {
            $table->increments('coSoId');
            $table->string('maCoSo')->nullable();
            $table->string('slug')->nullable();
            $table->string('tenCoSo');
            $table->string('diaChi')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();
        });

        Schema::create('phonghoc', function (Blueprint $table) {
            $table->increments('phongHocId');
            $table->unsignedInteger('coSoId');
            $table->string('tenPhong');
            $table->unsignedInteger('sucChua')->nullable();
            $table->string('trangThietBi')->nullable();
            $table->string('khuBlock')->nullable();
            $table->unsignedTinyInteger('tang')->nullable();
            $table->tinyInteger('trangThai')->default(1);
            $table->string('ghiChuBaoTri')->nullable();
            $table->dateTime('ngayBaoTri')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('taikhoan', function (Blueprint $table) {
            $table->increments('taiKhoanId');
            $table->string('taiKhoan')->nullable();
            $table->integer('role')->default(1);
            $table->integer('trangThai')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hosonguoidung', function (Blueprint $table) {
            $table->unsignedInteger('taiKhoanId')->primary();
            $table->string('hoTen')->nullable();
            $table->timestamps();
        });

        Schema::create('phonghoc_taisan', function (Blueprint $table) {
            $table->increments('phongHocTaiSanId');
            $table->unsignedInteger('phongHocId');
            $table->string('maTaiSan')->nullable();
            $table->string('tenTaiSan');
            $table->string('loaiTaiSan')->nullable();
            $table->unsignedInteger('soLuong')->default(1);
            $table->tinyInteger('tinhTrang')->default(1);
            $table->string('ghiChu')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('phonghoc_baotri', function (Blueprint $table) {
            $table->increments('phongHocBaoTriId');
            $table->unsignedInteger('phongHocId');
            $table->unsignedInteger('coSoId');
            $table->string('maPhieu')->unique();
            $table->string('tieuDe');
            $table->text('moTa')->nullable();
            $table->tinyInteger('mucDoUuTien')->default(1);
            $table->tinyInteger('trangThai')->default(0);
            $table->unsignedInteger('createdById')->nullable();
            $table->unsignedInteger('assignedToId')->nullable();
            $table->dateTime('ngayYeuCau');
            $table->dateTime('ngayBatDau')->nullable();
            $table->dateTime('ngayHoanTat')->nullable();
            $table->text('ketQuaXuLy')->nullable();
            $table->timestamps();
        });

        Schema::create('coso_nhatky', function (Blueprint $table) {
            $table->increments('coSoNhatKyId');
            $table->unsignedInteger('coSoId');
            $table->unsignedInteger('phongHocId')->nullable();
            $table->unsignedInteger('taiKhoanId')->nullable();
            $table->string('hanhDong');
            $table->string('moTa');
            $table->json('duLieu')->nullable();
            $table->timestamps();
        });
    }

    private function createTaiKhoan(int $id, string $taiKhoan, string $hoTen): void
    {
        \DB::table('taikhoan')->insert([
            'taiKhoanId' => $id,
            'taiKhoan' => $taiKhoan,
            'role' => 2,
            'trangThai' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('hosonguoidung')->insert([
            'taiKhoanId' => $id,
            'hoTen' => $hoTen,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
