<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserQuestionRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('user_question_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned(); // 1-5
            $table->timestamps();
            
            // منع المستخدم من تقييم نفس السؤال مرتين
            $table->unique(['user_id', 'question_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_question_ratings');
    }
}