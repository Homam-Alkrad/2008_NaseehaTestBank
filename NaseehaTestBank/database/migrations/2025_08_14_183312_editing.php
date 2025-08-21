<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Add comment column if it doesn't exist
            if (!Schema::hasColumn('questions', 'comment')) {
                $table->text('comment')->nullable()->after('creator_comment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'comment')) {
                $table->dropColumn('comment');
            }
        });
    }
};