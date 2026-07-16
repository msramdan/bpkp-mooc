<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyOption;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        $survey = Survey::create([
            'title' => 'Kuesioner Evaluasi Pengajar dan Fasilitas',
            'description' => 'Mohon isi kuesioner ini dengan jujur. Penilaian Anda akan membantu BPKP meningkatkan kualitas pelatihan di masa depan.',
            'is_active' => true,
        ]);

        // Pertanyaan 1: Rating (Bintang)
        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'rating',
            'question_text' => 'Berapa rating keseluruhan yang Anda berikan untuk materi kursus ini? (1-5 Bintang)',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Pertanyaan 2: Radio (Pilihan Ganda 1 Jawaban)
        $q2 = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'radio',
            'question_text' => 'Apakah pemateri menyampaikan materi dengan jelas dan mudah dipahami?',
            'is_required' => true,
            'urutan' => 2,
        ]);
        SurveyOption::create(['survey_question_id' => $q2->id, 'option_text' => 'Sangat Jelas', 'urutan' => 1]);
        SurveyOption::create(['survey_question_id' => $q2->id, 'option_text' => 'Cukup Jelas', 'urutan' => 2]);
        SurveyOption::create(['survey_question_id' => $q2->id, 'option_text' => 'Kurang Jelas', 'urutan' => 3]);
        SurveyOption::create(['survey_question_id' => $q2->id, 'option_text' => 'Tidak Jelas', 'urutan' => 4]);

        // Pertanyaan 3: Text (Isian)
        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'text',
            'question_text' => 'Sebutkan saran atau masukan Anda untuk penyelenggaraan kursus berikutnya:',
            'is_required' => false,
            'urutan' => 3,
        ]);
    }
}
