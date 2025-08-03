<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // تحديد المستلم (الطرف الآخر في المحادثة)
        $chat = $this->message->chat;
        $recipientUserId = $chat->doctor->user_id == $this->message->sender_id
            ? $chat->patient->user_id
            : $chat->doctor->user_id;

        return new PrivateChannel('user.' . $recipientUserId);
    }

    public function broadcastAs()
    {
        return 'NewMessage';
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->message->id,
            'chat_id' => $this->message->chat_id,
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->fullName,
                'email' => $this->message->sender->email,
                'photo' => $this->message->sender->photoPath,
            ],
            'content' => $this->message->message_content,
            'type' => $this->message->message_type,
            'timestamp' => $this->message->created_at->toDateTimeString(),
            'file_url' => $this->message->file_path
                ? Storage::url($this->message->file_path)
                : null,
            'file_path' => $this->message->file_path,
        ];
    }
}
