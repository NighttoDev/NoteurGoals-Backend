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

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
     * Kênh này dành riêng cho cuộc trò chuyện giữa 2 người.
     */
    public function broadcastOn(): PrivateChannel
    {
        $user1 = min($this->message->sender_id, $this->message->receiver_id);
        $user2 = max($this->message->sender_id, $this->message->receiver_id);
        
        return new PrivateChannel("chat.{$user1}.{$user2}");
    }
    
    /**
     * Tên của sự kiện khi được phát sóng.
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    /**
     * Dữ liệu sẽ được gửi đi trong sự kiện cho cửa sổ chat.
     */
    public function broadcastWith(): array
    {
        // Tải thông tin người gửi để có tên và avatar
        $this->message->load('sender');

        return [
            // Bọc trong 'message' để nhất quán với cách frontend `normalizeMessage` xử lý
            'message' => [ 
                'id' => $this->message->id,
                'content' => $this->message->content,
                'sender_id' => $this->message->sender_id,
                'receiver_id' => $this->message->receiver_id,
                'created_at' => $this->message->created_at->toIso8601String(),
                'sender' => [
                    'user_id' => $this->message->sender->user_id,
                    'name' => $this->message->sender->display_name,
                    'avatar' => $this->message->sender->avatar_url,
                ]
            ]
        ];
    }
}