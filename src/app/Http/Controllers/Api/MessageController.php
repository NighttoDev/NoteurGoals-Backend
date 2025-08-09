<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Lấy lịch sử tin nhắn giữa người dùng hiện tại và một người bạn.
     * Sử dụng Route Model Binding để tự động tìm User từ ID trên URL.
     * 
     * @param \App\Models\User $friend Người bạn đang chat cùng.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(User $friend)
    {
        $currentUserId = Auth::id();
        $friendId = $friend->user_id;

        // Truy vấn để lấy tất cả tin nhắn giữa 2 người
        $messages = Message::where(function($q) use ($currentUserId, $friendId) {
            $q->where('sender_id', $currentUserId)->where('receiver_id', $friendId);
        })->orWhere(function($q) use ($currentUserId, $friendId) {
            $q->where('sender_id', $friendId)->where('receiver_id', $currentUserId);
        })
        ->with('sender') // Tải sẵn thông tin của người gửi để tối ưu hóa
        ->orderBy('created_at', 'asc')
        ->get();

        return response()->json($messages);
    }

    /**
     * Lưu một tin nhắn mới vào CSDL và phát sóng nó đi.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $data = $request->validate([
            'receiver_id' => 'required|exists:Users,user_id',
            'content' => 'required|string|max:1000',
        ]);

        // Tạo tin nhắn mới
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $data['receiver_id'],
            'content' => $data['content'],
        ]);

        // === BƯỚC QUAN TRỌNG NHẤT ===
        // Bắn ra sự kiện MessageSent. 
        // toOthers() đảm bảo sự kiện không được gửi lại cho chính người gửi.
        broadcast(new MessageSent($message))->toOthers();

        // Tải lại thông tin người gửi để trả về cho client của người gửi
        $message->load('sender');

        return response()->json($message, 201);
    }
}