<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Interaction\Chat\ChatRoomMember;
use App\Services\ChatAccessService;
use App\Services\ChatMessageService;
use App\Services\ChatPresenceService;
use App\Services\ChatRoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClientChatController extends Controller
{
    public function index(Request $request, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService)
    {
        $user  = $request->user();
        $rooms = $chatRoomService->getVisibleRoomsForUser($user, $chatAccessService);

        $selectedRoomId = (int) $request->integer('room');
        if (!$selectedRoomId && $rooms->isNotEmpty()) {
            $selectedRoomId = (int) (($rooms->firstWhere('canAccess', true)['id'] ?? null) ?: $rooms->first()['id']);
        }

        $selectedRoomPayload = null;
        if ($selectedRoomId > 0) {
            $selectedRoom = $chatRoomService->getVisibleRoomForUser($selectedRoomId, $user, $chatAccessService);
            if ($selectedRoom) {
                $selectedRoomPayload = $chatRoomService->buildRoomPayload($selectedRoom, $user, $chatAccessService);
            }
        }

        return view('clients.hoc-vien.chat.index', [
            'rooms'        => $rooms->values(),
            'selectedRoom' => $selectedRoomPayload,
        ]);
    }

    public function rooms(Request $request, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService)
    {
        return response()->json([
            'rooms' => $chatRoomService->getVisibleRoomsForUser($request->user(), $chatAccessService),
        ]);
    }

    /**
     * Short-poll endpoint: Trả về NGAY LẬP TỨC – không có sleep().
     * Session được đóng trước khi query để không block request khác.
     * Client gọi endpoint này mỗi 1.5 giây.
     */
    public function poll(
        Request $request,
        ChatRoomService $chatRoomService,
        ChatAccessService $chatAccessService,
        ChatMessageService $chatMessageService,
        ChatPresenceService $chatPresenceService
    ) {
        // Đóng session NGAY – giải phóng lock để request khác không bị block
        if (session()->isStarted()) {
            session()->save();
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $user               = $request->user();
        $selectedRoomId     = (int) $request->integer('room');
        $lastKnownMessageId = max(0, (int) $request->integer('after'));

        if ($selectedRoomId <= 0) {
            return response()->json(['status' => 'no_room'], 200);
        }

        $selectedRoom = $chatRoomService->getVisibleRoomForUser($selectedRoomId, $user, $chatAccessService, false);
        if (!$selectedRoom) {
            return response()->json(['status' => 'not_found'], 200);
        }

        $roomPayload = $chatRoomService->buildRoomPayload($selectedRoom, $user, $chatAccessService);

        if (!$roomPayload['canAccess']) {
            return response()->json(['status' => 'no_access', 'room' => $roomPayload], 200);
        }

        $chatMessageService->markRoomRead($selectedRoom, $user, $lastKnownMessageId > 0 ? $lastKnownMessageId : null);
        $selectedRoom = $chatRoomService->getVisibleRoomForUser($selectedRoomId, $user, $chatAccessService, false) ?? $selectedRoom;
        $roomPayload = $chatRoomService->buildRoomPayload($selectedRoom, $user, $chatAccessService);

        $newMessages = $chatMessageService
            ->getMessagesAfterForUser($selectedRoom, $user, $lastKnownMessageId)
            ->values();

        return response()->json([
            'status'   => 'ok',
            'roomId'   => $selectedRoomId,
            'messages' => $newMessages->all(),
            'room'     => $roomPayload,
            'typingUsers' => $chatPresenceService->getTypingUsers($selectedRoom, $user),
        ]);
    }

    public function messages(Request $request, int $roomId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);

        abort_unless($room, 404);

        $roomPayload = $chatRoomService->buildRoomPayload($room, $user, $chatAccessService);
        if (!$roomPayload['canAccess']) {
            return response()->json(['message' => 'Bạn chưa tham gia nhóm chat này.', 'room' => $roomPayload], 403);
        }

        $member = ChatRoomMember::query()
            ->where('chatRoomId', $room->chatRoomId)
            ->where('taiKhoanId', $user->taiKhoanId)
            ->whereNull('roiAt')
            ->first();
        $readMarkerId = $member?->lastReadMessageId;

        $page = $chatMessageService->getMessagesPageForUser(
            $room,
            $user,
            $request->integer('before') ?: null
        );
        $messages = $page['messages'];

        $lastMessageId = $messages->last()['id'] ?? null;
        if ($lastMessageId) {
            $chatMessageService->markRoomRead($room, $user, $lastMessageId);
            $roomPayload['unreadCount'] = 0;
        }

        return response()->json([
            'room' => $roomPayload,
            'messages' => $messages,
            'hasMore' => (bool) ($page['hasMore'] ?? false),
            'readMarkerId' => $readMarkerId,
        ]);
    }

    public function members(Request $request, int $roomId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService)
    {
        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canAccessRoom($user, $room)) {
            return response()->json(['message' => 'Bạn chưa tham gia nhóm chat này.'], 403);
        }

        return response()->json([
            'members' => $chatRoomService->getRoomMembersPayload($room, $user, $chatAccessService),
        ]);
    }

    public function join(Request $request, int $roomId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canJoinRoom($user, $room)) {
            return response()->json(['message' => 'Bạn không có quyền tham gia nhóm chat này.'], 403);
        }

        $wasMember = \App\Models\Interaction\Chat\ChatRoomMember::query()
            ->where('chatRoomId', $room->chatRoomId)
            ->where('taiKhoanId', $user->taiKhoanId)
            ->whereNull('roiAt')
            ->exists();

        $chatRoomService->joinClassRoom($room, $user);

        if (!$wasMember && $room->isClassGroup()) {
            $displayName = optional($user->hoSoNguoiDung)->hoTen
                ?? $user->taiKhoan
                ?? 'Một thành viên';

            $chatMessageService->sendSystemMessage($room, $user, $displayName . ' đã tham gia đoạn chat.');
        }

        $room->refresh();

        return response()->json([
            'message' => 'Tham gia nhóm chat thành công.',
            'room'    => $chatRoomService->buildRoomPayload($room, $user, $chatAccessService),
        ]);
    }

    public function direct(Request $request, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService)
    {
        $validated = $request->validate([
            'targetUserId' => 'required|integer',
        ]);

        $user = $request->user();
        $targetUserId = (int) $validated['targetUserId'];

        if ((int) $user->taiKhoanId === $targetUserId) {
            return response()->json(['message' => 'Bạn không thể tự tạo đoạn chat riêng với chính mình.'], 422);
        }

        if (!$chatAccessService->canCreateDirectConversation($user, $targetUserId)) {
            return response()->json(['message' => 'Bạn không thể nhắn tin riêng với thành viên này.'], 403);
        }

        $targetUser = \App\Models\Auth\TaiKhoan::query()
            ->with('hoSoNguoiDung')
            ->findOrFail($targetUserId);

        $room = $chatRoomService->findOrCreateDirectRoom($user, $targetUser);

        return response()->json([
            'message' => 'Đã mở đoạn chat riêng.',
            'room' => $chatRoomService->buildRoomPayload($room, $user, $chatAccessService),
        ]);
    }

    public function search(Request $request, int $roomId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:120',
        ]);

        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canAccessRoom($user, $room)) {
            return response()->json(['message' => 'Bạn chưa tham gia nhóm chat này.'], 403);
        }

        return response()->json([
            'matches' => $chatMessageService
                ->searchMessagesForUser($room, $user, (string) $validated['q'])
                ->all(),
        ]);
    }

    public function typing(Request $request, int $roomId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatPresenceService $chatPresenceService)
    {
        $validated = $request->validate([
            'typing' => 'required|boolean',
        ]);

        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canSendMessage($user, $room)) {
            return response()->json(['message' => 'Bạn chưa thể gửi tin nhắn trong nhóm chat này.'], 403);
        }

        $chatPresenceService->setTyping($room, $user, (bool) $validated['typing']);

        return response()->json([
            'success' => true,
            'typingUsers' => $chatPresenceService->getTypingUsers($room, $user),
        ]);
    }

    public function viewAttachment(Request $request, int $id, ChatMessageService $chatMessageService)
    {
        $attachment = $chatMessageService->findVisibleAttachmentForUser($request->user(), $id);

        abort_unless($attachment, 404);

        $disk = Storage::disk($attachment->disk ?: 'public');
        $path = $request->query('variant') === 'thumbnail' && $attachment->thumbnailPath
            ? $attachment->thumbnailPath
            : $attachment->path;

        abort_unless($path && $disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => $disk->mimeType($path) ?: ($attachment->mime ?: 'application/octet-stream'),
            'Content-Disposition' => 'inline; filename="' . addslashes((string) $attachment->tenGoc) . '"',
        ]);
    }

    public function downloadAttachment(Request $request, int $id, ChatMessageService $chatMessageService)
    {
        $attachment = $chatMessageService->findVisibleAttachmentForUser($request->user(), $id);

        abort_unless($attachment, 404);

        $disk = Storage::disk($attachment->disk ?: 'public');
        $path = $attachment->path;

        abort_unless($path && $disk->exists($path), 404);

        return response()->download(
            $disk->path($path),
            (string) $attachment->tenGoc,
            [
                'Content-Type' => $attachment->mime ?: ($disk->mimeType($path) ?: 'application/octet-stream'),
            ]
        );
    }

    public function send(Request $request, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $validated = $request->validate([
            'roomId' => 'required|integer',
            'message' => 'nullable|string|max:2000',
            'replyToMessageId' => 'nullable|integer',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar',
        ]);

        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser((int) $validated['roomId'], $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canSendMessage($user, $room)) {
            return response()->json(['message' => 'Bạn chưa thể gửi tin nhắn trong nhóm chat này.'], 403);
        }

        $attachments = $request->file('attachments', []);
        $messageContent = trim((string) ($validated['message'] ?? ''));

        if ($messageContent === '' && empty($attachments)) {
            return response()->json(['message' => 'Vui lòng nhập nội dung hoặc chọn tệp đính kèm.'], 422);
        }

        $replyTo = null;
        if (!empty($validated['replyToMessageId'])) {
            $replyTo = $chatMessageService->findVisibleMessageForUser($room, $user, (int) $validated['replyToMessageId']);

            if (!$replyTo) {
                return response()->json(['message' => 'Không tìm thấy tin nhắn để trả lời.'], 422);
            }
        }

        $message = $chatMessageService->sendTextMessage($room, $user, $messageContent, $replyTo, $attachments);
        $room->refresh();

        return response()->json([
            'message'     => 'Đã gửi tin nhắn.',
            'chatMessage' => $message,
            'room'        => $chatRoomService->buildRoomPayload($room, $user, $chatAccessService),
        ]);
    }

    public function recall(Request $request, int $messageId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $user = $request->user();
        $roomId = (int) $request->integer('roomId');

        if ($roomId <= 0) {
            return response()->json(['message' => 'Thiếu thông tin phòng chat.'], 422);
        }

        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);
        abort_unless($room, 404);

        if (!$chatAccessService->canAccessRoom($user, $room)) {
            return response()->json(['message' => 'Bạn chưa tham gia nhóm chat này.'], 403);
        }

        $message = $chatMessageService->findVisibleMessageForUser($room, $user, $messageId);
        if (!$message) {
            return response()->json(['message' => 'Không tìm thấy tin nhắn cần thu hồi.'], 404);
        }

        $recalled = $chatMessageService->recallMessage($room, $user, $message);
        $room->refresh();

        return response()->json([
            'message' => 'Đã thu hồi tin nhắn.',
            'chatMessage' => $recalled,
            'room' => $chatRoomService->buildRoomPayload($room, $user, $chatAccessService),
        ]);
    }

    public function react(Request $request, int $messageId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $validated = $request->validate([
            'roomId' => 'required|integer',
            'emoji' => ['required', 'string', Rule::in(ChatMessageService::reactionEmojis())],
        ]);

        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser((int) $validated['roomId'], $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canAccessRoom($user, $room)) {
            return response()->json(['message' => 'Bạn chưa tham gia nhóm chat này.'], 403);
        }

        $message = $chatMessageService->findVisibleMessageForUser($room, $user, $messageId);
        if (!$message) {
            return response()->json(['message' => 'Không tìm thấy tin nhắn cần thả cảm xúc.'], 404);
        }

        $reaction = $chatMessageService->toggleReaction($room, $user, $message, (string) $validated['emoji']);
        $room->refresh();

        return response()->json([
            'message' => $reaction['reacted'] ? 'Đã thêm cảm xúc.' : 'Đã bỏ cảm xúc.',
            'chatMessage' => $reaction['chatMessage'],
            'reacted' => $reaction['reacted'],
            'room' => $chatRoomService->buildRoomPayload($room, $user, $chatAccessService),
        ]);
    }

    public function deleteForMe(Request $request, int $messageId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $validated = $request->validate([
            'roomId' => 'required|integer',
        ]);

        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser((int) $validated['roomId'], $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canAccessRoom($user, $room)) {
            return response()->json(['message' => 'Bạn chưa tham gia nhóm chat này.'], 403);
        }

        $message = $chatMessageService->findVisibleMessageForUser($room, $user, $messageId);
        if (!$message) {
            return response()->json(['message' => 'Không tìm thấy tin nhắn cần xóa.'], 404);
        }

        $chatMessageService->deleteMessageForUser($room, $user, $message);

        return response()->json([
            'message' => 'Đã xóa tin nhắn khỏi chế độ xem của bạn.',
            'deletedMessageId' => $messageId,
            'room' => $chatRoomService->buildRoomPayload($room, $user, $chatAccessService),
        ]);
    }

    public function markRead(Request $request, int $roomId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $validated = $request->validate(['lastMessageId' => 'nullable|integer']);

        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canAccessRoom($user, $room)) {
            return response()->json(['message' => 'Bạn chưa tham gia nhóm chat này.'], 403);
        }

        $chatMessageService->markRoomRead($room, $user, $validated['lastMessageId'] ?? null);

        return response()->json(['success' => true]);
    }
}
