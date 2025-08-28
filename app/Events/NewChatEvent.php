<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;
    public $creator;

    public function __construct($chat, $creator)
    {
        $this->chat = $chat;
        $this->creator = $creator;
    }

    public function broadcastOn()
    {
        // إرسال للمريض فقط (الطبيب هو من أنشأ المحادثة)
        return new PrivateChannel('user.' . $this->chat->patient->user_id);
    }

    public function broadcastAs()
    {
        return 'NewChat';
    }

    public function broadcastWith()
    {
        return [
            'chat_id' => $this->chat->id,
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->fullName,
                'email' => $this->creator->email,
            ],
            'doctor' => [
                'id' => $this->chat->doctor->user->id,
                'name' => $this->chat->doctor->user->fullName,
            ],
            'patient' => [
                'id' => $this->chat->patient->user->id,
                'name' => $this->chat->patient->user->fullName,
            ],
            'timestamp' => $this->chat->created_at->toDateTimeString(),
        ];
    }
} 