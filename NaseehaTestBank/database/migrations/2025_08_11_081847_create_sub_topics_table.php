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
        Schema::create('sub_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الموضوع الفرعي
            $table->text('description')->nullable(); // وصف الموضوع (اختياري)
            $table->foreignId('main_topic_id')->constrained('main_topics')->onDelete('cascade'); // ينتمي لموضوع رئيسي
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_topics');
    }
};