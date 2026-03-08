<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ChatAccessService;
use App\Services\ChatMessageService;
use App\Services\ChatRoomService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClientChatController extends Controller
{
    public function index(Request $request, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $user = $request->user();
        $rooms = $chatRoomService->getVisibleRoomsForUser($user, $chatAccessService);

        $selectedRoomId = (int) $request->integer('room');
        if (!$selectedRoomId && $rooms->isNotEmpty()) {
            $selectedRoomId = (int) (($rooms->firstWhere('canAccess', true)['id'] ?? null) ?: $rooms->first()['id']);
        }

        $selectedRoomPayload = null;
        $initialMessages = collect();

        if ($selectedRoomId > 0) {
            $selectedRoom = $chatRoomService->getVisibleRoomForUser($selectedRoomId, $user, $chatAccessService);

            if ($selectedRoom) {
                $selectedRoomPayload = $chatRoomService->buildRoomPayload($selectedRoom, $user, $chatAccessService);

                if ($selectedRoomPayload['canAccess']) {
                    $initialMessages = $chatMessageService->getMessagesForUser($selectedRoom, $user);
                    $lastMessageId = $initialMessages->last()['id'] ?? null;

                    if ($lastMessageId) {
                        $chatMessageService->markRoomRead($selectedRoom, $user, $lastMessageId);
                        $selectedRoomPayload['unreadCount'] = 0;
                        $rooms = $rooms->map(function (array $room) use ($selectedRoomId) {
                            if ((int) $room['id'] === (int) $selectedRoomId) {
                                $room['unreadCount'] = 0;
                            }
                            return $room;
                        });
                    }
                }
            }
        }

        return view('clients.hoc-vien.chat.index', [
            'rooms' => $rooms->values(),
            'selectedRoom' => $selectedRoomPayload,
            'initialMessages' => $initialMessages->values(),
        ]);
    }

    public function rooms(Request $request, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService)
    {
        return response()->json([
            'rooms' => $chatRoomService->getVisibleRoomsForUser($request->user(), $chatAccessService),
        ]);
    }

    public function messages(Request $request, int $roomId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);

        abort_unless($room, 404);

        $roomPayload = $chatRoomService->buildRoomPayload($room, $user, $chatAccessService);
        if (!$roomPayload['canAccess']) {
            return response()->json([
                'message' => 'Bạn chưa tham gia nhóm chat này.',
                'room' => $roomPayload,
            ], 403);
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

        return response()->json([
            'room' => $roomPayload,
            'messages' => $messages,
        ]);
    }

    public function join(Request $request, int $roomId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService)
    {
        $validated = $request->validate([
            'password' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canJoinRoom($user, $room)) {
            return response()->json([
                'message' => 'Bạn không có quyền tham gia nhóm chat này.',
            ], 403);
        }

        if (!$chatRoomService->roomPasswordMatches($room, $validated['password'] ?? null)) {
            throw ValidationException::withMessages([
                'password' => 'Mật khẩu nhóm chat không đúng.',
            ]);
        }

        $chatRoomService->joinClassRoom($room, $user, $validated['password'] ?? null);
        $room->refresh();

        return response()->json([
            'message' => 'Tham gia nhóm chat thành công.',
            'room' => $chatRoomService->buildRoomPayload($room, $user, $chatAccessService),
        ]);
    }

    public function send(Request $request, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $validated = $request->validate([
            'roomId' => 'required|integer',
            'message' => 'required|string|max:2000',
        ]);

        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser((int) $validated['roomId'], $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canSendMessage($user, $room)) {
            return response()->json([
                'message' => 'Bạn chưa thể gửi tin nhắn trong nhóm chat này.',
            ], 403);
        }

        $message = $chatMessageService->sendTextMessage($room, $user, $validated['message']);
        $room->refresh();

        return response()->json([
            'message' => 'Đã gửi tin nhắn.',
            'chatMessage' => $message,
            'room' => $chatRoomService->buildRoomPayload($room, $user, $chatAccessService),
        ]);
    }

    public function markRead(Request $request, int $roomId, ChatRoomService $chatRoomService, ChatAccessService $chatAccessService, ChatMessageService $chatMessageService)
    {
        $validated = $request->validate([
            'lastMessageId' => 'nullable|integer',
        ]);

        $user = $request->user();
        $room = $chatRoomService->getVisibleRoomForUser($roomId, $user, $chatAccessService);

        abort_unless($room, 404);

        if (!$chatAccessService->canAccessRoom($user, $room)) {
            return response()->json([
                'message' => 'Bạn chưa tham gia nhóm chat này.',
            ], 403);
        }

        $chatMessageService->markRoomRead($room, $user, $validated['lastMessageId'] ?? null);

        return response()->json([
            'success' => true,
        ]);
    }
}
