<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $courses = [
        [
            'kode' => 'AUD-101',
            'judul' => 'Audit Internal Berbasis Risiko',
            'kategori' => 'Audit Internal',
            'instruktur' => 'Dr. Rina Wulandari, M.Ak.',
            'thumbnail' => 'audit-internal-berbasis-risiko.jpg',
            'durasi_jam' => 24,
            'modul_total' => 12,
            'level' => 'Menengah',
            'rating' => 4.7,
            'deskripsi' => 'Memahami standar audit SPIP dan pendekatan berbasis risiko di instansi pemerintah.',
            'tags' => ['Standar Audit', 'Tata Kelola, Manajemen Risiko, dan Pengendalian', 'Pelaksanaan Pengawasan Intern'],
        ],
        [
            'kode' => 'MR-205',
            'judul' => 'Manajemen Risiko Instansi Pemerintah',
            'kategori' => 'Manajemen Risiko',
            'instruktur' => 'Bambang Setyawan, SE., M.Si.',
            'thumbnail' => 'manajemen-risiko-instansi-pemerintah.jpg',
            'durasi_jam' => 32,
            'modul_total' => 10,
            'level' => 'Lanjutan',
            'rating' => 4.9,
            'deskripsi' => 'Menyusun profil risiko, kontrol, dan pemantauan risiko organisasi.',
            'tags' => ['Tata Kelola, Manajemen Risiko, dan Pengendalian', 'SPIP Terintegrasi'],
        ],
        [
            'kode' => 'AK-088',
            'judul' => 'Akuntansi Sektor Publik Dasar',
            'kategori' => 'Akuntansi',
            'instruktur' => 'Dewi Kartika, S.E., Ak., CA.',
            'thumbnail' => 'akuntansi-sektor-publik-dasar.jpg',
            'durasi_jam' => 18,
            'modul_total' => 6,
            'level' => 'Pemula',
            'rating' => 4.8,
            'deskripsi' => 'Dasar akuntansi dan pelaporan keuangan sektor publik.',
            'tags' => ['Manajemen dan Analisis Keuangan', 'Keuangan Negara/Daerah & kekayaan yang dipisahkan'],
        ],
        [
            'kode' => 'GOV-301',
            'judul' => 'Tata Kelola Pemerintahan yang Baik',
            'kategori' => 'Tata Kelola',
            'instruktur' => 'Prof. Agus Pramono, MPA.',
            'thumbnail' => 'tata-kelola-pemerintahan-yang-baik.jpg',
            'durasi_jam' => 24,
            'modul_total' => 14,
            'level' => 'Menengah',
            'rating' => 4.8,
            'deskripsi' => 'Prinsip tata kelola, akuntabilitas, dan transparansi pemerintahan.',
            'tags' => ['Tata Kelola Pemerintahan, Reformasi Birokrasi, dan Transformasi Digital', 'Integritas'],
        ],
        [
            'kode' => 'AUD-102',
            'judul' => 'Teknik Wawancara Audit',
            'kategori' => 'Audit Internal',
            'instruktur' => 'Maya Sari, CIA.',
            'thumbnail' => 'teknik-wawancara-audit.jpg',
            'durasi_jam' => 16,
            'modul_total' => 8,
            'level' => 'Pemula',
            'rating' => 4.6,
            'deskripsi' => 'Teknik wawancara efektif dalam proses audit internal.',
            'tags' => ['Komunikasi', 'Pelaksanaan Pengawasan Intern'],
        ],
        [
            'kode' => 'IT-040',
            'judul' => 'Keamanan Informasi untuk ASN',
            'kategori' => 'TI & Keamanan',
            'instruktur' => 'Rizky Pratama, CISA.',
            'thumbnail' => 'keamanan-informasi-untuk-asn.jpg',
            'durasi_jam' => 12,
            'modul_total' => 6,
            'level' => 'Pemula',
            'rating' => 4.7,
            'deskripsi' => 'Keamanan data, phishing, dan kebijakan TI di lingkungan pemerintah.',
            'tags' => ['Literasi Digital', 'Operasional dan Keamanan Teknologi Informasi'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->courses as $course) {
            $model = Course::updateOrCreate(
                ['kode' => $course['kode']],
                [
                    'judul' => $course['judul'],
                    'slug' => Str::slug($course['kode'].'-'.$course['judul']),
                    'kategori' => $course['kategori'],
                    'instruktur' => $course['instruktur'],
                    'thumbnail' => $this->publishThumbnail($course['thumbnail']),
                    'durasi_jam' => $course['durasi_jam'],
                    'modul_total' => $course['modul_total'],
                    'level' => $course['level'],
                    'rating' => $course['rating'],
                    'deskripsi' => $course['deskripsi'],
                    'is_published' => true,
                ]
            );

            // Tag = nama kompetensi dari Kamus Kompetensi Aplikasi TNA.
            $model->syncTags($course['tags']);
        }
    }

    /**
     * Salin foto pelatihan dari database/seeders/images/courses ke disk public,
     * sama seperti thumbnail hasil upload (courses/thumbnails/...).
     */
    private function publishThumbnail(string $filename): string
    {
        $path = 'courses/thumbnails/'.$filename;

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents(database_path('seeders/images/courses/'.$filename)));
        }

        return $path;
    }
}
