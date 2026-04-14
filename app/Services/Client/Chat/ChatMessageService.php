<?php

namespace App\Services\Client\Chat;

use App\Models\Auth\TaiKhoan;
use App\Models\Interaction\Chat\ChatAuditLog;
use App\Models\Interaction\Chat\ChatMessage;
use App\Models\Interaction\Chat\ChatMessageAttachment;
use App\Models\Interaction\Chat\ChatMessageDelete;
use App\Models\Interaction\Chat\ChatMessageReaction;
use App\Models\Interaction\Chat\ChatRoom;
use App\Models\Interaction\Chat\ChatRoomMember;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChatMessageService
{
    private const RECALL_PLACEHOLDER = 'Tin nhắn đã được thu hồi';
    private const SUPPORTED_REACTIONS = ['👍', '❤️', '😂', '😮', '😢', '🔥', '😡'];
    private const COMPOSER_EMOJIS = [
        '😀', '😁', '😂', '🤣', '😊', '😍', '😘', '😎', '🤔', '😮',
        '😢', '😭', '😡', '👍', '👎', '👏', '🙏', '❤️', '💔', '🔥',
        '🎉', '🌟', '💯', '🤝', '👌', '🙌', '🥳', '😴', '🤯', '🤗',
    ];

    private array $roomReceiptMembers = [];

    public static function reactionEmojis(): array
    {
        return self::SUPPORTED_REACTIONS;
    }

    public static function composerEmojis(): array
    {
        return self::COMPOSER_EMOJIS;
    }

    private function messageRelations(): array
    {
        return [
            'nguoiGui.hoSoNguoiDung',
            'replyTo.nguoiGui.hoSoNguoiDung',
            'replyTo.attachments',
            'replyTo.deletes',
            'attachments',
            'reactions.taiKhoan.hoSoNguoiDung',
        ];
    }

    public function getMessagesForUser(ChatRoom $room, TaiKhoan $taiKhoan, ?int $beforeMessageId = null, int $limit = 50): Collection
    {
        return $this->getMessagesPageForUser($room, $taiKhoan, $beforeMessageId, $limit)['messages'];
    }

    public function getMessagesPageForUser(ChatRoom $room, TaiKhoan $taiKhoan, ?int $beforeMessageId = null, int $limit = 50): array
    {
        $messages = $this->buildVisibleMessageQuery($room, $taiKhoan, $beforeMessageId)
            ->orderByDesc('chatMessageId')
            ->limit($limit + 1)
            ->get();

        $hasMore = $messages->count() > $limit;
        if ($hasMore) {
            $messages = $messages->take($limit);
        }

        return [
            'messages' => $messages
                ->reverse()
                ->values()
                ->map(fn (ChatMessage $message) => $this->transformMessage($message, $taiKhoan)),
            'hasMore' => $hasMore,
        ];
    }

    public function getMessagesAfterForUser(ChatRoom $room, TaiKhoan $taiKhoan, int $afterMessageId = 0, int $limit = 50): Collection
    {
        return $this->buildVisibleMessageQuery($room, $taiKhoan)
            ->where('chatMessageId', '>', $afterMessageId)
            ->orderBy('chatMessageId')
            ->limit($limit)
            ->get()
            ->values()
            ->map(fn (ChatMessage $message) => $this->transformMessage($message, $taiKhoan));
    }

    public function findVisibleMessageForUser(ChatRoom $room, TaiKhoan $taiKhoan, int $messageId): ?ChatMessage
    {
        return $this->buildVisibleMessageQuery($room, $taiKhoan)
            ->where('chatMessageId', $messageId)
            ->first();
    }

    public function findVisibleAttachmentForUser(TaiKhoan $taiKhoan, int $attachmentId): ?ChatMessageAttachment
    {
        $attachment = ChatMessageAttachment::query()
            ->with([
                'message.room.lopHoc',
                'message.deletes',
            ])
            ->find($attachmentId);

        if (!$attachment || !$attachment->message || !$attachment->message->room) {
            return null;
        }

        if ($attachment->message->thuHoiLuc !== null) {
            return null;
        }

        if ($this->isMessageDeletedForUser($attachment->message, $taiKhoan)) {
            return null;
        }

        $accessService = app(ChatAccessService::class);

        return $accessService->canAccessRoom($taiKhoan, $attachment->message->room)
            ? $attachment
            : null;
    }

    public function searchMessagesForUser(ChatRoom $room, TaiKhoan $taiKhoan, string $query, int $limit = 20): Collection
    {
        $keyword = trim($query);
        if ($keyword === '') {
            return collect();
        }

        $like = '%' . $keyword . '%';

        return $this->buildVisibleMessageQuery($room, $taiKhoan)
            ->where(function ($messageQuery) use ($like) {
                $messageQuery
                    ->where('noiDung', 'like', $like)
                    ->orWhereHas('nguoiGui.hoSoNguoiDung', function ($senderQuery) use ($like) {
                        $senderQuery->where('hoTen', 'like', $like);
                    })
                    ->orWhereHas('nguoiGui', function ($senderQuery) use ($like) {
                        $senderQuery->where('taiKhoan', 'like', $like);
                    })
                    ->orWhereHas('attachments', function ($attachmentQuery) use ($like) {
                        $attachmentQuery->where('tenGoc', 'like', $like);
                    });
            })
            ->orderByDesc('chatMessageId')
            ->limit($limit)
            ->get()
            ->map(fn (ChatMessage $message) => $this->transformMessage($message, $taiKhoan))
            ->values();
    }

    public function sendTextMessage(
        ChatRoom $room,
        TaiKhoan $taiKhoan,
        ?string $content,
        ?ChatMessage $replyTo = null,
        array $attachments = []
    ): array {
        $content = trim((string) $content);
        $attachments = array_values(array_filter($attachments, fn ($file) => $file instanceof UploadedFile));

        if ($content === '' && empty($attachments)) {
            throw ValidationException::withMessages([
                'message' => 'Vui lòng nhập nội dung hoặc chọn tệp đính kèm.',
            ]);
        }

        $messageType = $this->resolveMessageType($content, $attachments);

        $message = DB::transaction(function () use ($room, $taiKhoan, $content, $replyTo, $attachments, $messageType) {
            $message = ChatMessage::query()->create([
                'chatRoomId' => $room->chatRoomId,
                'nguoiGuiId' => $taiKhoan->taiKhoanId,
                'replyToMessageId' => $replyTo?->chatMessageId,
                'loai' => $messageType,
                'noiDung' => $content !== '' ? $content : null,
                'guiLuc' => now(),
                'deadlineThuHoi' => now()->addDay(),
            ]);

            $storedAttachments = $this->storeAttachments($message, $attachments);

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

            ChatAuditLog::query()->create([
                'chatRoomId' => $room->chatRoomId,
                'chatMessageId' => $message->chatMessageId,
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'hanhDong' => 'message.sent',
                'duLieuMoi' => [
                    'type' => $message->loai,
                    'replyToMessageId' => $replyTo?->chatMessageId,
                    'attachmentCount' => $storedAttachments->count(),
                ],
                'created_at' => now(),
            ]);

            return $message->fresh($this->messageRelations());
        });

        return $this->transformMessage($message, $taiKhoan);
    }

    public function sendSystemMessage(ChatRoom $room, TaiKhoan $actor, string $content): ChatMessage
    {
        return DB::transaction(function () use ($room, $actor, $content) {
            $message = ChatMessage::query()->create([
                'chatRoomId' => $room->chatRoomId,
                'nguoiGuiId' => $actor->taiKhoanId,
                'loai' => ChatMessage::TYPE_SYSTEM,
                'noiDung' => trim($content),
                'guiLuc' => now(),
            ]);

            $room->forceFill([
                'lastMessageId' => $message->chatMessageId,
                'updated_at' => now(),
            ])->save();

            ChatAuditLog::query()->create([
                'chatRoomId' => $room->chatRoomId,
                'chatMessageId' => $message->chatMessageId,
                'taiKhoanId' => $actor->taiKhoanId,
                'hanhDong' => 'message.system',
                'duLieuMoi' => [
                    'content' => trim($content),
                ],
                'created_at' => now(),
            ]);

            return $message->fresh($this->messageRelations());
        });
    }

    public function recallMessage(ChatRoom $room, TaiKhoan $taiKhoan, ChatMessage $message): array
    {
        if ((int) $message->chatRoomId !== (int) $room->chatRoomId) {
            throw ValidationException::withMessages([
                'messageId' => 'Tin nhắn không thuộc phòng chat đã chọn.',
            ]);
        }

        if (!$this->canRecallMessage($message, $taiKhoan)) {
            throw ValidationException::withMessages([
                'messageId' => 'Tin nhắn này không còn có thể thu hồi.',
            ]);
        }

        $message = DB::transaction(function () use ($room, $taiKhoan, $message) {
            $oldData = [
                'content' => $message->noiDung,
                'thuHoiLuc' => optional($message->thuHoiLuc)?->toIso8601String(),
            ];

            $message->forceFill([
                'thuHoiLuc' => now(),
                'updated_at' => now(),
            ])->save();

            $message->reactions()->delete();

            $room->forceFill([
                'updated_at' => now(),
            ])->save();

            ChatAuditLog::query()->create([
                'chatRoomId' => $room->chatRoomId,
                'chatMessageId' => $message->chatMessageId,
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'hanhDong' => 'message.recalled',
                'duLieuCu' => $oldData,
                'duLieuMoi' => [
                    'content' => self::RECALL_PLACEHOLDER,
                    'thuHoiLuc' => now()->toIso8601String(),
                ],
                'created_at' => now(),
            ]);

            return $message->fresh($this->messageRelations());
        });

        return $this->transformMessage($message, $taiKhoan);
    }

    public function toggleReaction(ChatRoom $room, TaiKhoan $taiKhoan, ChatMessage $message, string $emoji): array
    {
        if ((int) $message->chatRoomId !== (int) $room->chatRoomId) {
            throw ValidationException::withMessages([
                'messageId' => 'Tin nhắn không thuộc phòng chat đã chọn.',
            ]);
        }

        if ($message->thuHoiLuc !== null) {
            throw ValidationException::withMessages([
                'messageId' => 'Không thể thả cảm xúc cho tin nhắn đã thu hồi.',
            ]);
        }

        if ($message->loai === ChatMessage::TYPE_SYSTEM) {
            throw ValidationException::withMessages([
                'messageId' => 'Không thể thả cảm xúc cho tin nhắn hệ thống.',
            ]);
        }

        if (!in_array($emoji, self::reactionEmojis(), true)) {
            throw ValidationException::withMessages([
                'emoji' => 'Cảm xúc không hợp lệ.',
            ]);
        }

        $result = DB::transaction(function () use ($room, $taiKhoan, $message, $emoji) {
            $existingReaction = ChatMessageReaction::query()
                ->where('chatMessageId', $message->chatMessageId)
                ->where('taiKhoanId', $taiKhoan->taiKhoanId)
                ->where('emoji', $emoji)
                ->first();

            $reacted = $existingReaction === null;

            if ($existingReaction) {
                $existingReaction->delete();
            } else {
                ChatMessageReaction::query()->create([
                    'chatMessageId' => $message->chatMessageId,
                    'taiKhoanId' => $taiKhoan->taiKhoanId,
                    'emoji' => $emoji,
                ]);
            }

            $room->forceFill([
                'updated_at' => now(),
            ])->save();

            ChatAuditLog::query()->create([
                'chatRoomId' => $room->chatRoomId,
                'chatMessageId' => $message->chatMessageId,
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'hanhDong' => $reacted ? 'message.reaction_added' : 'message.reaction_removed',
                'duLieuMoi' => [
                    'emoji' => $emoji,
                    'reacted' => $reacted,
                ],
                'created_at' => now(),
            ]);

            return [
                'chatMessage' => $message->fresh($this->messageRelations()),
                'reacted' => $reacted,
            ];
        });

        return [
            'chatMessage' => $this->transformMessage($result['chatMessage'], $taiKhoan),
            'reacted' => $result['reacted'],
        ];
    }

    public function deleteMessageForUser(ChatRoom $room, TaiKhoan $taiKhoan, ChatMessage $message): void
    {
        if ((int) $message->chatRoomId !== (int) $room->chatRoomId) {
            throw ValidationException::withMessages([
                'messageId' => 'Tin nhắn không thuộc phòng chat đã chọn.',
            ]);
        }

        if ($message->loai === ChatMessage::TYPE_SYSTEM) {
            throw ValidationException::withMessages([
                'messageId' => 'Không thể xóa tin nhắn hệ thống khỏi chế độ xem cá nhân.',
            ]);
        }

        DB::transaction(function () use ($room, $taiKhoan, $message) {
            ChatMessageDelete::query()->updateOrCreate(
                [
                    'chatMessageId' => $message->chatMessageId,
                    'taiKhoanId' => $taiKhoan->taiKhoanId,
                ],
                [
                    'deletedAt' => now(),
                    'created_at' => now(),
                ]
            );

            ChatAuditLog::query()->create([
                'chatRoomId' => $room->chatRoomId,
                'chatMessageId' => $message->chatMessageId,
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'hanhDong' => 'message.deleted_for_me',
                'created_at' => now(),
            ]);
        });
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

        $replyTo = null;
        if ($message->replyTo && !$this->isMessageDeletedForUser($message->replyTo, $taiKhoan)) {
            $replyTo = [
                'id' => $message->replyTo->chatMessageId,
                'senderName' => optional(optional($message->replyTo->nguoiGui)->hoSoNguoiDung)->hoTen
                    ?? optional($message->replyTo->nguoiGui)->taiKhoan
                    ?? 'Người dùng',
                'content' => Str::limit($this->previewTextForMessage($message->replyTo), 60),
                'isRecalled' => $message->replyTo->thuHoiLuc !== null,
            ];
        }

        return [
            'id' => $message->chatMessageId,
            'roomId' => $message->chatRoomId,
            'type' => $message->loai,
            'content' => $message->thuHoiLuc ? self::RECALL_PLACEHOLDER : (string) $message->noiDung,
            'isMine' => (int) $message->nguoiGuiId === (int) $taiKhoan->taiKhoanId,
            'senderId' => $message->nguoiGuiId,
            'senderName' => $senderName,
            'senderAvatarUrl' => $this->avatarUrlForAccount($sender),
            'replyTo' => $replyTo,
            'isRecalled' => $message->thuHoiLuc !== null,
            'isSystem' => $message->loai === ChatMessage::TYPE_SYSTEM,
            'sentAt' => optional($message->guiLuc ?? $message->created_at)?->toIso8601String(),
            'sentAtLabel' => optional($message->guiLuc ?? $message->created_at)?->format('H:i d/m/Y'),
            'canRecall' => $message->loai !== ChatMessage::TYPE_SYSTEM
                && $message->thuHoiLuc === null
                && (int) $message->nguoiGuiId === (int) $taiKhoan->taiKhoanId
                && optional($message->deadlineThuHoi)?->isFuture(),
            'canDeleteForMe' => $message->loai !== ChatMessage::TYPE_SYSTEM,
            'attachments' => $this->transformAttachments($message),
            'reactions' => $this->transformReactions($message, $taiKhoan),
            'receipt' => $this->buildReceiptPayload($message, $taiKhoan),
        ];
    }

    public function canRecallMessage(ChatMessage $message, TaiKhoan $taiKhoan): bool
    {
        return $message->loai !== ChatMessage::TYPE_SYSTEM
            && $message->thuHoiLuc === null
            && (int) $message->nguoiGuiId === (int) $taiKhoan->taiKhoanId
            && optional($message->deadlineThuHoi)?->isFuture();
    }

    private function buildVisibleMessageQuery(ChatRoom $room, TaiKhoan $taiKhoan, ?int $beforeMessageId = null)
    {
        $query = ChatMessage::query()
            ->with($this->messageRelations())
            ->where('chatRoomId', $room->chatRoomId)
            ->whereDoesntHave('deletes', function ($subQuery) use ($taiKhoan) {
                $subQuery->where('taiKhoanId', $taiKhoan->taiKhoanId);
            });

        if ($beforeMessageId) {
            $query->where('chatMessageId', '<', $beforeMessageId);
        }

        return $query;
    }

    private function resolveMessageType(string $content, array $attachments): string
    {
        if ($content !== '') {
            return ChatMessage::TYPE_TEXT;
        }

        if (empty($attachments)) {
            return ChatMessage::TYPE_TEXT;
        }

        $allImages = collect($attachments)->every(function (UploadedFile $file) {
            return str_starts_with((string) $file->getMimeType(), 'image/');
        });

        return $allImages ? ChatMessage::TYPE_IMAGE : ChatMessage::TYPE_FILE;
    }

    private function storeAttachments(ChatMessage $message, array $attachments): Collection
    {
        return collect($attachments)->map(function (UploadedFile $file) use ($message) {
            $extension = strtolower((string) $file->getClientOriginalExtension());
            $fileName = now()->format('YmdHis') . '_' . Str::random(20) . ($extension ? '.' . $extension : '');
            $path = $file->storeAs('chat/messages/' . now()->format('Y/m'), $fileName, 'public');

            $width = null;
            $height = null;

            if (str_starts_with((string) $file->getMimeType(), 'image/')) {
                $dimensions = @getimagesize($file->getRealPath() ?: '');
                if (is_array($dimensions)) {
                    $width = $dimensions[0] ?? null;
                    $height = $dimensions[1] ?? null;
                }
            }

            return ChatMessageAttachment::query()->create([
                'chatMessageId' => $message->chatMessageId,
                'disk' => 'public',
                'path' => $path,
                'thumbnailPath' => null,
                'tenGoc' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'width' => $width,
                'height' => $height,
            ]);
        });
    }

    private function transformAttachments(ChatMessage $message): array
    {
        if ($message->thuHoiLuc !== null) {
            return [];
        }

        $attachments = $message->relationLoaded('attachments')
            ? $message->attachments
            : $message->attachments()->get();

        return $attachments
            ->map(function (ChatMessageAttachment $attachment) {
                return [
                    'id' => $attachment->chatAttachmentId,
                    'name' => $attachment->tenGoc,
                    'mime' => $attachment->mime,
                    'size' => (int) $attachment->size,
                    'isImage' => str_starts_with((string) $attachment->mime, 'image/'),
                    'width' => $attachment->width,
                    'height' => $attachment->height,
                    'url' => route('home.api.chat.attachments.view', ['id' => $attachment->chatAttachmentId]),
                    'downloadUrl' => route('home.api.chat.attachments.download', ['id' => $attachment->chatAttachmentId]),
                    'thumbnailUrl' => $attachment->thumbnailPath
                        ? route('home.api.chat.attachments.view', [
                            'id' => $attachment->chatAttachmentId,
                            'variant' => 'thumbnail',
                        ])
                        : null,
                ];
            })
            ->values()
            ->all();
    }

    private function previewTextForMessage(ChatMessage $message): string
    {
        if ($message->thuHoiLuc !== null) {
            return self::RECALL_PLACEHOLDER;
        }

        if (trim((string) $message->noiDung) !== '') {
            return (string) $message->noiDung;
        }

        $attachments = $message->relationLoaded('attachments')
            ? $message->attachments
            : $message->attachments()->get();

        if ($attachments->isEmpty()) {
            return '';
        }

        $allImages = $attachments->every(function (ChatMessageAttachment $attachment) {
            return str_starts_with((string) $attachment->mime, 'image/');
        });

        return $allImages ? '[Ảnh đính kèm]' : '[Tệp đính kèm]';
    }

    private function buildReceiptPayload(ChatMessage $message, TaiKhoan $viewer): ?array
    {
        if (
            $message->loai === ChatMessage::TYPE_SYSTEM ||
            (int) $message->nguoiGuiId !== (int) $viewer->taiKhoanId
        ) {
            return null;
        }

        $sentAt = $message->guiLuc ?? $message->created_at ?? now();
        $members = $this->roomMembersForReceipts($message->chatRoomId)
            ->filter(function (ChatRoomMember $member) use ($message) {
                return (int) $member->taiKhoanId !== (int) $message->nguoiGuiId
                    && $member->roiAt === null;
            })
            ->values();

        $deliveredUsers = [];
        $seenUsers = [];

        foreach ($members as $member) {
            $participant = $this->receiptParticipantPayload($member);
            if (!$participant) {
                continue;
            }

            $lastSeenAt = $member->lastSeenAt;
            $hasDelivered = $lastSeenAt !== null && $lastSeenAt->gte($sentAt);
            $hasSeen = (int) ($member->lastReadMessageId ?? 0) >= (int) $message->chatMessageId;

            if ($hasDelivered) {
                $deliveredUsers[] = [
                    ...$participant,
                    'at' => $lastSeenAt?->toIso8601String(),
                    'atLabel' => $lastSeenAt?->diffForHumans(),
                ];
            }

            if ($hasSeen) {
                $seenUsers[] = [
                    ...$participant,
                    'at' => $lastSeenAt?->toIso8601String(),
                    'atLabel' => $lastSeenAt?->diffForHumans(),
                ];
            }
        }

        $primaryUsers = !empty($seenUsers) ? $seenUsers : $deliveredUsers;
        $status = !empty($seenUsers)
            ? 'seen'
            : (!empty($deliveredUsers) ? 'delivered' : 'sent');

        return [
            'status' => $status,
            'statusLabel' => match ($status) {
                'seen' => 'Đã xem',
                'delivered' => 'Đã nhận',
                default => 'Đã gửi',
            },
            'sentBy' => [
                [
                    'id' => $viewer->taiKhoanId,
                    'name' => optional($viewer->hoSoNguoiDung)->hoTen ?? $viewer->taiKhoan ?? 'Bạn',
                    'avatarUrl' => $this->avatarUrlForAccount($viewer),
                    'at' => $sentAt?->toIso8601String(),
                    'atLabel' => $sentAt?->diffForHumans(),
                ],
            ],
            'deliveredUsers' => $deliveredUsers,
            'seenUsers' => $seenUsers,
            'deliveredCount' => count($deliveredUsers),
            'seenCount' => count($seenUsers),
            'previewUsers' => array_slice($primaryUsers, 0, 3),
            'remainingCount' => max(0, count($primaryUsers) - 3),
        ];
    }

    private function isMessageDeletedForUser(ChatMessage $message, TaiKhoan $taiKhoan): bool
    {
        $deletes = $message->relationLoaded('deletes')
            ? $message->deletes
            : $message->deletes()->get();

        return $deletes->contains(
            fn (ChatMessageDelete $delete) => (int) $delete->taiKhoanId === (int) $taiKhoan->taiKhoanId
        );
    }

    private function transformReactions(ChatMessage $message, TaiKhoan $taiKhoan): array
    {
        if ($message->thuHoiLuc !== null || $message->loai === ChatMessage::TYPE_SYSTEM) {
            return [];
        }

        $reactions = $message->relationLoaded('reactions')
            ? $message->reactions
            : $message->reactions()->get();

        if ($reactions->isEmpty()) {
            return [];
        }

        $grouped = $reactions
            ->groupBy('emoji')
            ->map(function (Collection $items, string $emoji) use ($taiKhoan) {
                $userNames = $items
                    ->map(function (ChatMessageReaction $reaction) {
                        return optional(optional($reaction->taiKhoan)->hoSoNguoiDung)->hoTen
                            ?? optional($reaction->taiKhoan)->taiKhoan
                            ?? 'Người dùng';
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'emoji' => $emoji,
                    'count' => $items->count(),
                    'reactedByMe' => $items->contains(
                        fn (ChatMessageReaction $reaction) => (int) $reaction->taiKhoanId === (int) $taiKhoan->taiKhoanId
                    ),
                    'userNames' => $userNames,
                ];
            });

        $reactionOrder = array_flip(self::reactionEmojis());

        return $grouped
            ->sortBy(fn (array $item) => $reactionOrder[$item['emoji']] ?? (count($reactionOrder) + 100))
            ->values()
            ->all();
    }

    private function roomMembersForReceipts(int $roomId): Collection
    {
        if (!array_key_exists($roomId, $this->roomReceiptMembers)) {
            $this->roomReceiptMembers[$roomId] = ChatRoomMember::query()
                ->with('taiKhoan.hoSoNguoiDung')
                ->where('chatRoomId', $roomId)
                ->whereNull('roiAt')
                ->get();
        }

        return $this->roomReceiptMembers[$roomId];
    }

    private function receiptParticipantPayload(ChatRoomMember $member): ?array
    {
        $account = $member->taiKhoan;
        if (!$account) {
            return null;
        }

        return [
            'id' => $account->taiKhoanId,
            'name' => optional($account->hoSoNguoiDung)->hoTen
                ?? $account->taiKhoan
                ?? 'Người dùng',
            'avatarUrl' => $this->avatarUrlForAccount($account),
        ];
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
}
