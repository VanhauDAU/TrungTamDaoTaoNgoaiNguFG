<?php

namespace App\Services\Client\Chat;

use App\Models\Auth\TaiKhoan;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use App\Models\Interaction\Chat\ChatRoom;
use App\Models\Interaction\Chat\ChatRoomMember;
use Illuminate\Support\Collection;

class ChatAccessService
{
    public function canJoinRoom(int|TaiKhoan $taiKhoan, ChatRoom $room): bool
    {
        $taiKhoan = $this->resolveTaiKhoan($taiKhoan);

        if (!$room->isClassGroup() || !$room->lopHocId) {
            return false;
        }

        if ($this->isTeacherOfClassRoom($taiKhoan, $room)) {
            return true;
        }

        $registration = DangKyLopHoc::query()
            ->where('lopHocId', $room->lopHocId)
            ->where('taiKhoanId', $taiKhoan->taiKhoanId)
            ->first();

        return $registration?->canJoinChat($room->lopHoc) ?? false;
    }

    public function canAccessRoom(int|TaiKhoan $taiKhoan, ChatRoom $room): bool
    {
        $taiKhoan = $this->resolveTaiKhoan($taiKhoan);

        if ($room->isClassGroup() && $this->isTeacherOfClassRoom($taiKhoan, $room)) {
            return true;
        }

        $isMember = ChatRoomMember::query()
            ->where('chatRoomId', $room->chatRoomId)
            ->where('taiKhoanId', $taiKhoan->taiKhoanId)
            ->whereNull('roiAt')
            ->exists();

        if ($isMember) {
            return true;
        }

        return $this->canJoinRoom($taiKhoan, $room);
    }

    public function canSendMessage(int|TaiKhoan $taiKhoan, ChatRoom $room): bool
    {
        $taiKhoan = $this->resolveTaiKhoan($taiKhoan);

        if (!$this->canAccessRoom($taiKhoan, $room)) {
            return false;
        }

        if ($room->isDirect()) {
            return true;
        }

        if ($this->isTeacherOfClassRoom($taiKhoan, $room)) {
            return true;
        }

        $registration = DangKyLopHoc::query()
            ->where('lopHocId', $room->lopHocId)
            ->where('taiKhoanId', $taiKhoan->taiKhoanId)
            ->first();

        return $registration?->canSendChat($room->lopHoc) ?? false;
    }

    public function canCreateDirectConversation(int|TaiKhoan $firstUser, int|TaiKhoan $secondUser): bool
    {
        $firstUser = $this->resolveTaiKhoan($firstUser);
        $secondUser = $this->resolveTaiKhoan($secondUser);

        if ($firstUser->taiKhoanId === $secondUser->taiKhoanId) {
            return false;
        }

        $firstClassIds = $this->getAccessibleClassIds($firstUser);
        $secondClassIds = $this->getAccessibleClassIds($secondUser);

        return $firstClassIds->intersect($secondClassIds)->isNotEmpty();
    }

    public function getAccessibleClassIds(int|TaiKhoan $taiKhoan): Collection
    {
        $taiKhoan = $this->resolveTaiKhoan($taiKhoan);

        if ($taiKhoan->role === TaiKhoan::ROLE_HOC_VIEN) {
            return DangKyLopHoc::query()
                ->where('taiKhoanId', $taiKhoan->taiKhoanId)
                ->whereIn('trangThai', [
                    DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN,
                    DangKyLopHoc::TRANG_THAI_DANG_HOC,
                    DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
                    DangKyLopHoc::TRANG_THAI_BAO_LUU,
                    DangKyLopHoc::TRANG_THAI_HOAN_THANH,
                ])
                ->whereHas('lopHoc', function ($query) {
                    $query->whereIn('trangThai', [
                        LopHoc::TRANG_THAI_CHOT_DANH_SACH,
                        LopHoc::TRANG_THAI_DANG_HOC,
                        LopHoc::TRANG_THAI_DA_KET_THUC,
                    ]);
                })
                ->pluck('lopHocId')
                ->unique()
                ->values();
        }

        if ($taiKhoan->role === TaiKhoan::ROLE_GIAO_VIEN) {
            return LopHoc::query()
                ->where('taiKhoanId', $taiKhoan->taiKhoanId)
                ->whereIn('trangThai', [
                    LopHoc::TRANG_THAI_CHOT_DANH_SACH,
                    LopHoc::TRANG_THAI_DANG_HOC,
                    LopHoc::TRANG_THAI_DA_KET_THUC,
                ])
                ->pluck('lopHocId')
                ->unique()
                ->values();
        }

        return collect();
    }

    private function isTeacherOfClassRoom(TaiKhoan $taiKhoan, ChatRoom $room): bool
    {
        return $room->isClassGroup()
            && $room->lopHocId
            && (int) $taiKhoan->taiKhoanId === (int) optional($room->lopHoc)->taiKhoanId;
    }

    private function resolveTaiKhoan(int|TaiKhoan $taiKhoan): TaiKhoan
    {
        return $taiKhoan instanceof TaiKhoan
            ? $taiKhoan
            : TaiKhoan::query()->findOrFail($taiKhoan);
    }
}
