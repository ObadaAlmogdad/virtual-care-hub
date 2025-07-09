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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('doctor_id')->nullable()->constrained();
            $table->foreignId('medical_tag_id')->constrained();
            $table->boolean('isSpecial')->default(false);
            $table->string('problem');
            $table->string('media')->nullable()->default("");
            $table->boolean('isAnonymous')->default(false);
            $table->double('fee');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'scheduled', 'completed'])->default('pending');
            $table->dateTime('scheduled_at')->nullable();
            $table->integer('reminder_before_minutes')->default(30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
