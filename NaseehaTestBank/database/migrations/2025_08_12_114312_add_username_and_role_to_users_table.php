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
        Schema::table('users', function (Blueprint $table) {
            // إضافة username بعد name
            $table->string('username')->unique()->after('name');
            // إضافة الدور الوظيفي بعد email
            $table->enum('role', ['SysAdmin', 'User'])->default('User')->after('email');
            // حذف email_verified_at لأننا لا نحتاجه
            $table->dropColumn('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // حذف الحقول المضافة
            $table->dropColumn(['username', 'role']);
            // إعادة email_verified_at
            $table->timestamp('email_verified_at')->nullable();
        });
    }
};