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
            $table->foreignId('user_id')->constrained();
            $table->foreignId('doctor_id')->constrained();
            $table->foreignId('medical_tag_id')->constrained();
            $table->boolean('isSpecial');
            $table->string('problem');
            $table->string('media');
            $table->boolean('isAnonymous');
            $table->string('replayOfDoctor');
            $table->double('fee');
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
