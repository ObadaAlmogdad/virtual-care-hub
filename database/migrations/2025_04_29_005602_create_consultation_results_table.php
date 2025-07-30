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
        Schema::create('consultation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained();
            $table->foreignId('user_question_tag_answer_id')->constrained();
            $table->string('replayOfDoctor');
            $table->boolean('accepted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_results');
    }
};
