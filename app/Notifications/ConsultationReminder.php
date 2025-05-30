<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Consultation;

class ConsultationReminder extends Notification
{
    use Queueable;

    protected $consultation;

    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $isDoctor = $notifiable->id === $this->consultation->doctor->user_id;
        $otherParty = $isDoctor ? $this->consultation->user : $this->consultation->doctor->user;
        
        return (new MailMessage)
            ->subject('Consultation Reminder')
            ->greeting('Hello ' . $notifiable->fullName)
            ->line('This is a reminder about your upcoming consultation.')
            ->line('Consultation Details:')
            ->line('Date: ' . $this->consultation->scheduled_at->format('Y-m-d H:i'))
            ->line('With: ' . $otherParty->fullName)
            ->line('Problem: ' . $this->consultation->problem)
            ->action('View Consultation', url('/consultations/' . $this->consultation->id))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {
        return [
            'consultation_id' => $this->consultation->id,
            'scheduled_at' => $this->consultation->scheduled_at,
            'message' => 'You have an upcoming consultation scheduled for ' . 
                        $this->consultation->scheduled_at->format('Y-m-d H:i')
        ];
    }
} 