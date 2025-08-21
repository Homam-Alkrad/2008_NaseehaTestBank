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
        Schema::create('main_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الموضوع الرئيسي
            $table->text('description')->nullable(); // وصف الموضوع (اختياري)
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade'); // ينتمي لدرس
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_topics');
    }
};