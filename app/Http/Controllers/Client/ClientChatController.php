<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ChatAccessService;
use App\Services\ChatMessageService;
use App\Services\ChatRoomService;
use Illuminate\Http\Request;

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
        ChatMessageService $chatMessageService
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

        $newMessages = $chatMessageService
            ->getMessagesAfterForUser($selectedRoom, $user, $lastKnownMessageId)
            ->values();

        return response()->json([
            'status'   => 'ok',
            'roomId'   => $selectedRoomId,
            'messages' => $newMessages->all(),
            'room'     => $roomPayload,
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

        $messages = $chatMessageService->getMessagesForUser(
            $room,
            $user,
            $request->integer('before') ?: null
        );

        $lastMessageId = $messages->last()['id'] ?? null;
        if ($lastMessageId) {
            $chatMessageService->markRoomRead($room, $user, $lastMessageId);
            $roomPayload['unreadCount'] = 0;
        }

        return response()->json(['room' => $roomPayload, 'messages' => $messages]);
    }

    public function join(Request $request, int $roomId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService)
    {
        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canJoinRoom($user, $room)) {
            return response()->json(['message' => 'Bạn không có quyền tham gia nhóm chat này.'], 403);
        }

        $chatRoomService->joinClassRoom($room, $user);
        $room->refresh();

        return response()->json([
            'message' => 'Tham gia nhóm chat thành công.',
            'room'    => $chatRoomService->buildRoomPayload($room, $user, $chatAccessService),
        ]);
    }

    public function send(Request $request, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $validated = $request->validate([
            'roomId'  => 'required|integer',
            'message' => 'required|string|max:2000',
        ]);

        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser((int) $validated['roomId'], $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canSendMessage($user, $room)) {
            return response()->json(['message' => 'Bạn chưa thể gửi tin nhắn trong nhóm chat này.'], 403);
        }

        $message = $chatMessageService->sendTextMessage($room, $user, $validated['message']);
        $room->refresh();

        return response()->json([
            'message'     => 'Đã gửi tin nhắn.',
            'chatMessage' => $message,
            'room'        => $chatRoomService->buildRoomPayload($room, $user, $chatAccessService),
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
