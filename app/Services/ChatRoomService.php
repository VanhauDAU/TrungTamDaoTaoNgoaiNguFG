<?php

namespace App\Services;

use App\Models\Auth\TaiKhoan;
use App\Models\Education\LopHoc;
use App\Models\Interaction\Chat\ChatMessage;
use App\Models\Interaction\Chat\ChatRoom;
use App\Models\Interaction\Chat\ChatRoomMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatRoomService
{
    public function findOrCreateClassRoom(LopHoc $lopHoc, ?int $creatorId = null): ChatRoom
    {
        return DB::transaction(function () use ($lopHoc, $creatorId) {
            $room = ChatRoom::query()->firstOrCreate(
                ['lopHocId' => $lopHoc->lopHocId],
                [
                    'loai' => ChatRoom::TYPE_CLASS_GROUP,
                    'tenPhong' => $lopHoc->tenLopHoc,
                    'taoBoiId' => $creatorId ?? $lopHoc->taiKhoanId,
                    'trangThai' => ChatRoom::STATUS_ACTIVE,
                ]
            );

            $this->ensureTeacherMember($room, $lopHoc->taiKhoanId);

            return $room->fresh();
        });
    }

    public function ensureTeacherMember(ChatRoom $room, ?int $teacherId): ?ChatRoomMember
    {
        if (!$teacherId) {
            return null;
        }

        return ChatRoomMember::query()->updateOrCreate(
            [
                'chatRoomId' => $room->chatRoomId,
                'taiKhoanId' => $teacherId,
            ],
            [
                'vaiTro' => ChatRoomMember::ROLE_TEACHER,
                'joinedAt' => now(),
                'roiAt' => null,
            ]
        );
    }

    public function bootstrapClassRooms(bool $dryRun = false): array
    {
        $stats = [
            'totalClasses' => 0,
            'createdRooms' => 0,
            'existingRooms' => 0,
            'teacherMembersCreatedOrUpdated' => 0,
        ];

        LopHoc::query()
            ->select(['lopHocId', 'tenLopHoc', 'taiKhoanId'])
            ->orderBy('lopHocId')
            ->chunkById(100, function ($classes) use (&$stats, $dryRun) {
                foreach ($classes as $lopHoc) {
                    $stats['totalClasses']++;

                    $existingRoom = ChatRoom::query()
                        ->where('lopHocId', $lopHoc->lopHocId)
                        ->first();

                    if ($existingRoom) {
                        $stats['existingRooms']++;

                        if ($lopHoc->taiKhoanId) {
                            $teacherMember = ChatRoomMember::query()
                                ->where('chatRoomId', $existingRoom->chatRoomId)
                                ->where('taiKhoanId', $lopHoc->taiKhoanId)
                                ->first();

                            if (!$teacherMember || $teacherMember->roiAt !== null || $teacherMember->vaiTro !== ChatRoomMember::ROLE_TEACHER) {
                                $stats['teacherMembersCreatedOrUpdated']++;
                            }
                        }

                        if (!$dryRun) {
                            $this->ensureTeacherMember($existingRoom, $lopHoc->taiKhoanId);
                        }

                        continue;
                    }

                    $stats['createdRooms']++;

                    if ($lopHoc->taiKhoanId) {
                        $stats['teacherMembersCreatedOrUpdated']++;
                    }

                    if (!$dryRun) {
                        $this->findOrCreateClassRoom($lopHoc);
                    }
                }
            }, 'lopHocId', 'lopHocId');

        return $stats;
    }

    public function getVisibleRoomsForUser(TaiKhoan $taiKhoan, ChatAccessService $accessService): Collection
    {
        $classIds = $accessService->getAccessibleClassIds($taiKhoan);

        if ($classIds->isEmpty()) {
            return collect();
        }

        $rooms = ChatRoom::query()
            ->with([
                'lopHoc.khoaHoc',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lastMessage.nguoiGui.hoSoNguoiDung',
            ])
            ->active()
            ->where('loai', ChatRoom::TYPE_CLASS_GROUP)
            ->whereIn('lopHocId', $classIds)
            ->orderByDesc('updated_at')
            ->get();

        return $rooms
            ->map(function (ChatRoom $room) use ($taiKhoan, $accessService) {
                if ($room->isClassGroup() && (int) $taiKhoan->taiKhoanId === (int) optional($room->lopHoc)->taiKhoanId) {
                    $this->ensureTeacherMember($room, $taiKhoan->taiKhoanId);
                }

                return $this->buildRoomPayload($room->fresh(['lopHoc.khoaHoc', 'lopHoc.taiKhoan.hoSoNguoiDung', 'lastMessage.nguoiGui.hoSoNguoiDung']), $taiKhoan, $accessService);
            })
            ->sortByDesc(function (array $room) {
                return $room['lastMessageAt'] ?? $room['updatedAt'] ?? '';
            })
            ->values();
    }

    public function getVisibleRoomForUser(int $roomId, TaiKhoan $taiKhoan, ChatAccessService $accessService): ?ChatRoom
    {
        $classIds = $accessService->getAccessibleClassIds($taiKhoan);

        if ($classIds->isEmpty()) {
            return null;
        }

        $room = ChatRoom::query()
            ->with([
                'lopHoc.khoaHoc',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lastMessage.nguoiGui.hoSoNguoiDung',
            ])
            ->where('chatRoomId', $roomId)
            ->where('loai', ChatRoom::TYPE_CLASS_GROUP)
            ->whereIn('lopHocId', $classIds)
            ->first();

        if ($room && $room->isClassGroup() && (int) $taiKhoan->taiKhoanId === (int) optional($room->lopHoc)->taiKhoanId) {
            $this->ensureTeacherMember($room, $taiKhoan->taiKhoanId);
            $room->load([
                'lopHoc.khoaHoc',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lastMessage.nguoiGui.hoSoNguoiDung',
            ]);
        }

        return $room;
    }

    public function buildRoomPayload(ChatRoom $room, TaiKhoan $taiKhoan, ChatAccessService $accessService): array
    {
        $member = ChatRoomMember::query()
            ->where('chatRoomId', $room->chatRoomId)
            ->where('taiKhoanId', $taiKhoan->taiKhoanId)
            ->whereNull('roiAt')
            ->first();

        $lastMessage = $room->lastMessage;
        $teacher = optional($room->lopHoc)->taiKhoan;
        $teacherName = optional($teacher?->hoSoNguoiDung)->hoTen
            ?? $teacher?->taiKhoan
            ?? 'Chưa phân công';

        return [
            'id' => $room->chatRoomId,
            'name' => $room->tenPhong ?? optional($room->lopHoc)->tenLopHoc ?? 'Nhóm chat lớp',
            'type' => $room->loai,
            'lopHocId' => $room->lopHocId,
            'className' => optional($room->lopHoc)->tenLopHoc,
            'courseName' => optional(optional($room->lopHoc)->khoaHoc)->tenKhoaHoc,
            'teacherName' => $teacherName,
            'canJoin' => $accessService->canJoinRoom($taiKhoan, $room),
            'canAccess' => $accessService->canAccessRoom($taiKhoan, $room),
            'canSend' => $accessService->canSendMessage($taiKhoan, $room),
            'isMember' => $member !== null || ((int) $taiKhoan->taiKhoanId === (int) optional($room->lopHoc)->taiKhoanId),
            'requiresPassword' => false,
            'memberRole' => $member?->vaiTro,
            'lastMessagePreview' => $this->makeLastMessagePreview($lastMessage),
            'lastMessageAt' => optional($lastMessage?->guiLuc ?? $lastMessage?->created_at)?->toIso8601String(),
            'lastMessageAtLabel' => optional($lastMessage?->guiLuc ?? $lastMessage?->created_at)?->diffForHumans(),
            'unreadCount' => $this->getUnreadCount($room, $taiKhoan, $member),
            'updatedAt' => optional($room->updated_at)?->toIso8601String(),
        ];
    }

    public function joinClassRoom(ChatRoom $room, TaiKhoan $taiKhoan): ChatRoomMember
    {
        return ChatRoomMember::query()->updateOrCreate(
            [
                'chatRoomId' => $room->chatRoomId,
                'taiKhoanId' => $taiKhoan->taiKhoanId,
            ],
            [
                'vaiTro' => (int) $taiKhoan->taiKhoanId === (int) optional($room->lopHoc)->taiKhoanId
                    ? ChatRoomMember::ROLE_TEACHER
                    : ChatRoomMember::ROLE_MEMBER,
                'joinedAt' => now(),
                'roiAt' => null,
            ]
        );
    }

    private function getUnreadCount(ChatRoom $room, TaiKhoan $taiKhoan, ?ChatRoomMember $member): int
    {
        if (!$member) {
            return 0;
        }

        $lastReadMessageId = $member?->lastReadMessageId ?? 0;

        return ChatMessage::query()
            ->where('chatRoomId', $room->chatRoomId)
            ->where('chatMessageId', '>', $lastReadMessageId)
            ->where('nguoiGuiId', '!=', $taiKhoan->taiKhoanId)
            ->count();
    }

    private function makeLastMessagePreview(?ChatMessage $message): ?string
    {
        if (!$message) {
            return null;
        }

        if ($message->thuHoiLuc) {
            return 'Tin nhắn đã được thu hồi';
        }

        return match ($message->loai) {
            ChatMessage::TYPE_IMAGE => '[Ảnh]',
            ChatMessage::TYPE_FILE => '[Tệp đính kèm]',
            ChatMessage::TYPE_LOCATION => '[Vị trí]',
            default => \Illuminate\Support\Str::limit(trim(strip_tags((string) $message->noiDung)), 80),
        };
    }
}
