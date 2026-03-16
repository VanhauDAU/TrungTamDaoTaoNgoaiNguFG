<?php

namespace App\Contracts\Admin\CoSo;

use App\Models\Facility\PhongHoc;
use Illuminate\Http\Request;

interface PhongHocServiceInterface
{
    public function getList(Request $request): array;

    public function store(Request $request): PhongHoc;

    public function update(Request $request, int $id): PhongHoc;

    public function destroy(Request $request, int $id): string;

    public function toggleStatus(Request $request, int $id): array;

    public function lichSu(int $id): array;

    public function getRoomQrData(int $id): array;

    public function listMaintenanceTickets(int $id): array;

    public function storeMaintenanceTicket(Request $request, int $id): array;

    public function updateMaintenanceTicket(Request $request, int $ticketId): array;
}
