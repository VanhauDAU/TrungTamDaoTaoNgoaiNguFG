<?php

namespace App\Services\Admin\CoSo;

use App\Contracts\Admin\CoSo\PhongHocServiceInterface;
use App\Exceptions\MaintenanceConflictException;
use App\Models\Education\BuoiHoc;
use App\Models\Facility\CoSoNhatKy;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\PhongHocBaoTri;
use App\Models\Facility\PhongHoc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhongHocService implements PhongHocServiceInterface
{
    public function getList(Request $request): array
    {
        $query = PhongHoc::with('coSoDaoTao');

        if ($search = $request->q)
            $query->where('tenPhong', 'like', "%{$search}%");
        if ($request->filled('coSoId'))
            $query->where('coSoId', $request->coSoId);
        if ($request->filled('trangThai'))
            $query->where('trangThai', $request->trangThai);

        return [
            'phongHocs' => $query->orderBy('coSoId')->orderBy('tenPhong')->paginate(20)->withQueryString(),
            'coSos' => CoSoDaoTao::orderBy('maCoSo')->get(),
            'tongSo' => PhongHoc::count(),
            'hoatDong' => PhongHoc::where('trangThai', PhongHoc::TRANG_THAI_SAN_SANG)->count(),
        ];
    }

    public function store(Request $request): PhongHoc
    {
        $request->validate([
            'tenPhong' => [
                'required',
                'string',
                'max:50',
                Rule::unique('phonghoc', 'tenPhong')->where('coSoId', $request->coSoId)
            ],
            'coSoId' => 'required|exists:cosodaotao,coSoId',
            'sucChua' => 'nullable|integer|min:1|max:999',
            'trangThietBi' => 'nullable|string|max:500',
            'khuBlock' => 'nullable|string|max:50',
            'tang' => 'nullable|integer|min:0|max:50',
            'trangThai' => 'required|in:0,1,3',
        ], [
            'tenPhong.required' => 'Vui lòng nhập tên phòng.',
            'tenPhong.unique' => 'Tên phòng này đã tồn tại trong cơ sở đào tạo, vui lòng chọn tên khác.',
            'coSoId.required' => 'Vui lòng chọn cơ sở.',
            'coSoId.exists' => 'Cơ sở không tồn tại.',
        ]);

        $phong = PhongHoc::create($request->only(['tenPhong', 'coSoId', 'sucChua', 'trangThietBi', 'khuBlock', 'tang', 'trangThai']));
        $this->writeAuditLog($phong, 'phong_hoc.created', "Đã tạo phòng {$phong->tenPhong}.", [
            'trangThai' => (int) $phong->trangThai,
            'sucChua' => $phong->sucChua,
            'khuBlock' => $phong->khuBlock,
            'tang' => $phong->tang,
        ]);

        return $phong->fresh();
    }

    public function update(Request $request, int $id): PhongHoc
    {
        $phong = PhongHoc::findOrFail($id);

        $request->validate([
            'tenPhong' => [
                'required',
                'string',
                'max:50',
                Rule::unique('phonghoc', 'tenPhong')->ignore($id, 'phongHocId')->where('coSoId', $phong->coSoId)
            ],
            'sucChua' => 'nullable|integer|min:1|max:999',
            'trangThietBi' => 'nullable|string|max:500',
            'khuBlock' => 'nullable|string|max:50',
            'tang' => 'nullable|integer|min:0|max:50',
            'trangThai' => 'required|in:0,1,3',
            'ghiChuBaoTri' => 'nullable|string|max:500',
        ], [
            'tenPhong.required' => 'Vui lòng nhập tên phòng.',
            'tenPhong.unique' => 'Tên phòng này đã tồn tại trong cơ sở đào tạo, vui lòng chọn tên khác.',
        ]);

        $this->guardMaintenanceStateChange($phong, (int) $request->input('trangThai'), $request->boolean('forceMaintenance'));

        $data = $request->only(['tenPhong', 'sucChua', 'trangThietBi', 'khuBlock', 'tang', 'trangThai', 'ghiChuBaoTri']);
        $phong->fill($data);
        $dirty = array_keys($phong->getDirty());

        if ((int)$request->trangThai === PhongHoc::TRANG_THAI_BAO_TRI && (int)$phong->getOriginal('trangThai') !== PhongHoc::TRANG_THAI_BAO_TRI) {
            $data['ngayBaoTri'] = Carbon::now();
        }
        if ((int)$request->trangThai === PhongHoc::TRANG_THAI_SAN_SANG) {
            $data['ngayBaoTri'] = null;
        }

        $phong->update($data);
        if ($dirty !== [] || array_key_exists('ngayBaoTri', $data)) {
            $this->writeAuditLog($phong->fresh(), 'phong_hoc.updated', "Đã cập nhật phòng {$phong->tenPhong}.", [
                'changedFields' => array_values(array_unique(array_merge($dirty, array_keys(array_intersect_key($data, ['ngayBaoTri' => true]))))),
            ]);
        }

        return $phong->fresh();
    }

    public function destroy(Request $request, int $id): string
    {
        $phong = PhongHoc::withCount(['lopHocDangHoc'])->findOrFail($id);

        if ($phong->lop_hoc_dang_hoc_count > 0) {
            throw new \RuntimeException("Không thể xóa phòng «{$phong->tenPhong}» vì còn {$phong->lop_hoc_dang_hoc_count} lớp học đang hoạt động trong phòng này.");
        }

        $ten = $phong->tenPhong;
        $this->writeAuditLog($phong, 'phong_hoc.deleted', "Đã xóa phòng {$phong->tenPhong}.", [
            'trangThai' => (int) $phong->trangThai,
        ]);
        $phong->delete();
        return $ten;
    }

    public function toggleStatus(Request $request, int $id): array
    {
        $phong = PhongHoc::findOrFail($id);
        $request->validate(['ghiChuBaoTri' => 'nullable|string|max:500']);

        if ($phong->isAvailable()) {
            $this->guardMaintenanceStateChange($phong, PhongHoc::TRANG_THAI_BAO_TRI, $request->boolean('forceMaintenance'));
            $phong->update(['trangThai' => PhongHoc::TRANG_THAI_BAO_TRI, 'ngayBaoTri' => Carbon::now(), 'ghiChuBaoTri' => $request->ghiChuBaoTri]);
            $msg = "Phòng «{$phong->tenPhong}» đã chuyển sang trạng thái bảo trì.";
            $this->writeAuditLog($phong->fresh(), 'phong_hoc.maintenance_started', "Đã chuyển phòng {$phong->tenPhong} sang bảo trì.", [
                'ghiChuBaoTri' => $request->ghiChuBaoTri,
            ]);
        }
        else {
            $phong->update(['trangThai' => PhongHoc::TRANG_THAI_SAN_SANG, 'ngayBaoTri' => null, 'ghiChuBaoTri' => null]);
            $msg = "Phòng «{$phong->tenPhong}» đã sẵn sàng sử dụng.";
            $this->writeAuditLog($phong->fresh(), 'phong_hoc.maintenance_cleared', "Đã đưa phòng {$phong->tenPhong} về trạng thái sẵn sàng.", []);
        }

        return ['success' => true, 'message' => $msg, 'trangThai' => $phong->fresh()->trangThai, 'room' => $phong->fresh()];
    }

    public function lichSu(int $id): array
    {
        $phong = PhongHoc::findOrFail($id);
        $lichSu = $phong->lopHocs()
            ->with(['khoaHoc:khoaHocId,tenKhoaHoc', 'taiKhoan.hoSoNguoiDung', 'taiKhoan.nhanSu'])
            ->orderByDesc('ngayBatDau')->take(20)->get()
            ->map(function ($lop) {
            $tenGV = optional($lop->taiKhoan->hoSoNguoiDung ?? null)->hoTen
                ?? optional($lop->taiKhoan->nhanSu ?? null)->hoTen ?? '—';
            return [
            'lopHocId' => $lop->lopHocId,
            'maLopHoc' => $lop->maLopHoc,
            'tenLopHoc' => $lop->tenLopHoc,
            'tenKhoaHoc' => $lop->khoaHoc->tenKhoaHoc ?? '—',
            'tenGiaoVien' => $tenGV,
            'ngayBatDau' => $lop->ngayBatDau ?Carbon::parse($lop->ngayBatDau)->format('d/m/Y') : '—',
            'ngayKetThuc' => $lop->ngayKetThuc ?Carbon::parse($lop->ngayKetThuc)->format('d/m/Y') : '—',
            'trangThai' => $lop->trangThai,
            'trangThaiLabel' => $lop->trangThaiLabel,
            ];
        });

        return ['success' => true, 'data' => $lichSu, 'total' => $phong->lopHocs()->count()];
    }

    public function getRoomQrData(int $id): array
    {
        $room = PhongHoc::with('coSoDaoTao')->findOrFail($id);
        $targetUrl = route('admin.co-so.show', $room->coSoId) . '?room=' . $room->phongHocId;

        return [
            'phongHocId' => $room->phongHocId,
            'tenPhong' => $room->tenPhong,
            'coSo' => $room->coSoDaoTao->tenCoSo ?? '—',
            'targetUrl' => $targetUrl,
            'qrImageUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' . urlencode($targetUrl),
        ];
    }

    public function listMaintenanceTickets(int $id): array
    {
        $room = PhongHoc::with([
            'maintenanceTickets' => fn($query) => $query
                ->with([
                    'nguoiTao.hoSoNguoiDung:taiKhoanId,hoTen',
                    'nguoiPhuTrach.hoSoNguoiDung:taiKhoanId,hoTen',
                ])
                ->latest('phongHocBaoTriId'),
        ])->findOrFail($id);

        return [
            'room' => [
                'phongHocId' => $room->phongHocId,
                'tenPhong' => $room->tenPhong,
            ],
            'summary' => [
                'open' => $room->maintenanceTickets->whereIn('trangThai', [
                    PhongHocBaoTri::TRANG_THAI_MOI_TAO,
                    PhongHocBaoTri::TRANG_THAI_DANG_XU_LY,
                ])->count(),
                'completed' => $room->maintenanceTickets->where('trangThai', PhongHocBaoTri::TRANG_THAI_DA_HOAN_TAT)->count(),
            ],
            'tickets' => $room->maintenanceTickets->map(fn(PhongHocBaoTri $ticket) => $this->buildMaintenanceTicketResponse($ticket))->values(),
            'statusOptions' => PhongHocBaoTri::trangThaiLabels(),
            'priorityOptions' => PhongHocBaoTri::mucDoUuTienLabels(),
        ];
    }

    public function storeMaintenanceTicket(Request $request, int $id): array
    {
        $room = PhongHoc::findOrFail($id);
        $payload = $this->validateMaintenanceTicketPayload($request);
        $payload['coSoId'] = $room->coSoId;
        $payload['phongHocId'] = $room->phongHocId;
        $payload['maPhieu'] = PhongHocBaoTri::generateCode();
        $payload['createdById'] = auth()->id();
        $payload['ngayYeuCau'] = now();
        $payload['trangThai'] = $payload['trangThai'] ?? PhongHocBaoTri::TRANG_THAI_MOI_TAO;

        if ((int) $payload['trangThai'] === PhongHocBaoTri::TRANG_THAI_DANG_XU_LY) {
            $payload['ngayBatDau'] = now();
        }

        $ticket = PhongHocBaoTri::create($payload);
        $this->writeAuditLog($room, 'phong_hoc.maintenance_ticket_created', "Đã tạo phiếu bảo trì {$ticket->maPhieu} cho phòng {$room->tenPhong}.", [
            'mucDoUuTien' => (int) $ticket->mucDoUuTien,
            'trangThai' => (int) $ticket->trangThai,
        ]);

        return $this->buildMaintenanceTicketResponse($ticket->fresh(['nguoiTao.hoSoNguoiDung', 'nguoiPhuTrach.hoSoNguoiDung']));
    }

    public function updateMaintenanceTicket(Request $request, int $ticketId): array
    {
        $ticket = PhongHocBaoTri::with('phongHoc')->findOrFail($ticketId);
        $payload = $this->validateMaintenanceTicketPayload($request, true);

        if (array_key_exists('trangThai', $payload)) {
            $newStatus = (int) $payload['trangThai'];
            if ($newStatus === PhongHocBaoTri::TRANG_THAI_DANG_XU_LY && !$ticket->ngayBatDau) {
                $payload['ngayBatDau'] = now();
            }
            if ($newStatus === PhongHocBaoTri::TRANG_THAI_DA_HOAN_TAT) {
                $payload['ngayHoanTat'] = now();
            }
            if ($newStatus === PhongHocBaoTri::TRANG_THAI_DA_HUY) {
                $payload['ngayHoanTat'] = null;
            }
        }

        $ticket->fill($payload);
        $dirty = array_keys($ticket->getDirty());
        $ticket->save();

        if ($dirty !== []) {
            $this->writeAuditLog($ticket->phongHoc, 'phong_hoc.maintenance_ticket_updated', "Đã cập nhật phiếu bảo trì {$ticket->maPhieu}.", [
                'changedFields' => $dirty,
            ]);
        }

        return $this->buildMaintenanceTicketResponse($ticket->fresh(['nguoiTao.hoSoNguoiDung', 'nguoiPhuTrach.hoSoNguoiDung']));
    }

    private function guardMaintenanceStateChange(PhongHoc $phong, int $targetStatus, bool $forceMaintenance = false): void
    {
        if ($targetStatus !== PhongHoc::TRANG_THAI_BAO_TRI || $phong->isInMaintenance()) {
            return;
        }

        $liveSessions = BuoiHoc::with(['caHoc', 'lopHoc'])
            ->where('phongHocId', $phong->phongHocId)
            ->whereDate('ngayHoc', Carbon::today()->toDateString())
            ->where('trangThai', BuoiHoc::TRANG_THAI_DANG_DIEN_RA)
            ->get();

        if ($liveSessions->isNotEmpty()) {
            throw new \RuntimeException('Phòng đang có buổi học diễn ra, không thể chuyển sang bảo trì ngay lúc này.');
        }

        $impact = $this->buildMaintenanceImpact($phong);
        if (($impact['count'] ?? 0) > 0 && !$forceMaintenance) {
            throw new MaintenanceConflictException(
                $impact,
                "Phòng {$phong->tenPhong} đang có {$impact['count']} buổi học sắp tới. Xác nhận cưỡng bức nếu vẫn muốn chuyển sang bảo trì."
            );
        }
    }

    private function buildMaintenanceImpact(PhongHoc $phong): array
    {
        $sessions = BuoiHoc::with([
            'caHoc:caHocId,gioBatDau,gioKetThuc',
            'lopHoc:lopHocId,tenLopHoc,maLopHoc',
            'taiKhoan.hoSoNguoiDung:taiKhoanId,hoTen',
        ])
            ->where('phongHocId', $phong->phongHocId)
            ->where(function ($query) {
                $query->whereDate('ngayHoc', '>', Carbon::today()->toDateString())
                    ->orWhere(function ($nested) {
                        $nested->whereDate('ngayHoc', Carbon::today()->toDateString())
                            ->where('trangThai', BuoiHoc::TRANG_THAI_SAP_DIEN_RA);
                    });
            })
            ->whereIn('trangThai', [
                BuoiHoc::TRANG_THAI_SAP_DIEN_RA,
                BuoiHoc::TRANG_THAI_DANG_DIEN_RA,
            ])
            ->orderBy('ngayHoc')
            ->orderBy('caHocId')
            ->get();

        return [
            'count' => $sessions->count(),
            'sessions' => $sessions->take(5)->map(function (BuoiHoc $session) {
                return [
                    'buoiHocId' => $session->buoiHocId,
                    'ngayHoc' => Carbon::parse($session->ngayHoc)->format('d/m/Y'),
                    'timeRange' => trim((string) ($session->caHoc?->gioBatDau ?? '')) . ' - ' . trim((string) ($session->caHoc?->gioKetThuc ?? '')),
                    'tenLopHoc' => $session->lopHoc->tenLopHoc ?? 'Lớp chưa rõ',
                    'maLopHoc' => $session->lopHoc->maLopHoc ?? '—',
                    'tenGiaoVien' => optional(optional($session->taiKhoan)->hoSoNguoiDung)->hoTen
                        ?? optional($session->taiKhoan)->taiKhoan
                        ?? '—',
                ];
            })->values()->all(),
        ];
    }

    private function writeAuditLog(PhongHoc $phong, string $action, string $message, array $data = []): void
    {
        CoSoNhatKy::create([
            'coSoId' => $phong->coSoId,
            'phongHocId' => $phong->phongHocId,
            'taiKhoanId' => auth()->id(),
            'hanhDong' => $action,
            'moTa' => $message,
            'duLieu' => $data,
        ]);
    }

    private function validateMaintenanceTicketPayload(Request $request, bool $partial = false): array
    {
        $rules = [
            'tieuDe' => [$partial ? 'sometimes' : 'required', 'required', 'string', 'max:150'],
            'moTa' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'string'],
            'mucDoUuTien' => [$partial ? 'sometimes' : 'required', 'required', 'in:0,1,2,3'],
            'trangThai' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'in:0,1,2,3'],
            'assignedToId' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'exists:taikhoan,taiKhoanId'],
            'ketQuaXuLy' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'string'],
        ];

        return $request->validate($rules);
    }

    private function buildMaintenanceTicketResponse(PhongHocBaoTri $ticket): array
    {
        return [
            'phongHocBaoTriId' => $ticket->phongHocBaoTriId,
            'maPhieu' => $ticket->maPhieu,
            'tieuDe' => $ticket->tieuDe,
            'moTa' => $ticket->moTa,
            'mucDoUuTien' => (int) $ticket->mucDoUuTien,
            'mucDoUuTienLabel' => $ticket->mucDoUuTienLabel,
            'trangThai' => (int) $ticket->trangThai,
            'trangThaiLabel' => $ticket->trangThaiLabel,
            'assignedToId' => $ticket->assignedToId,
            'assignedToName' => optional(optional($ticket->nguoiPhuTrach)->hoSoNguoiDung)->hoTen
                ?? optional($ticket->nguoiPhuTrach)->taiKhoan
                ?? 'Chưa phân công',
            'createdByName' => optional(optional($ticket->nguoiTao)->hoSoNguoiDung)->hoTen
                ?? optional($ticket->nguoiTao)->taiKhoan
                ?? 'Hệ thống',
            'ngayYeuCau' => optional($ticket->ngayYeuCau)->format('d/m/Y H:i'),
            'ngayBatDau' => optional($ticket->ngayBatDau)->format('d/m/Y H:i'),
            'ngayHoanTat' => optional($ticket->ngayHoanTat)->format('d/m/Y H:i'),
            'ketQuaXuLy' => $ticket->ketQuaXuLy,
        ];
    }
}
