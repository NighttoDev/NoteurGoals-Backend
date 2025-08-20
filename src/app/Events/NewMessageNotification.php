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

class NewMessageNotification implements ShouldBroadcast
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
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): PrivateChannel
    {
        // Gửi thông báo đến kênh riêng tư của người NHẬN
        return new PrivateChannel('App.User.' . $this->message->receiver_id);
    }

    /**
     * The event's broadcast name.
     * Đặt tên tường minh cho event để frontend dễ dàng lắng nghe
     */
    public function broadcastAs(): string
    {
        return 'NewMessageNotification';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        // Tải thông tin người gửi để có tên và avatar
        $this->message->load('sender');

        return [
            'sender_id' => $this->message->sender->user_id,
            'sender_name' => $this->message->sender->display_name,
            'message_content' => $this->message->content,
            'sender_avatar' => $this->message->sender->avatar_url,
        ];
    }
}