<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersToSeed = [
            [
                'name' => 'Test User',
                'username' => 'test',
                'email' => 'test@example.com',
                'password' => '123456',
                'role' => 'User',
            ],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => 'admin123',
                'role' => 'Admin',
            ],
            [
                'name' => 'مدير النظام',
                'username' => 'admin', // يتم تحديث هذا المستخدم إذا كان موجوداً
                'email' => 'admin@naseeha.com',
                'password' => 'admin123',
                'role' => 'Admin',
            ],
            [
                'name' => 'مستخدم تجريبي',
                'username' => 'testuser',
                'email' => 'user@naseeha.com',
                'password' => '123456',
                'role' => 'User',
            ],
            [
                'name' => 'مدير النظام الرئيسي',
                'username' => 'SysAdmin',
                'email' => 'admin@naseeha.com',
                'password' => 'Admin@123',
                'role' => 'SysAdmin',
            ],
            [
                'name' => 'محمد أحمد',
                'username' => 'mohammed',
                'email' => 'mohammed@test.com',
                'password' => '123456',
                'role' => 'User',
            ],
        ];

        foreach ($usersToSeed as $user) {
            User::updateOrCreate(
                // الشرط للبحث عن المستخدم
                ['username' => $user['username']],
                // البيانات التي سيتم تحديثها أو إضافتها
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                ]
            );
        }

        echo "تم دمج وإنشاء المستخدمين بنجاح!\n";
    }
}