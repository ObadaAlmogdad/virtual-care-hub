<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;

class AccountVerificationNotification extends Notification
{
    use Queueable;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $role = $this->user->role === 'doctor' ? 'طبيب' : 'مريض';
        
        return (new MailMessage)
            ->subject('تم تأكيد حسابك بنجاح - Virtual Care Hub')
            ->greeting('مرحباً ' . $this->user->fullName)
            ->line('يسرنا إعلامك بأن حسابك قد تم تأكيده بنجاح من قبل إدارة النظام.')
            ->line('تفاصيل الحساب:')
            ->line('الاسم: ' . $this->user->fullName)
            ->line('البريد الإلكتروني: ' . $this->user->email)
            ->line('نوع الحساب: ' . $role)
            ->line('يمكنك الآن الوصول إلى جميع خدمات المنصة.')
            ->action('تسجيل الدخول', url('/login'))
            ->line('شكراً لك على استخدام منصتنا!')
            ->salutation('مع تحيات فريق Virtual Care Hub');
    }

    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->user->id,
            'message' => 'تم تأكيد حساب المستخدم ' . $this->user->fullName . ' بنجاح',
            'verified_at' => now()
        ];
    }
} 