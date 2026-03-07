<?php

namespace App\Services;

use App\Models\Education\LopHoc;
use App\Models\Interaction\Chat\ChatRoom;
use App\Models\Interaction\Chat\ChatRoomMember;
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
}
