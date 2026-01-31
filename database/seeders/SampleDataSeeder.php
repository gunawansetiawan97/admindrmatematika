<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionPackage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first();

        // Paket Aktif
        $activePackage = QuestionPackage::create([
            'title' => 'Olimpiade Matematika - Paket A',
            'description' => 'Latihan soal olimpiade matematika tingkat dasar. Cocok untuk persiapan awal kompetisi.',
            'duration_minutes' => 60,
            'start_date' => Carbon::now()->subDays(1),
            'end_date' => Carbon::now()->addDays(30),
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->createMathQuestions($activePackage);

        // Paket Akan Datang
        $upcomingPackage = QuestionPackage::create([
            'title' => 'Olimpiade Sains - Paket B',
            'description' => 'Paket soal sains untuk persiapan olimpiade tingkat menengah.',
            'duration_minutes' => 90,
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(37),
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        // Paket Past (untuk latihan)
        $pastPackage = QuestionPackage::create([
            'title' => 'Try Out Olimpiade 2024',
            'description' => 'Paket soal try out yang sudah berakhir. Tersedia untuk latihan.',
            'duration_minutes' => 120,
            'start_date' => Carbon::now()->subDays(60),
            'end_date' => Carbon::now()->subDays(30),
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->createMathQuestions($pastPackage);
    }

    private function createMathQuestions(QuestionPackage $package): void
    {
        // Soal 1 - Pilihan Ganda
        $q1 = Question::create([
            'package_id' => $package->id,
            'question_text' => 'Berapakah hasil dari 15 x 15?',
            'question_type' => 'multiple_choice',
            'correct_answer' => 'C',
            'points' => 5,
            'order' => 1,
        ]);

        QuestionOption::insert([
            ['question_id' => $q1->id, 'option_label' => 'A', 'option_text' => '125', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q1->id, 'option_label' => 'B', 'option_text' => '200', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q1->id, 'option_label' => 'C', 'option_text' => '225', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q1->id, 'option_label' => 'D', 'option_text' => '250', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q1->id, 'option_label' => 'E', 'option_text' => '275', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Soal 2 - Pilihan Ganda
        $q2 = Question::create([
            'package_id' => $package->id,
            'question_text' => 'Jika x + y = 10 dan x - y = 4, berapakah nilai x?',
            'question_type' => 'multiple_choice',
            'correct_answer' => 'B',
            'points' => 10,
            'order' => 2,
        ]);

        QuestionOption::insert([
            ['question_id' => $q2->id, 'option_label' => 'A', 'option_text' => '6', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q2->id, 'option_label' => 'B', 'option_text' => '7', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q2->id, 'option_label' => 'C', 'option_text' => '8', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q2->id, 'option_label' => 'D', 'option_text' => '9', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q2->id, 'option_label' => 'E', 'option_text' => '10', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Soal 3 - Essay
        Question::create([
            'package_id' => $package->id,
            'question_text' => 'Sebuah persegi panjang memiliki panjang 12 cm dan lebar 8 cm. Hitunglah luas dan keliling persegi panjang tersebut. Jelaskan langkah-langkah penyelesaiannya.',
            'question_type' => 'essay',
            'correct_answer' => null,
            'points' => 15,
            'order' => 3,
        ]);

        // Soal 4 - Pilihan Ganda
        $q4 = Question::create([
            'package_id' => $package->id,
            'question_text' => 'Berapakah nilai dari 2^10?',
            'question_type' => 'multiple_choice',
            'correct_answer' => 'D',
            'points' => 5,
            'order' => 4,
        ]);

        QuestionOption::insert([
            ['question_id' => $q4->id, 'option_label' => 'A', 'option_text' => '512', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q4->id, 'option_label' => 'B', 'option_text' => '256', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q4->id, 'option_label' => 'C', 'option_text' => '128', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q4->id, 'option_label' => 'D', 'option_text' => '1024', 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $q4->id, 'option_label' => 'E', 'option_text' => '2048', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Soal 5 - Essay
        Question::create([
            'package_id' => $package->id,
            'question_text' => 'Buktikan bahwa jumlah sudut-sudut dalam sebuah segitiga adalah 180 derajat.',
            'question_type' => 'essay',
            'correct_answer' => null,
            'points' => 20,
            'order' => 5,
        ]);
    }
}
