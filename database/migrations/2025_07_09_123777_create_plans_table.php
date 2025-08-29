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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->integer('duration'); // عدد الأيام
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->unsignedInteger('expected_wait_minutes')->default(0);
            $table->unsignedInteger('private_consultations_quota')->default(0);
            $table->unsignedInteger('ai_consultations_quota')->default(0);
            $table->unsignedInteger('max_family_members')->default(0);
            $table->unsignedInteger('savings_percent')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
