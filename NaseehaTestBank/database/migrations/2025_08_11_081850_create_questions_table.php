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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            
            // محتوى السؤال
            $table->text('text')->nullable(); // نص السؤال (اختياري)
            $table->string('image')->nullable(); // مسار الصورة (اختياري)
            
            // التصنيف الإجباري
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade'); // إجباري
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade'); // إجباري
            
            // التصنيف الاختياري
            $table->foreignId('main_topic_id')->nullable()->constrained('main_topics')->onDelete('set null'); // اختياري
            $table->foreignId('sub_topic_id')->nullable()->constrained('sub_topics')->onDelete('set null'); // اختياري
            
            // معلومات المنشئ
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // المستخدم المنشئ
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};