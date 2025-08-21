<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\MessageSent;
use App\Events\NewMessageNotification; // Import event thông báo
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Lấy lịch sử tin nhắn giữa người dùng hiện tại và một người bạn.
     * Sửa đổi để nhận vào friendId thay vì User model.
     * 
     * @param int $friendId ID của người bạn đang chat cùng.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($friendId)
    {
        $currentUserId = Auth::id();

        // Kiểm tra xem người dùng có tồn tại không
        $friend = User::find($friendId);
        if (!$friend) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $messages = Message::where(function($q) use ($currentUserId, $friendId) {
            $q->where('sender_id', $currentUserId)->where('receiver_id', $friendId);
        })->orWhere(function($q) use ($currentUserId, $friendId) {
            $q->where('sender_id', $friendId)->where('receiver_id', $currentUserId);
        })
        ->with('sender') // Tải sẵn thông tin người gửi
        ->orderBy('created_at', 'asc')
        ->get();

        $formattedMessages = $messages->map(function ($message) {
            return $this->formatMessage($message);
        });
        
        // Trả về dữ liệu dưới một key 'messages' để nhất quán với ChatWindow.tsx
        return response()->json(['messages' => $formattedMessages]);
    }

    /**
     * Lưu một tin nhắn mới vào CSDL và phát sóng cả hai sự kiện.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => 'required|integer|exists:Users,user_id',
            'content' => 'required|string|max:1000',
        ]);
        
        // Không cho phép gửi tin nhắn cho chính mình
        if ($data['receiver_id'] == Auth::id()) {
            return response()->json(['message' => 'You cannot send a message to yourself.'], 400);
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $data['receiver_id'],
            'content' => $data['content'],
        ]);

        // Tải lại thông tin người gửi để sử dụng ngay sau đó
        $message->load('sender');

        // 1. Phát sóng sự kiện đến kênh chat để cập nhật UI
        broadcast(new MessageSent($message))->toOthers();
        
        // 2. Phát sóng sự kiện để gửi thông báo cho người nhận
        broadcast(new NewMessageNotification($message))->toOthers();

        // Format lại dữ liệu trả về cho client đã gửi request
        $responseData = $this->formatMessage($message);
        
        return response()->json($responseData, 201);
    }

    /**
     * Một hàm helper riêng để format một đối tượng Message thành mảng chuẩn.
     *
     * @param \App\Models\Message $message
     * @return array
     */
    private function formatMessage(Message $message): array
    {
        // Kiểm tra xem quan hệ sender đã được tải chưa
        if (!$message->relationLoaded('sender')) {
            $message->load('sender');
        }

        return [
            'id' => $message->id,
            'content' => $message->content,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'created_at' => $message->created_at->toIso8601String(),
            'sender' => $message->sender ? [ 
                'user_id' => $message->sender->user_id,
                'name' => $message->sender->display_name,
                'avatar' => $message->sender->avatar_url,
            ] : null,
        ];
    }
}