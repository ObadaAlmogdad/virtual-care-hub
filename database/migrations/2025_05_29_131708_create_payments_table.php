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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id'); // المريض
            $table->foreignId('doctor_id');
            $table->foreignId('consultation_id')->nullable();
            $table->string('stripe_payment_intent_id')->unique();
            $table->integer('amount'); // القيمة الكاملة
            $table->integer('fee');    // العمولة
            $table->integer('net_amount'); // المبلغ الصافي للطبيب
            $table->string('status')->default('pending'); // pending, succeeded, failed
            $table->boolean('is_refunded')->default(false);
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
