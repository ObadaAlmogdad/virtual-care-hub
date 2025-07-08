<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Consultation;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class SendConsultationReminders extends Command
{
    protected $signature = 'consultations:send-reminders';
    protected $description = 'Send reminders for upcoming consultations';

    public function handle()
    {
        $now = Carbon::now();

        // Get consultations that are scheduled within the next hour
        // $consultations = Consultation::where('status', 'scheduled')
        //     ->where('scheduled_at', '>', $now)
        //     ->where('scheduled_at', '<=', $now->copy()->addHour())
        //     ->with(['user', 'doctor'])
        //     ->get();

        $consultations = Consultation::where('status', 'scheduled')
            ->whereRaw('DATE_SUB(scheduled_at, INTERVAL reminder_before_minutes MINUTE) <= ?', [$now])
            ->where('scheduled_at', '>', $now)
            ->with(['user', 'doctor.user'])
            ->get();

        foreach ($consultations as $consultation) {
            $reminderTime = Carbon::parse($consultation->scheduled_at)
                ->subMinutes($consultation->reminder_before_minutes);

        
            if ($now->greaterThanOrEqualTo($reminderTime)) {
                // Send notification to user
                $consultation->user->notify(new \App\Notifications\ConsultationReminder($consultation));

                // Send notification to doctor
                $consultation->doctor->user->notify(new \App\Notifications\ConsultationReminder($consultation));

                $this->info("Reminder sent for consultation #{$consultation->id}");
            }
        }
    }
}
