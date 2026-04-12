<?php

namespace App\Services\Client\Chat;

use App\Models\Auth\TaiKhoan;
use App\Models\Education\LopHoc;
use App\Models\Interaction\Chat\ChatMessage;
use App\Models\Interaction\Chat\ChatRoom;
use App\Models\Interaction\Chat\ChatRoomMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            $room->fill([
                'loai' => ChatRoom::TYPE_CLASS_GROUP,
                'tenPhong' => $lopHoc->tenLopHoc,
                'taoBoiId' => $room->taoBoiId ?: ($creatorId ?? $lopHoc->taiKhoanId),
                'trangThai' => ChatRoom::STATUS_ACTIVE,
            ]);

            if ($room->isDirty()) {
                $room->save();
            }

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

        if ($classIds->isNotEmpty()) {
            $this->ensureRoomsForClassIds($classIds);
        }

        $roomQuery = ChatRoom::query()
            ->with([
                'lopHoc.khoaHoc',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lastMessage.nguoiGui.hoSoNguoiDung',
                'members.taiKhoan.hoSoNguoiDung',
            ])
            ->active();

        if ($classIds->isNotEmpty()) {
            $roomQuery->where(function ($query) use ($classIds, $taiKhoan) {
                $query->where(function ($subQuery) use ($classIds) {
                    $subQuery
                        ->where('loai', ChatRoom::TYPE_CLASS_GROUP)
                        ->whereIn('lopHocId', $classIds);
                })->orWhere(function ($subQuery) use ($taiKhoan) {
                    $subQuery
                        ->where('loai', ChatRoom::TYPE_DIRECT)
                        ->whereHas('members', function ($memberQuery) use ($taiKhoan) {
                            $memberQuery
                                ->where('taiKhoanId', $taiKhoan->taiKhoanId)
                                ->whereNull('roiAt');
                        });
                });
            });
        } else {
            $roomQuery
                ->where('loai', ChatRoom::TYPE_DIRECT)
                ->whereHas('members', function ($memberQuery) use ($taiKhoan) {
                    $memberQuery
                        ->where('taiKhoanId', $taiKhoan->taiKhoanId)
                        ->whereNull('roiAt');
                });
        }

        $rooms = $roomQuery
            ->orderByDesc('updated_at')
            ->get();

        return $rooms
            ->map(function (ChatRoom $room) use ($taiKhoan, $accessService) {
                if ($room->isClassGroup() && (int) $taiKhoan->taiKhoanId === (int) optional($room->lopHoc)->taiKhoanId) {
                    $this->ensureTeacherMember($room, $taiKhoan->taiKhoanId);
                }

                return $this->buildRoomPayload($room->fresh([
                    'lopHoc.khoaHoc',
                    'lopHoc.taiKhoan.hoSoNguoiDung',
                    'lastMessage.nguoiGui.hoSoNguoiDung',
                    'members.taiKhoan.hoSoNguoiDung',
                ]), $taiKhoan, $accessService);
            })
            ->sortByDesc(function (array $room) {
                return $room['lastMessageAt'] ?? $room['updatedAt'] ?? '';
            })
            ->values();
    }

    public function getVisibleRoomForUser(int $roomId, TaiKhoan $taiKhoan, ChatAccessService $accessService, bool $ensureRooms = true): ?ChatRoom
    {
        $classIds = $accessService->getAccessibleClassIds($taiKhoan);

        if ($ensureRooms && $classIds->isNotEmpty()) {
            $this->ensureRoomsForClassIds($classIds);
        }

        $room = ChatRoom::query()
            ->with([
                'lopHoc.khoaHoc',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lastMessage.nguoiGui.hoSoNguoiDung',
                'members.taiKhoan.hoSoNguoiDung',
            ])
            ->where('chatRoomId', $roomId);

        if ($classIds->isNotEmpty()) {
            $room->where(function ($query) use ($classIds, $taiKhoan) {
                $query->where(function ($subQuery) use ($classIds) {
                    $subQuery
                        ->where('loai', ChatRoom::TYPE_CLASS_GROUP)
                        ->whereIn('lopHocId', $classIds);
                })->orWhere(function ($subQuery) use ($taiKhoan) {
                    $subQuery
                        ->where('loai', ChatRoom::TYPE_DIRECT)
                        ->whereHas('members', function ($memberQuery) use ($taiKhoan) {
                            $memberQuery
                                ->where('taiKhoanId', $taiKhoan->taiKhoanId)
                                ->whereNull('roiAt');
                        });
                });
            });
        } else {
            $room
                ->where('loai', ChatRoom::TYPE_DIRECT)
                ->whereHas('members', function ($memberQuery) use ($taiKhoan) {
                    $memberQuery
                        ->where('taiKhoanId', $taiKhoan->taiKhoanId)
                        ->whereNull('roiAt');
                });
        }

        $room = $room->first();

        if ($room && $room->isClassGroup() && (int) $taiKhoan->taiKhoanId === (int) optional($room->lopHoc)->taiKhoanId) {
            $this->ensureTeacherMember($room, $taiKhoan->taiKhoanId);
            $room->load([
                'lopHoc.khoaHoc',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lastMessage.nguoiGui.hoSoNguoiDung',
                'members.taiKhoan.hoSoNguoiDung',
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
        $directPeer = $this->resolveDirectPeer($room, $taiKhoan);
        $directPeerName = optional($directPeer?->hoSoNguoiDung)->hoTen
            ?? $directPeer?->taiKhoan;
        $directContext = $this->resolveDirectContext($taiKhoan, $directPeer, $accessService);
        $roomName = $room->isDirect()
            ? ($directPeerName ?: 'Tin nhắn riêng')
            : ($room->tenPhong ?? optional($room->lopHoc)->tenLopHoc ?? 'Nhóm chat lớp');
        $roomAvatarUrl = $room->isDirect()
            ? $this->avatarUrlForAccount($directPeer)
            : null;

        return [
            'id' => $room->chatRoomId,
            'name' => $roomName,
            'avatarUrl' => $roomAvatarUrl,
            'type' => $room->loai,
            'lopHocId' => $room->lopHocId,
            'className' => $room->isDirect() ? 'Đoạn chat riêng' : optional($room->lopHoc)->tenLopHoc,
            'classCode' => $room->isDirect() ? null : optional($room->lopHoc)->maLopHoc,
            'courseName' => $room->isDirect() ? null : optional(optional($room->lopHoc)->khoaHoc)->tenKhoaHoc,
            'teacherName' => $room->isDirect() ? ($directPeer?->getRoleLabel() ?? 'Thành viên') : $teacherName,
            'teacherAvatarUrl' => $this->avatarUrlForAccount($teacher),
            'directContextClassName' => $directContext['className'] ?? null,
            'directContextCourseName' => $directContext['courseName'] ?? null,
            'directContextLabel' => $directContext['label'] ?? null,
            'canJoin' => $accessService->canJoinRoom($taiKhoan, $room),
            'canAccess' => $accessService->canAccessRoom($taiKhoan, $room),
            'canSend' => $accessService->canSendMessage($taiKhoan, $room),
            'isMember' => $member !== null || ((int) $taiKhoan->taiKhoanId === (int) optional($room->lopHoc)->taiKhoanId),
            'requiresPassword' => false,
            'memberRole' => $member?->vaiTro,
            'directPeerId' => $directPeer?->taiKhoanId,
            'directPeerName' => $directPeerName,
            'directPeerAvatarUrl' => $this->avatarUrlForAccount($directPeer),
            'lastMessagePreview' => $this->makeLastMessagePreview($lastMessage),
            'lastMessageAt' => optional($lastMessage?->guiLuc ?? $lastMessage?->created_at)?->toIso8601String(),
            'lastMessageAtLabel' => optional($lastMessage?->guiLuc ?? $lastMessage?->created_at)?->diffForHumans(),
            'unreadCount' => $this->getUnreadCount($room, $taiKhoan, $member),
            'memberLastReadMessageId' => $member?->lastReadMessageId,
            'memberLastSeenAt' => optional($member?->lastSeenAt)?->toIso8601String(),
            'updatedAt' => optional($room->updated_at)?->toIso8601String(),
        ];
    }

    public function getRoomMembersPayload(ChatRoom $room, TaiKhoan $viewer, ChatAccessService $accessService): Collection
    {
        $room->loadMissing(['members.taiKhoan.hoSoNguoiDung']);

        return $room->members
            ->filter(fn(ChatRoomMember $member) => $member->taiKhoan !== null)
            ->map(function (ChatRoomMember $member) use ($viewer, $accessService) {
                $account = $member->taiKhoan;
                $name = optional($account->hoSoNguoiDung)->hoTen
                    ?? $account->taiKhoan
                    ?? 'Người dùng';

                return [
                    'id' => $account->taiKhoanId,
                    'name' => $name,
                    'initials' => $this->makeInitials($name),
                    'avatarUrl' => $this->avatarUrlForAccount($account),
                    'roleLabel' => $this->mapChatRoleLabel($member->vaiTro, $account),
                    'isMe' => (int) $account->taiKhoanId === (int) $viewer->taiKhoanId,
                    'canDirect' => (int) $account->taiKhoanId !== (int) $viewer->taiKhoanId
                        && $accessService->canCreateDirectConversation($viewer, $account),
                    'isOnline' => $this->memberIsOnlineRecently($member),
                    'presenceLabel' => $this->presenceLabelForMember($member),
                    'lastSeenAt' => optional($member->lastSeenAt)?->toIso8601String(),
                ];
            })
            ->sortBy([
                ['isMe', 'asc'],
                ['roleLabel', 'asc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    public function findOrCreateDirectRoom(TaiKhoan $firstUser, TaiKhoan $secondUser): ChatRoom
    {
        $orderedIds = collect([$firstUser->taiKhoanId, $secondUser->taiKhoanId])
            ->sort()
            ->values();

        return DB::transaction(function () use ($orderedIds, $firstUser, $secondUser) {
            $room = ChatRoom::query()
                ->with(['members'])
                ->where('loai', ChatRoom::TYPE_DIRECT)
                ->whereHas('members', function ($query) use ($orderedIds) {
                    $query->where('taiKhoanId', $orderedIds[0])->whereNull('roiAt');
                })
                ->whereHas('members', function ($query) use ($orderedIds) {
                    $query->where('taiKhoanId', $orderedIds[1])->whereNull('roiAt');
                })
                ->get()
                ->first(function (ChatRoom $candidate) use ($orderedIds) {
                    $activeMemberIds = $candidate->members
                        ->pluck('taiKhoanId')
                        ->sort()
                        ->values();

                    return $activeMemberIds->count() === 2
                        && $activeMemberIds->all() === $orderedIds->all();
                });

            if (!$room) {
                $room = ChatRoom::query()->create([
                    'loai' => ChatRoom::TYPE_DIRECT,
                    'tenPhong' => null,
                    'taoBoiId' => $firstUser->taiKhoanId,
                    'trangThai' => ChatRoom::STATUS_ACTIVE,
                ]);
            }

            foreach ([$firstUser, $secondUser] as $user) {
                ChatRoomMember::query()->updateOrCreate(
                    [
                        'chatRoomId' => $room->chatRoomId,
                        'taiKhoanId' => $user->taiKhoanId,
                    ],
                    [
                        'vaiTro' => ChatRoomMember::ROLE_MEMBER,
                        'joinedAt' => now(),
                        'roiAt' => null,
                    ]
                );
            }

            return $room->fresh([
                'lopHoc.khoaHoc',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lastMessage.nguoiGui.hoSoNguoiDung',
                'members.taiKhoan.hoSoNguoiDung',
            ]);
        });
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

    public function leaveRoom(ChatRoom $room, TaiKhoan $taiKhoan): void
    {
        DB::transaction(function () use ($room, $taiKhoan) {
            if ($room->isDirect()) {
                // Direct chat: xóa hẳn bản ghi member của người dùng này
                ChatRoomMember::query()
                    ->where('chatRoomId', $room->chatRoomId)
                    ->where('taiKhoanId', $taiKhoan->taiKhoanId)
                    ->delete();

                // Nếu không còn thành viên nào → xóa luôn room
                $remainingMembers = ChatRoomMember::query()
                    ->where('chatRoomId', $room->chatRoomId)
                    ->count();

                if ($remainingMembers === 0) {
                    $room->delete();
                }
            } else {
                // Class group: đặt roiAt = now() (rời nhóm, không xóa room vì còn người khác)
                ChatRoomMember::query()
                    ->where('chatRoomId', $room->chatRoomId)
                    ->where('taiKhoanId', $taiKhoan->taiKhoanId)
                    ->update(['roiAt' => now()]);
            }
        });
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

    private function memberIsOnlineRecently(ChatRoomMember $member): bool
    {
        return $member->lastSeenAt !== null
            && $member->lastSeenAt->gte(now()->subMinutes(2));
    }

    private function presenceLabelForMember(ChatRoomMember $member): string
    {
        if ($this->memberIsOnlineRecently($member)) {
            return 'Đang hoạt động';
        }

        if ($member->lastSeenAt) {
            return 'Hoạt động ' . $member->lastSeenAt->diffForHumans();
        }

        return 'Chưa hoạt động gần đây';
    }

    private function avatarUrlForAccount(?TaiKhoan $account): ?string
    {
        if (!$account) {
            return null;
        }

        $path = $account->hoSoNguoiDung?->anhDaiDien;

        if (is_string($path) && $path !== '') {
            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            return asset('storage/' . ltrim($path, '/'));
        }

        if (is_string($account->google_avatar) && $account->google_avatar !== '') {
            return $account->google_avatar;
        }

        return null;
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

    private function ensureRoomsForClassIds(Collection $classIds): void
    {
        LopHoc::query()
            ->whereIn('lopHocId', $classIds->all())
            ->get()
            ->each(function (LopHoc $lopHoc) {
                if ($lopHoc->canStudentJoinChat() || $lopHoc->isCompleted()) {
                    $this->findOrCreateClassRoom($lopHoc);
                }
            });
    }

    private function resolveDirectPeer(ChatRoom $room, TaiKhoan $taiKhoan): ?TaiKhoan
    {
        if (!$room->isDirect()) {
            return null;
        }

        $room->loadMissing(['members.taiKhoan.hoSoNguoiDung']);

        return optional(
            $room->members->first(function (ChatRoomMember $member) use ($taiKhoan) {
                return (int) $member->taiKhoanId !== (int) $taiKhoan->taiKhoanId;
            })
        )->taiKhoan;
    }

    private function makeInitials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn(string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'TV';
    }

    private function mapChatRoleLabel(?string $role, TaiKhoan $account): string
    {
        if ($role === ChatRoomMember::ROLE_TEACHER) {
            return 'Giáo viên';
        }

        if ($role === ChatRoomMember::ROLE_OWNER) {
            return 'Chủ phòng';
        }

        return $account->getRoleLabel();
    }

    private function resolveDirectContext(TaiKhoan $viewer, ?TaiKhoan $peer, ChatAccessService $accessService): ?array
    {
        if (!$peer) {
            return null;
        }

        $sharedClassIds = $accessService
            ->getAccessibleClassIds($viewer)
            ->intersect($accessService->getAccessibleClassIds($peer))
            ->values();

        if ($sharedClassIds->isEmpty()) {
            return null;
        }

        $class = LopHoc::query()
            ->with('khoaHoc')
            ->whereIn('lopHocId', $sharedClassIds->all())
            ->orderByDesc('lopHocId')
            ->first();

        if (!$class) {
            return null;
        }

        return [
            'className' => $class->tenLopHoc,
            'courseName' => optional($class->khoaHoc)->tenKhoaHoc,
            'label' => 'Kết nối qua lớp ' . $class->tenLopHoc,
        ];
    }
}
