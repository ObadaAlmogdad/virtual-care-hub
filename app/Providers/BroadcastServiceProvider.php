<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes();

        Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
            return true; // لتبسيط الاختبار، اجعلها true مؤقتًا
        });

        require base_path('routes/channels.php');
    }
}
