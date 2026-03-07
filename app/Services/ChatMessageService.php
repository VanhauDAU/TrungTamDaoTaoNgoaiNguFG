<?php

namespace App\Services;

use App\Models\Auth\TaiKhoan;
use App\Models\Interaction\Chat\ChatMessage;
use App\Models\Interaction\Chat\ChatRoom;
use App\Models\Interaction\Chat\ChatRoomMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatMessageService
{
    public function getMessagesForUser(ChatRoom $room, TaiKhoan $taiKhoan, ?int $beforeMessageId = null, int $limit = 50): Collection
    {
        $query = ChatMessage::query()
            ->with(['nguoiGui.hoSoNguoiDung', 'replyTo.nguoiGui.hoSoNguoiDung'])
            ->where('chatRoomId', $room->chatRoomId)
            ->whereDoesntHave('deletes', function ($subQuery) use ($taiKhoan) {
                $subQuery->where('taiKhoanId', $taiKhoan->taiKhoanId);
            });

        if ($beforeMessageId) {
            $query->where('chatMessageId', '<', $beforeMessageId);
        }

        return $query
            ->orderByDesc('chatMessageId')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn(ChatMessage $message) => $this->transformMessage($message, $taiKhoan));
    }

    public function sendTextMessage(ChatRoom $room, TaiKhoan $taiKhoan, string $content): array
    {
        $message = DB::transaction(function () use ($room, $taiKhoan, $content) {
            $message = ChatMessage::query()->create([
                'chatRoomId' => $room->chatRoomId,
                'nguoiGuiId' => $taiKhoan->taiKhoanId,
                'loai' => ChatMessage::TYPE_TEXT,
                'noiDung' => trim($content),
                'guiLuc' => now(),
                'deadlineThuHoi' => now()->addDay(),
            ]);

            $room->update([
                'lastMessageId' => $message->chatMessageId,
                'updated_at' => now(),
            ]);

            ChatRoomMember::query()->updateOrCreate(
                [
                    'chatRoomId' => $room->chatRoomId,
                    'taiKhoanId' => $taiKhoan->taiKhoanId,
                ],
                [
                    'vaiTro' => (int) $taiKhoan->taiKhoanId === (int) optional($room->lopHoc)->taiKhoanId
                        ? ChatRoomMember::ROLE_TEACHER
                        : ChatRoomMember::ROLE_MEMBER,
                    'joinedAt' => now(),
                    'lastReadMessageId' => $message->chatMessageId,
                    'lastSeenAt' => now(),
                    'roiAt' => null,
                ]
            );

            return $message->fresh(['nguoiGui.hoSoNguoiDung', 'replyTo.nguoiGui.hoSoNguoiDung']);
        });

        return $this->transformMessage($message, $taiKhoan);
    }

    public function markRoomRead(ChatRoom $room, TaiKhoan $taiKhoan, ?int $lastMessageId = null): void
    {
        $lastMessageId ??= ChatMessage::query()
            ->where('chatRoomId', $room->chatRoomId)
            ->max('chatMessageId');

        ChatRoomMember::query()->updateOrCreate(
            [
                'chatRoomId' => $room->chatRoomId,
                'taiKhoanId' => $taiKhoan->taiKhoanId,
            ],
            [
                'vaiTro' => (int) $taiKhoan->taiKhoanId === (int) optional($room->lopHoc)->taiKhoanId
                    ? ChatRoomMember::ROLE_TEACHER
                    : ChatRoomMember::ROLE_MEMBER,
                'joinedAt' => now(),
                'lastReadMessageId' => $lastMessageId,
                'lastSeenAt' => now(),
                'roiAt' => null,
            ]
        );
    }

    public function transformMessage(ChatMessage $message, TaiKhoan $taiKhoan): array
    {
        $sender = $message->nguoiGui;
        $senderName = optional($sender?->hoSoNguoiDung)->hoTen
            ?? $sender?->taiKhoan
            ?? 'Người dùng';

        return [
            'id' => $message->chatMessageId,
            'roomId' => $message->chatRoomId,
            'type' => $message->loai,
            'content' => $message->thuHoiLuc ? 'Tin nhắn đã được thu hồi' : (string) $message->noiDung,
            'isMine' => (int) $message->nguoiGuiId === (int) $taiKhoan->taiKhoanId,
            'senderId' => $message->nguoiGuiId,
            'senderName' => $senderName,
            'replyTo' => $message->replyTo ? [
                'id' => $message->replyTo->chatMessageId,
                'senderName' => optional(optional($message->replyTo->nguoiGui)->hoSoNguoiDung)->hoTen
                    ?? optional($message->replyTo->nguoiGui)->taiKhoan
                    ?? 'Người dùng',
                'content' => \Illuminate\Support\Str::limit((string) $message->replyTo->noiDung, 60),
            ] : null,
            'isRecalled' => $message->thuHoiLuc !== null,
            'sentAt' => optional($message->guiLuc ?? $message->created_at)?->toIso8601String(),
            'sentAtLabel' => optional($message->guiLuc ?? $message->created_at)?->format('H:i d/m/Y'),
            'canRecall' => $message->thuHoiLuc === null
                && (int) $message->nguoiGuiId === (int) $taiKhoan->taiKhoanId
                && optional($message->deadlineThuHoi)?->isFuture(),
        ];
    }
}
