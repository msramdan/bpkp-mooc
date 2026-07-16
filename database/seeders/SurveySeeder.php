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
            'question_text' => 'Sebutkan apa yang paling berkesan bagi Anda dari materi ini:',
            'is_required' => true,
            'urutan' => 3,
        ]);

        // Pertanyaan 4: Radio
        $q4 = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'radio',
            'question_text' => 'Bagaimana Anda menilai penguasaan materi dari pengajar?',
            'is_required' => true,
            'urutan' => 4,
        ]);
        SurveyOption::create(['survey_question_id' => $q4->id, 'option_text' => 'Sangat Menguasai', 'urutan' => 1]);
        SurveyOption::create(['survey_question_id' => $q4->id, 'option_text' => 'Cukup Menguasai', 'urutan' => 2]);
        SurveyOption::create(['survey_question_id' => $q4->id, 'option_text' => 'Kurang Menguasai', 'urutan' => 3]);
        
        // Pertanyaan 5: Checkbox (Multi-select)
        $q5 = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'checkbox',
            'question_text' => 'Fasilitas apa saja yang menurut Anda sudah memadai selama pelatihan? (Boleh pilih lebih dari satu)',
            'is_required' => true,
            'urutan' => 5,
        ]);
        SurveyOption::create(['survey_question_id' => $q5->id, 'option_text' => 'Materi / Modul Pembelajaran', 'urutan' => 1]);
        SurveyOption::create(['survey_question_id' => $q5->id, 'option_text' => 'Platform LMS', 'urutan' => 2]);
        SurveyOption::create(['survey_question_id' => $q5->id, 'option_text' => 'Bantuan / Dukungan Admin', 'urutan' => 3]);
        SurveyOption::create(['survey_question_id' => $q5->id, 'option_text' => 'Studi Kasus Pembelajaran', 'urutan' => 4]);

        // Pertanyaan 6: Rating (Bintang)
        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'rating',
            'question_text' => 'Seberapa relevan materi yang diajarkan dengan tugas Anda di instansi? (1-5)',
            'is_required' => true,
            'urutan' => 6,
        ]);

        // Pertanyaan 7: Radio
        $q7 = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'radio',
            'question_text' => 'Apakah alokasi waktu kursus ini dirasa cukup?',
            'is_required' => true,
            'urutan' => 7,
        ]);
        SurveyOption::create(['survey_question_id' => $q7->id, 'option_text' => 'Sangat Cukup', 'urutan' => 1]);
        SurveyOption::create(['survey_question_id' => $q7->id, 'option_text' => 'Pas', 'urutan' => 2]);
        SurveyOption::create(['survey_question_id' => $q7->id, 'option_text' => 'Kurang / Perlu ditambah', 'urutan' => 3]);

        // Pertanyaan 8: Radio
        $q8 = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'radio',
            'question_text' => 'Apakah studi kasus yang diberikan membantu dalam memahami teori?',
            'is_required' => true,
            'urutan' => 8,
        ]);
        SurveyOption::create(['survey_question_id' => $q8->id, 'option_text' => 'Sangat Membantu', 'urutan' => 1]);
        SurveyOption::create(['survey_question_id' => $q8->id, 'option_text' => 'Cukup Membantu', 'urutan' => 2]);
        SurveyOption::create(['survey_question_id' => $q8->id, 'option_text' => 'Tidak Membantu sama sekali', 'urutan' => 3]);

        // Pertanyaan 9: Checkbox (Multi-select)
        $q9 = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'checkbox',
            'question_text' => 'Topik pelatihan apa yang Anda harapkan ada di masa depan? (Pilih yang relevan)',
            'is_required' => false,
            'urutan' => 9,
        ]);
        SurveyOption::create(['survey_question_id' => $q9->id, 'option_text' => 'Manajemen Risiko', 'urutan' => 1]);
        SurveyOption::create(['survey_question_id' => $q9->id, 'option_text' => 'Audit Internal', 'urutan' => 2]);
        SurveyOption::create(['survey_question_id' => $q9->id, 'option_text' => 'Forensik Digital', 'urutan' => 3]);
        SurveyOption::create(['survey_question_id' => $q9->id, 'option_text' => 'Pengadaan Barang & Jasa', 'urutan' => 4]);

        // Pertanyaan 10: Text (Isian)
        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'text',
            'question_text' => 'Sebutkan saran atau masukan Anda untuk penyelenggaraan kursus berikutnya secara keseluruhan:',
            'is_required' => false,
            'urutan' => 10,
        ]);
    }
}
