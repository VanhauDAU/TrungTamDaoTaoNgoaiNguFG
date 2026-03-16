<?php

namespace App\Services\Admin\CoSo;

use App\Contracts\Admin\CoSo\CoSoServiceInterface;
use App\Models\Education\BuoiHoc;
use App\Models\Facility\CoSoNhatKy;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\PhongHocBaoTri;
use App\Models\Facility\PhongHoc;
use App\Models\Facility\TinhThanh;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CoSoService implements CoSoServiceInterface
{
    public function getList(Request $request): array
    {
        $query = CoSoDaoTao::with('tinhThanh')->withCount('phongHocs');

        if ($search = $request->q) {
            $query->where(fn($q) => $q
            ->where('tenCoSo', 'like', "%{$search}%")
            ->orWhere('maCoSo', 'like', "%{$search}%")
            ->orWhere('diaChi', 'like', "%{$search}%")
            );
        }
        if ($request->filled('trangThai')) {
            $query->where('trangThai', $request->trangThai);
        }

        return [
            'coSos' => $query->orderBy('maCoSo')->paginate(15)->withQueryString(),
            'tongSo' => CoSoDaoTao::count(),
            'hoatDong' => CoSoDaoTao::where('trangThai', 1)->count(),
        ];
    }

    public function getCreateFormData(): array
    {
        return ['tinhThanhs' => TinhThanh::orderBy('tenTinhThanh')->get()];
    }

    public function getDetail(int $id): array
    {
        $coSo = CoSoDaoTao::with([
            'tinhThanh',
            'phongHocs' => fn($query) => $query
                ->select(['phongHocId', 'coSoId', 'tenPhong', 'sucChua', 'trangThietBi', 'khuBlock', 'tang', 'trangThai', 'ngayBaoTri', 'ghiChuBaoTri'])
                ->withCount([
                    'maintenanceTickets as maintenance_ticket_count',
                    'maintenanceTickets as maintenance_ticket_open_count' => fn($ticketQuery) => $ticketQuery->whereIn('trangThai', [
                        PhongHocBaoTri::TRANG_THAI_MOI_TAO,
                        PhongHocBaoTri::TRANG_THAI_DANG_XU_LY,
                    ]),
                ])
                ->orderBy('tenPhong'),
            'nhanSus.taiKhoan.hoSoNguoiDung',
        ])->findOrFail($id);

        $operationsSnapshot = $this->buildOperationalSnapshot($coSo);

        return [
            'coSo' => $coSo,
            'tinhThanhs' => TinhThanh::orderBy('tenTinhThanh')->get(),
            'operationsSnapshot' => $operationsSnapshot,
            'roomStateMap' => collect($operationsSnapshot['roomStates'])->keyBy('phongHocId'),
        ];
    }

    public function getOperationalSnapshot(int $id): array
    {
        $coSo = CoSoDaoTao::with([
            'phongHocs' => fn($query) => $query
                ->select(['phongHocId', 'coSoId', 'tenPhong', 'sucChua', 'khuBlock', 'tang', 'trangThai'])
                ->withCount([
                    'maintenanceTickets as maintenance_ticket_open_count' => fn($ticketQuery) => $ticketQuery->whereIn('trangThai', [
                        PhongHocBaoTri::TRANG_THAI_MOI_TAO,
                        PhongHocBaoTri::TRANG_THAI_DANG_XU_LY,
                    ]),
                ])
                ->orderBy('tenPhong'),
        ])->findOrFail($id);

        return $this->buildOperationalSnapshot($coSo);
    }

    public function getEditFormData(int $id): array
    {
        return [
            'coSo' => CoSoDaoTao::findOrFail($id),
            'tinhThanhs' => TinhThanh::orderBy('tenTinhThanh')->get(),
        ];
    }

    public function store(Request $request): CoSoDaoTao
    {
        $request->validate([
            'maCoSo' => 'required|string|max:20|unique:cosodaotao,maCoSo',
            'tenCoSo' => 'required|string|max:150',
            'diaChi' => 'required|string|max:255',
            'soDienThoai' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'tinhThanhId' => 'nullable|exists:tinhthanh,tinhThanhId',
            'maPhuongXa' => 'nullable|integer',
            'tenPhuongXa' => 'nullable|string|max:150',
            'viDo' => 'nullable|numeric|between:-90,90',
            'kinhDo' => 'nullable|numeric|between:-180,180',
            'ngayKhaiTruong' => 'nullable|date',
            'banDoGoogle' => 'nullable|string|max:500',
            'trangThai' => 'required|in:0,1',
        ], [
            'maCoSo.required' => 'Vui lòng nhập mã cơ sở.',
            'maCoSo.unique' => 'Mã cơ sở đã tồn tại.',
            'tenCoSo.required' => 'Vui lòng nhập tên cơ sở.',
            'diaChi.required' => 'Vui lòng nhập địa chỉ chi tiết.',
        ]);

        $data = $request->only(['maCoSo', 'tenCoSo', 'diaChi', 'soDienThoai', 'email', 'tinhThanhId', 'maPhuongXa', 'tenPhuongXa', 'viDo', 'kinhDo', 'ngayKhaiTruong', 'banDoGoogle', 'trangThai']);
        $data['slug'] = Str::slug($request->tenCoSo);

        $coSo = CoSoDaoTao::create($data);

        $this->writeAuditLog($coSo, null, 'co_so.created', 'Đã tạo cơ sở đào tạo mới.', [
            'tenCoSo' => $coSo->tenCoSo,
            'maCoSo' => $coSo->maCoSo,
        ]);

        return $coSo;
    }

    public function update(Request $request, int $id): CoSoDaoTao
    {
        $coSo = CoSoDaoTao::findOrFail($id);

        $request->validate([
            'maCoSo' => "required|string|max:20|unique:cosodaotao,maCoSo,{$id},coSoId",
            'tenCoSo' => 'required|string|max:150',
            'diaChi' => 'required|string|max:255',
            'soDienThoai' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'tinhThanhId' => 'nullable|exists:tinhthanh,tinhThanhId',
            'maPhuongXa' => 'nullable|integer',
            'tenPhuongXa' => 'nullable|string|max:150',
            'viDo' => 'nullable|numeric|between:-90,90',
            'kinhDo' => 'nullable|numeric|between:-180,180',
            'ngayKhaiTruong' => 'nullable|date',
            'banDoGoogle' => 'nullable|string|max:500',
            'trangThai' => 'required|in:0,1',
        ], [
            'maCoSo.required' => 'Vui lòng nhập mã cơ sở.',
            'maCoSo.unique' => 'Mã cơ sở đã được dùng bởi cơ sở khác.',
            'tenCoSo.required' => 'Vui lòng nhập tên cơ sở.',
            'diaChi.required' => 'Vui lòng nhập địa chỉ chi tiết.',
        ]);

        $data = $request->only(['maCoSo', 'tenCoSo', 'diaChi', 'soDienThoai', 'email', 'tinhThanhId', 'maPhuongXa', 'tenPhuongXa', 'viDo', 'kinhDo', 'ngayKhaiTruong', 'banDoGoogle', 'trangThai']);
        $data['slug'] = Str::slug($request->tenCoSo);

        $coSo->fill($data);
        $dirty = array_keys($coSo->getDirty());
        $coSo->save();

        if ($dirty !== []) {
            $this->writeAuditLog($coSo, null, 'co_so.updated', 'Đã cập nhật thông tin cơ sở đào tạo.', [
                'changedFields' => $dirty,
            ]);
        }

        return $coSo;
    }

    public function destroy(int $id): string
    {
        $coSo = CoSoDaoTao::withCount(['phongHocs', 'nhanSus'])->findOrFail($id);

        if ($coSo->phong_hocs_count > 0) {
            throw new \RuntimeException('Không thể xóa — cơ sở này còn ' . $coSo->phong_hocs_count . ' phòng học.');
        }
        if ($coSo->nhan_sus_count > 0) {
            throw new \RuntimeException('Không thể xóa — cơ sở này đang có ' . $coSo->nhan_sus_count . ' nhân sự làm việc.');
        }

        $ten = $coSo->tenCoSo;
        $this->writeAuditLog($coSo, null, 'co_so.deleted', 'Đã xóa cơ sở đào tạo.', [
            'tenCoSo' => $coSo->tenCoSo,
            'maCoSo' => $coSo->maCoSo,
        ]);
        $coSo->delete();
        return $ten;
    }

    public function getPhuongXa(int $maTinh): array
    {
        try {
            /** @var HttpResponse $response */
            $response = Http::timeout(8)->retry(1, 200)
                ->get("https://provinces.open-api.vn/api/p/{$maTinh}?depth=3");

            if ($response->successful()) {
                $data = $response->json();
                $wards = collect($data['districts'] ?? [])
                    ->flatMap(fn($d) => $d['wards'] ?? [])
                    ->map(fn($w) => ['code' => (int)($w['code'] ?? 0), 'name' => $w['name'] ?? null])
                    ->filter(fn($w) => $w['code'] > 0 && !empty($w['name']))
                    ->unique('code')->sortBy('name')->values();

                return ['success' => true, 'wards' => $wards, 'source' => 'open-api'];
            }
        }
        catch (\Exception) {
        }

        $wards = CoSoDaoTao::query()
            ->whereHas('tinhThanh', fn($q) => $q->where('maAPI', $maTinh))
            ->whereNotNull('maPhuongXa')->whereNotNull('tenPhuongXa')
            ->selectRaw('maPhuongXa as code, tenPhuongXa as name')
            ->groupBy('maPhuongXa', 'tenPhuongXa')->orderBy('tenPhuongXa')->get();

        return ['success' => $wards->isNotEmpty(), 'wards' => $wards, 'source' => 'local-db'];
    }

    public function apiList(Request $request): Collection
    {
        $query = CoSoDaoTao::with('tinhThanh')->where('trangThai', 1);

        if ($request->filled('tinhThanhId'))
            $query->where('tinhThanhId', $request->tinhThanhId);
        if ($request->filled('maPhuongXa'))
            $query->where('maPhuongXa', $request->maPhuongXa);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('tenCoSo', 'like', "%{$s}%")->orWhere('diaChi', 'like', "%{$s}%")->orWhere('tenPhuongXa', 'like', "%{$s}%"));
        }

        return $query->get()->map(fn($c) => [
        'coSoId' => $c->coSoId,
        'tenCoSo' => $c->tenCoSo,
        'diaChi' => $c->diaChi,
        'tenPhuongXa' => $c->tenPhuongXa,
        'tinhThanh' => optional($c->tinhThanh)->tenTinhThanh,
        'tinhThanhId' => $c->tinhThanhId,
        'maPhuongXa' => $c->maPhuongXa,
        'soDienThoai' => $c->soDienThoai,
        'email' => $c->email,
        'viDo' => $c->viDo,
        'kinhDo' => $c->kinhDo,
        'banDoGoogle' => $c->banDoGoogle,
        'hasCoords' => $c->hasCoordinates(),
        'diaChiDayDu' => $c->diaChi_day_du,
        ]);
    }

    public function getPhuongXaCoCoSo(int $tinhThanhId): Collection
    {
        return CoSoDaoTao::where('tinhThanhId', $tinhThanhId)
            ->where('trangThai', 1)->whereNotNull('maPhuongXa')
            ->select('maPhuongXa', 'tenPhuongXa')
            ->groupBy('maPhuongXa', 'tenPhuongXa')->orderBy('tenPhuongXa')->get();
    }

    public function getCoSoByLocation(Request $request): Collection
    {
        $query = CoSoDaoTao::where('trangThai', 1);
        if ($request->filled('tinhThanhId'))
            $query->where('tinhThanhId', $request->tinhThanhId);
        if ($request->filled('maPhuongXa'))
            $query->where('maPhuongXa', $request->maPhuongXa);

        return $query->orderBy('tenCoSo')->get()->map(fn($c) => [
        'coSoId' => $c->coSoId,
        'tenCoSo' => $c->tenCoSo,
        'diaChi' => $c->diaChi,
        'tenPhuongXa' => $c->tenPhuongXa,
        ]);
    }

    private function buildOperationalSnapshot(CoSoDaoTao $coSo): array
    {
        $today = Carbon::today();
        $roomIds = $coSo->phongHocs->pluck('phongHocId')->filter()->values();

        $liveRoomIds = $roomIds->isEmpty()
            ? collect()
            : BuoiHoc::query()
                ->whereIn('phongHocId', $roomIds)
                ->whereDate('ngayHoc', $today->toDateString())
                ->where('trangThai', BuoiHoc::TRANG_THAI_DANG_DIEN_RA)
                ->pluck('phongHocId')
                ->unique()
                ->values();

        $todaySessions = $roomIds->isEmpty()
            ? collect()
            : BuoiHoc::with([
                'caHoc:caHocId,gioBatDau,gioKetThuc,tenCa',
                'lopHoc:lopHocId,tenLopHoc,maLopHoc',
                'taiKhoan.hoSoNguoiDung:taiKhoanId,hoTen',
            ])
                ->whereIn('phongHocId', $roomIds)
                ->whereDate('ngayHoc', $today->toDateString())
                ->orderBy('ngayHoc')
                ->orderBy('caHocId')
                ->get();

        $roomStates = $coSo->phongHocs->map(function (PhongHoc $room) use ($liveRoomIds, $todaySessions) {
            $roomSessionCount = $todaySessions->where('phongHocId', $room->phongHocId)->count();

            return [
                'phongHocId' => $room->phongHocId,
                'tenPhong' => $room->tenPhong,
                'khuBlock' => $room->khuBlock,
                'tang' => $room->tang,
                'viTriLabel' => $room->viTriLabel,
                'sucChua' => (int) ($room->sucChua ?? 0),
                'trangThai' => (int) $room->trangThai,
                'trangThaiLabel' => $room->trangThaiLabel,
                'isCurrentlyInUse' => $liveRoomIds->contains($room->phongHocId),
                'todaySessionsCount' => $roomSessionCount,
                'openMaintenanceTickets' => (int) ($room->maintenance_ticket_open_count ?? 0),
            ];
        })->values();

        $schedule = $coSo->phongHocs->map(function (PhongHoc $room) use ($todaySessions, $liveRoomIds) {
            $sessions = $todaySessions
                ->where('phongHocId', $room->phongHocId)
                ->sortBy(fn(BuoiHoc $session) => $session->caHoc?->gioBatDau ?? '23:59:59')
                ->values()
                ->map(function (BuoiHoc $session) {
                    return [
                        'buoiHocId' => $session->buoiHocId,
                        'timeRange' => trim((string) ($session->caHoc?->gioBatDau ?? '')) . ' - ' . trim((string) ($session->caHoc?->gioKetThuc ?? '')),
                        'status' => (int) $session->trangThai,
                        'statusLabel' => $session->trangThaiLabel,
                        'className' => $session->lopHoc->tenLopHoc ?? 'Lớp chưa rõ',
                        'classCode' => $session->lopHoc->maLopHoc ?? '—',
                        'teacherName' => optional(optional($session->taiKhoan)->hoSoNguoiDung)->hoTen
                            ?? optional($session->taiKhoan)->taiKhoan
                            ?? '—',
                    ];
                });

            return [
                'phongHocId' => $room->phongHocId,
                'tenPhong' => $room->tenPhong,
                'khuBlock' => $room->khuBlock,
                'tang' => $room->tang,
                'viTriLabel' => $room->viTriLabel,
                'trangThai' => (int) $room->trangThai,
                'trangThaiLabel' => $room->trangThaiLabel,
                'isCurrentlyInUse' => $liveRoomIds->contains($room->phongHocId),
                'sessions' => $sessions,
            ];
        })->filter(fn(array $room) => count($room['sessions']) > 0)->values();

        $readyRooms = $coSo->phongHocs->where('trangThai', PhongHoc::TRANG_THAI_SAN_SANG)->count();
        $maintenanceRooms = $coSo->phongHocs->where('trangThai', PhongHoc::TRANG_THAI_BAO_TRI)->count();
        $disabledRooms = $coSo->phongHocs->where('trangThai', PhongHoc::TRANG_THAI_VO_HIEU)->count();
        $liveRooms = $liveRoomIds->count();
        $sessionsTodayCount = $todaySessions->count();
        $upcomingSessions = $todaySessions->where('trangThai', BuoiHoc::TRANG_THAI_SAP_DIEN_RA)->count();
        $liveSessions = $todaySessions->where('trangThai', BuoiHoc::TRANG_THAI_DANG_DIEN_RA)->count();
        $openMaintenanceTickets = $coSo->phongHocs->sum(
            fn(PhongHoc $room) => (int) ($room->maintenance_ticket_open_count
                ?? $room->maintenanceTickets->whereIn('trangThai', [
                    PhongHocBaoTri::TRANG_THAI_MOI_TAO,
                    PhongHocBaoTri::TRANG_THAI_DANG_XU_LY,
                ])->count())
        );
        $utilizationRate = $coSo->phongHocs->count() > 0
            ? (int) round(($liveRooms / max($coSo->phongHocs->count(), 1)) * 100)
            : 0;

        $maintenanceConflicts = $todaySessions
            ->filter(fn(BuoiHoc $session) => in_array(
                (int) optional($coSo->phongHocs->firstWhere('phongHocId', $session->phongHocId))->trangThai,
                [PhongHoc::TRANG_THAI_BAO_TRI, PhongHoc::TRANG_THAI_VO_HIEU],
                true
            ))
            ->count();

        $alerts = collect();
        if ($readyRooms === 0 && $coSo->phongHocs->isNotEmpty()) {
            $alerts->push([
                'level' => 'critical',
                'title' => 'Không còn phòng sẵn sàng',
                'message' => 'Tất cả phòng của cơ sở đang bảo trì hoặc bị vô hiệu hóa.',
            ]);
        }
        if ($maintenanceRooms > 0) {
            $alerts->push([
                'level' => 'warning',
                'title' => "{$maintenanceRooms} phòng đang bảo trì",
                'message' => 'Cần theo dõi tiến độ xử lý để tránh ảnh hưởng kế hoạch học.',
            ]);
        }
        if ($maintenanceConflicts > 0) {
            $alerts->push([
                'level' => 'critical',
                'title' => 'Có lịch học nằm trong phòng không sẵn sàng',
                'message' => "Phát hiện {$maintenanceConflicts} buổi học hôm nay đang gắn với phòng bảo trì hoặc vô hiệu hóa.",
            ]);
        }
        if ($openMaintenanceTickets > 0) {
            $alerts->push([
                'level' => 'info',
                'title' => "{$openMaintenanceTickets} phiếu bảo trì đang mở",
                'message' => 'Cần theo dõi tiến độ xử lý để đóng phiếu đúng hạn.',
            ]);
        }
        if ($sessionsTodayCount === 0) {
            $alerts->push([
                'level' => 'info',
                'title' => 'Hôm nay chưa có buổi học',
                'message' => 'Không có lớp nào được xếp lịch tại cơ sở trong ngày hiện tại.',
            ]);
        }

        $auditLogs = CoSoNhatKy::with('taiKhoan.hoSoNguoiDung:taiKhoanId,hoTen')
            ->where('coSoId', $coSo->coSoId)
            ->latest('coSoNhatKyId')
            ->take(10)
            ->get()
            ->map(function (CoSoNhatKy $log) {
                return [
                    'coSoNhatKyId' => $log->coSoNhatKyId,
                    'action' => $log->hanhDong,
                    'message' => $log->moTa,
                    'actorName' => optional(optional($log->taiKhoan)->hoSoNguoiDung)->hoTen
                        ?? optional($log->taiKhoan)->taiKhoan
                        ?? 'Hệ thống',
                    'createdAt' => optional($log->created_at)->format('d/m/Y H:i'),
                ];
            })
            ->values();

        return [
            'generatedAt' => now()->format('d/m/Y H:i:s'),
            'summary' => [
                'totalRooms' => $coSo->phongHocs->count(),
                'readyRooms' => $readyRooms,
                'maintenanceRooms' => $maintenanceRooms,
                'disabledRooms' => $disabledRooms,
                'liveRooms' => $liveRooms,
                'openMaintenanceTickets' => $openMaintenanceTickets,
                'sessionsToday' => $sessionsTodayCount,
                'upcomingSessions' => $upcomingSessions,
                'liveSessions' => $liveSessions,
                'utilizationRate' => $utilizationRate,
            ],
            'alerts' => $alerts->values()->all(),
            'roomStates' => $roomStates->all(),
            'schedule' => $schedule->all(),
            'auditLogs' => $auditLogs->all(),
        ];
    }

    private function writeAuditLog(CoSoDaoTao $coSo, ?int $phongHocId, string $action, string $message, array $data = []): void
    {
        CoSoNhatKy::create([
            'coSoId' => $coSo->coSoId,
            'phongHocId' => $phongHocId,
            'taiKhoanId' => auth()->id(),
            'hanhDong' => $action,
            'moTa' => $message,
            'duLieu' => $data,
        ]);
    }
}
