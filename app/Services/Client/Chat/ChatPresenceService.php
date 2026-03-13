<?php

namespace App\Services\Client\Chat;

use App\Models\Auth\TaiKhoan;
use App\Models\Interaction\Chat\ChatRoom;
use App\Models\Interaction\Chat\ChatRoomMember;
use Illuminate\Support\Facades\Cache;

class ChatPresenceService
{
    private const TYPING_TTL_SECONDS = 8;

    public function setTyping(ChatRoom $room, TaiKhoan $taiKhoan, bool $typing): void
    {
        $key = $this->typingKey($room->chatRoomId, $taiKhoan->taiKhoanId);

        if ($typing) {
            Cache::put($key, now()->toIso8601String(), now()->addSeconds(self::TYPING_TTL_SECONDS));
            return;
        }

        Cache::forget($key);
    }

    public function getTypingUsers(ChatRoom $room, TaiKhoan $viewer): array
    {
        $room->loadMissing(['members.taiKhoan.hoSoNguoiDung']);

        return $room->members
            ->filter(function (ChatRoomMember $member) use ($viewer) {
                return $member->taiKhoan !== null
                    && $member->roiAt === null
                    && (int) $member->taiKhoanId !== (int) $viewer->taiKhoanId
                    && Cache::has($this->typingKey($member->chatRoomId, $member->taiKhoanId));
            })
            ->map(function (ChatRoomMember $member) {
                $account = $member->taiKhoan;
                $name = optional($account->hoSoNguoiDung)->hoTen
                    ?? $account->taiKhoan
                    ?? 'Người dùng';

                return [
                    'id' => $account->taiKhoanId,
                    'name' => $name,
                    'avatarUrl' => ($account->hoSoNguoiDung && $account->hoSoNguoiDung->anhDaiDien)
                        ? asset('storage/' . ltrim($account->hoSoNguoiDung->anhDaiDien, '/'))
                        : null,
                ];
            })
            ->values()
            ->all();
    }

    private function typingKey(int $roomId, int $userId): string
    {
        return 'chat:typing:' . $roomId . ':' . $userId;
    }
}
