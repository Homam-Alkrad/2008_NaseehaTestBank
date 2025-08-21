<?php
// database/migrations/xxxx_xx_xx_add_rating_to_questions_table.php

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
        Schema::table('questions', function (Blueprint $table) {
            $table->integer('rating')->default(1)->after('user_id'); // تقييم من 1 إلى 5
            $table->text('creator_comment')->nullable()->after('rating'); // تعليق المنشئ
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['rating', 'creator_comment']);
        });
    }
};