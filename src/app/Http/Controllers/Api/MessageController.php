<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class MessageController extends Controller
{
    /**
     * Lấy lịch sử tin nhắn giữa người dùng hiện tại và một người bạn.
     * Đã được sửa để format lại dữ liệu trả về cho đồng nhất.
     * 
     * @param \App\Models\User $friend Người bạn đang chat cùng.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(User $friend)
    {
        $currentUserId = Auth::id();
        $friendId = $friend->user_id;

        $messages = Message::where(function($q) use ($currentUserId, $friendId) {
            $q->where('sender_id', $currentUserId)->where('receiver_id', $friendId);
        })->orWhere(function($q) use ($currentUserId, $friendId) {
            $q->where('sender_id', $friendId)->where('receiver_id', $currentUserId);
        })
        ->with('sender') // Tải sẵn thông tin người gửi
        ->orderBy('created_at', 'asc')
        ->get();

        // === SỬA LỖI Ở ĐÂY: FORMAT LẠI COLLECTION KẾT QUẢ ===
        // Sử dụng map để lặp qua từng tin nhắn và tạo ra cấu trúc JSON mong muốn
        $formattedMessages = $messages->map(function ($message) {
            return $this->formatMessage($message);
        });

        return response()->json($formattedMessages);
    }

    /**
     * Lưu một tin nhắn mới vào CSDL và phát sóng nó đi.
     * Đã được sửa để format lại dữ liệu trả về cho đồng nhất.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => 'required|exists:Users,user_id',
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $data['receiver_id'],
            'content' => $data['content'],
        ]);

        // Phát sóng sự kiện đến các client khác (không thay đổi)
        broadcast(new MessageSent($message))->toOthers();

        // Tải lại thông tin người gửi để sử dụng ngay sau đó
        $message->load('sender');

        // === SỬA LỖI Ở ĐÂY: FORMAT LẠI DỮ LIỆU TRẢ VỀ ===
        // Gọi hàm helper để tạo ra cấu trúc JSON chuẩn
        $responseData = $this->formatMessage($message);
        
        return response()->json($responseData, 201);
    }

    /**
     * Một hàm helper riêng để format một đối tượng Message thành mảng chuẩn.
     * Điều này giúp tránh lặp lại code và đảm bảo tính đồng nhất.
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
            'sender' => $message->sender ? [ // Kiểm tra sender tồn tại
                'user_id' => $message->sender->user_id,
                'name' => $message->sender->display_name,
                'avatar' => $message->sender->avatar_url,
            ] : null, // Trả về null nếu không có thông tin sender
        ];
    }
}