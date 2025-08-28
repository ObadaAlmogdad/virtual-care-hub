<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('video_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('callee_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel_name')->unique();
            $table->unsignedBigInteger('agora_uid_caller')->nullable();
            $table->unsignedBigInteger('agora_uid_callee')->nullable();
            $table->enum('status', [
                'initiated',   // أنشئت من المتصل
                'ringing',     // تنبيه الطرف الآخر
                'accepted',    // قُبلت من الطرف الآخر
                'declined',    // رُفضت
                'missed',      // لم يُرد
                'ended',       // انتهت بشكل طبيعي
                'failed',      // فشل فني
            ])->default('ringing');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_sec')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['chat_id', 'status']);
            $table->index(['caller_id', 'callee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_calls');
    }
};


