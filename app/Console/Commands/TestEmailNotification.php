<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\AccountVerificationNotification;

class TestEmailNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-notification {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending account verification email notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error('User not found!');
            return 1;
        }
        
        try {
            $user->notify(new AccountVerificationNotification($user));
            $this->info('Email notification sent successfully to: ' . $user->email);
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 