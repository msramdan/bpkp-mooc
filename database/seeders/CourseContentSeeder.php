<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use Illuminate\Database\Seeder;

class CourseContentSeeder extends Seeder
{
    /**
     * @var array<string, array<int, array{judul: string, deskripsi: string, lessons: array<int, array{judul: string, tipe: string, durasi_menit: int}>}>>
     */
    private array $templates = [
        'AUD-101' => [
            ['judul' => 'Pengenalan Audit SPIP', 'deskripsi' => 'Landasan regulasi dan peran audit internal.', 'lessons' => [
                ['judul' => 'Video: Konsep SPIP di Instansi Pemerintah', 'tipe' => 'video', 'durasi_menit' => 18],
                ['judul' => 'Bacaan: Peraturan terkait audit internal', 'tipe' => 'reading', 'durasi_menit' => 25],
                ['judul' => 'Kuis singkat modul 1', 'tipe' => 'kuis', 'durasi_menit' => 10],
            ]],
            ['judul' => 'Identifikasi & Penilaian Risiko', 'deskripsi' => 'Teknik memetakan risiko proses bisnis.', 'lessons' => [
                ['judul' => 'Video: Workshop risk mapping', 'tipe' => 'video', 'durasi_menit' => 32],
                ['judul' => 'Dokumen: Template matriks risiko', 'tipe' => 'dokumen', 'durasi_menit' => 15],
                ['judul' => 'Live session: Tanya jawab fasilitator', 'tipe' => 'live', 'durasi_menit' => 60],
            ]],
            ['judul' => 'Perencanaan Audit Berbasis Risiko', 'deskripsi' => 'Menyusun rencana audit tahunan.', 'lessons' => [
                ['judul' => 'Video: Menyusun RAT', 'tipe' => 'video', 'durasi_menit' => 28],
                ['judul' => 'Studi kasus: Rencana audit pengadaan', 'tipe' => 'reading', 'durasi_menit' => 40],
            ]],
        ],
        'MR-205' => [
            ['judul' => 'Kerangka Manajemen Risiko', 'deskripsi' => 'Pedoman MR instansi pemerintah.', 'lessons' => [
                ['judul' => 'Video: Siklus manajemen risiko', 'tipe' => 'video', 'durasi_menit' => 22],
                ['judul' => 'Dokumen: Panduan profil risiko', 'tipe' => 'dokumen', 'durasi_menit' => 20],
            ]],
            ['judul' => 'Profil Risiko Unit Kerja', 'deskripsi' => 'Praktik penyusunan profil risiko.', 'lessons' => [
                ['judul' => 'Video: Identifikasi risiko operasional', 'tipe' => 'video', 'durasi_menit' => 35],
                ['judul' => 'Kuis: Pemetaan risiko', 'tipe' => 'kuis', 'durasi_menit' => 15],
                ['judul' => 'Upload latihan matriks risiko', 'tipe' => 'dokumen', 'durasi_menit' => 30],
            ]],
            ['judul' => 'Pemantauan & Pelaporan Risiko', 'deskripsi' => 'Monitoring dan laporan berkala.', 'lessons' => [
                ['judul' => 'Video: Dashboard risiko', 'tipe' => 'video', 'durasi_menit' => 24],
                ['judul' => 'Live session: Review profil risiko', 'tipe' => 'live', 'durasi_menit' => 90],
            ]],
        ],
        'AK-088' => [
            ['judul' => 'Dasar Akuntansi Sektor Publik', 'deskripsi' => 'Konsep dan standar akuntansi APBN/APBD.', 'lessons' => [
                ['judul' => 'Video: Pengantar akuntansi pemerintah', 'tipe' => 'video', 'durasi_menit' => 20],
                ['judul' => 'Bacaan: SAK pemerintah', 'tipe' => 'reading', 'durasi_menit' => 30],
            ]],
            ['judul' => 'Jurnal & Pembukuan', 'deskripsi' => 'Pencatatan transaksi keuangan.', 'lessons' => [
                ['judul' => 'Video: Teknik jurnal umum', 'tipe' => 'video', 'durasi_menit' => 26],
                ['judul' => 'Latihan jurnal APBD', 'tipe' => 'kuis', 'durasi_menit' => 20],
                ['judul' => 'Dokumen: Contoh jurnal lengkap', 'tipe' => 'dokumen', 'durasi_menit' => 15],
            ]],
            ['judul' => 'Pelaporan Keuangan', 'deskripsi' => 'Menyusun laporan keuangan instansi.', 'lessons' => [
                ['judul' => 'Video: Laporan realisasi anggaran', 'tipe' => 'video', 'durasi_menit' => 28],
                ['judul' => 'Kuis akhir modul pelaporan', 'tipe' => 'kuis', 'durasi_menit' => 25],
            ]],
        ],
        'GOV-301' => [
            ['judul' => 'Prinsip Tata Kelola', 'deskripsi' => 'Good governance di sektor publik.', 'lessons' => [
                ['judul' => 'Video: Prinsip transparansi & akuntabilitas', 'tipe' => 'video', 'durasi_menit' => 24],
                ['judul' => 'Bacaan: Framework tata kelola', 'tipe' => 'reading', 'durasi_menit' => 35],
            ]],
            ['judul' => 'Implementasi di Instansi', 'deskripsi' => 'Praktik tata kelola organisasi.', 'lessons' => [
                ['judul' => 'Studi kasus tata kelola daerah', 'tipe' => 'reading', 'durasi_menit' => 45],
                ['judul' => 'Live session: Panel praktisi', 'tipe' => 'live', 'durasi_menit' => 75],
            ]],
        ],
        'AUD-102' => [
            ['judul' => 'Persiapan Wawancara Audit', 'deskripsi' => 'Perencanaan dan teknik pembukaan.', 'lessons' => [
                ['judul' => 'Video: Teknik wawancara efektif', 'tipe' => 'video', 'durasi_menit' => 18],
                ['judul' => 'Dokumen: Daftar pertanyaan wawancara', 'tipe' => 'dokumen', 'durasi_menit' => 10],
            ]],
            ['judul' => 'Pelaksanaan & Dokumentasi', 'deskripsi' => 'Mendokumentasikan hasil wawancara.', 'lessons' => [
                ['judul' => 'Video: Role-play wawancara', 'tipe' => 'video', 'durasi_menit' => 30],
                ['judul' => 'Kuis: Analisis transkrip wawancara', 'tipe' => 'kuis', 'durasi_menit' => 15],
            ]],
        ],
        'IT-040' => [
            ['judul' => 'Keamanan Informasi Dasar', 'deskripsi' => 'Ancaman dan kebijakan keamanan TI.', 'lessons' => [
                ['judul' => 'Video: Keamanan data ASN', 'tipe' => 'video', 'durasi_menit' => 16],
                ['judul' => 'Bacaan: Kebijakan TI pemerintah', 'tipe' => 'reading', 'durasi_menit' => 20],
            ]],
            ['judul' => 'Praktik Keamanan Sehari-hari', 'deskripsi' => 'Phishing, password, dan akses data.', 'lessons' => [
                ['judul' => 'Video: Simulasi serangan phishing', 'tipe' => 'video', 'durasi_menit' => 14],
                ['judul' => 'Kuis keamanan informasi', 'tipe' => 'kuis', 'durasi_menit' => 12],
                ['judul' => 'Checklist kepatuhan TI', 'tipe' => 'dokumen', 'durasi_menit' => 10],
            ]],
        ],
    ];

    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            CourseModule::query()->where('course_id', $course->id)->delete();

            $modules = $this->resolveModules($course);

            foreach ($modules as $index => $moduleData) {
                $module = CourseModule::create([
                    'course_id' => $course->id,
                    'urutan' => $index + 1,
                    'judul' => $moduleData['judul'],
                    'deskripsi' => $moduleData['deskripsi'] ?? null,
                    'durasi_menit' => collect($moduleData['lessons'])->sum('durasi_menit'),
                ]);

                foreach ($moduleData['lessons'] as $lessonIndex => $lesson) {
                    CourseLesson::create([
                        'course_module_id' => $module->id,
                        'urutan' => $lessonIndex + 1,
                        'judul' => $lesson['judul'],
                        'tipe' => $lesson['tipe'],
                        'durasi_menit' => $lesson['durasi_menit'],
                        'video_url' => $lesson['tipe'] === 'video' ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
                        'is_preview' => $lessonIndex === 0 && $index === 0,
                        'is_required' => true,
                    ]);
                }
            }
        }
    }

    /**
     * @return array<int, array{judul: string, deskripsi: string, lessons: array<int, array{judul: string, tipe: string, durasi_menit: int}>}>
     */
    private function resolveModules(Course $course): array
    {
        $target = max(1, min((int) $course->modul_total, 30));
        $base = $this->templates[$course->kode] ?? [];
        $modules = array_values($base);

        while (count($modules) < $target) {
            $i = count($modules) + 1;
            $modules[] = $this->genericModule($i);
        }

        return array_slice($modules, 0, $target);
    }

    /**
     * @return array{judul: string, deskripsi: string, lessons: array<int, array{judul: string, tipe: string, durasi_menit: int}>}
     */
    private function genericModule(int $number): array
    {
        $types = ['video', 'reading', 'dokumen', 'kuis', 'live'];

        return [
            'judul' => 'Modul '.$number,
            'deskripsi' => 'Materi pembelajaran modul '.$number.'.',
            'lessons' => [
                ['judul' => 'Video: Materi inti modul '.$number, 'tipe' => 'video', 'durasi_menit' => 18 + ($number % 8)],
                ['judul' => 'Bacaan pendukung modul '.$number, 'tipe' => 'reading', 'durasi_menit' => 12],
                ['judul' => 'Latihan / kuis modul '.$number, 'tipe' => $types[$number % count($types)], 'durasi_menit' => 10],
            ],
        ];
    }
}
