<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

// Thêm `implements ShouldBroadcast` để báo cho Laravel rằng Event này cần được phát sóng
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Event này sẽ chứa đối tượng Message mà chúng ta vừa tạo
    public Message $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Lấy kênh mà sự kiện sẽ được phát sóng trên đó.
     * Đây là một kênh riêng tư, chỉ những người được phép mới có thể nghe.
     * Tên kênh được tạo ra từ ID của người gửi và người nhận.
     * Chúng ta sắp xếp ID để đảm bảo tên kênh luôn duy nhất cho một cặp người dùng,
     * bất kể ai là người gửi. (ví dụ: chat.38.40)
     */
    public function broadcastOn(): PrivateChannel
    {
        $user1 = min($this->message->sender_id, $this->message->receiver_id);
        $user2 = max($this->message->sender_id, $this->message->receiver_id);
        
        // Tên kênh sẽ là "chat.{id_nho_hon}.{id_lon_hon}"
        return new PrivateChannel("chat.{$user1}.{$user2}");
    }

    /**
     * Dữ liệu sẽ được gửi đi trong sự kiện.
     * Chúng ta sẽ format lại để frontend dễ sử dụng.
     */
    public function broadcastWith(): array
    {
        // Tải thông tin người gửi để có tên và avatar
        $this->message->load('sender');

        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'created_at' => $this->message->created_at->toIso8601String(), // Format thời gian chuẩn
            'sender' => [
                'user_id' => $this->message->sender->user_id,
                'name' => $this->message->sender->display_name,
                'avatar' => $this->message->sender->avatar_url,
            ]
        ];
    }
}