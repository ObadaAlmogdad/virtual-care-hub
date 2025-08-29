<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintCreatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
     protected $complaint;

    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $user = $this->complaint->user;
        return [
            'complaint_id' => $this->complaint->id,
            'header'       => $this->complaint->header,
            'content'      => $this->complaint->content,
            'user_id'      => $user?->id,
            'user_name'    => $user?->fullName,
            'user_email'   => $user?->email,
            'user_role'    => $user?->role,
            'created_at'   => $this->complaint->created_at->toDateTimeString(),
            'message'      => 'تم إنشاء شكوى جديدة من ' . ($user?->role === 'Doctor' ? 'طبيب' : 'مريض') . ' - ' . $user?->fullName,
        ];
    }
}
