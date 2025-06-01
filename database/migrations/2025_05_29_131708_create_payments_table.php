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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('consultation_id')->nullable()->constrained()->onDelete('set null');
            $table->string('stripe_payment_intent_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'succeeded', 'failed'])->default('pending');
            $table->boolean('is_refunded')->default(false);
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_reason')->nullable();
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
