<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(protected ChatbotService $chatbotService)
    {
    }

    /**
     * Xử lý tin nhắn từ học viên và trả về phản hồi AI
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user    = auth()->user();
        $message = trim($request->input('message'));

        $context = $this->chatbotService->buildContext($user);
        $reply   = $this->chatbotService->chat($message, $context);

        return response()->json(['reply' => $reply]);
    }
}
