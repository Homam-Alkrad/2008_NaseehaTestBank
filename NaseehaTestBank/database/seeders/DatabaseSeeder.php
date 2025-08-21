<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\Lesson;
use App\Models\Question;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // إنشاء admin user
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@testbank.com',
            'password' => bcrypt('password'),
            'role' => 'Admin'
        ]);

        // إنشاء regular user
        User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'user@testbank.com',
            'password' => bcrypt('password'),
            'role' => 'User'
        ]);

        // إنشاء subject تجريبي
        $subject = Subject::create([
            'name' => 'Mathematics',
            'description' => 'Mathematical concepts and theories'
        ]);

        // إنشاء unit تجريبي
        $unit = Unit::create([
            'name' => 'Algebra',
            'description' => 'Basic algebraic concepts',
            'subject_id' => $subject->id
        ]);

        // إنشاء lesson تجريبي
        $lesson = Lesson::create([
            'name' => 'Linear Equations',
            'description' => 'Solving linear equations',
            'unit_id' => $unit->id
        ]);

        // إنشاء أسئلة تجريبية
        Question::create([
            'text' => 'What is x in 2x + 3 = 7?',
            'unit_id' => $unit->id,
            'lesson_id' => $lesson->id,
            'user_id' => 2, // Test user
            'rating' => 4,
            'comment' => 'Basic linear equation'
        ]);

        Question::create([
            'text' => 'Solve for y: 3y - 5 = 10',
            'unit_id' => $unit->id,
            'lesson_id' => $lesson->id,
            'user_id' => 2,
            'rating' => 5,
            'comment' => 'Another linear equation'
        ]);

        $this->command->info('Database seeded successfully!');
    }
}