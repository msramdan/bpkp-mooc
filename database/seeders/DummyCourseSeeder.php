<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\LearningCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 8 pelatihan contoh untuk tampilan landing page. Semua berkode DUMMY-xx dan
 * berjudul "DUMMY - ..." supaya mudah dikenali dan dihapus.
 */
class DummyCourseSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $courses = [
        [
            'kode' => 'DUMMY-01',
            'judul' => 'Audit Kinerja Instansi Pemerintah',
            'kategori' => 'Audit Kinerja',
            'thumbnail' => 'audit-internal-berbasis-risiko.jpg',
            'durasi_jam' => 20,
            'modul_total' => 8,
            'level' => 'Menengah',
            'deskripsi' => 'Merencanakan dan melaksanakan audit kinerja atas program dan kegiatan instansi pemerintah.',
            'tags' => ['Audit Kinerja', 'Penilaian Kinerja Organisasi'],
        ],
        [
            'kode' => 'DUMMY-02',
            'judul' => 'Evaluasi Akuntabilitas Kinerja (SAKIP)',
            'kategori' => 'Audit Kinerja',
            'thumbnail' => 'tata-kelola-pemerintahan-yang-baik.jpg',
            'durasi_jam' => 16,
            'modul_total' => 6,
            'level' => 'Pemula',
            'deskripsi' => 'Memahami siklus SAKIP dan teknik evaluasi akuntabilitas kinerja instansi pemerintah.',
            'tags' => ['Manajemen Kinerja Organisasi', 'Perencanaan Kinerja dan Penganggaran'],
        ],
        [
            'kode' => 'DUMMY-03',
            'judul' => 'Penerapan GRC di Sektor Publik',
            'kategori' => 'GRC',
            'thumbnail' => 'pelatihan-foto-bersama.jpg',
            'durasi_jam' => 24,
            'modul_total' => 10,
            'level' => 'Menengah',
            'deskripsi' => 'Mengintegrasikan tata kelola, manajemen risiko, dan kepatuhan dalam organisasi pemerintah.',
            'tags' => ['Tata Kelola, Manajemen Risiko, dan Pengendalian', 'Integritas'],
        ],
        [
            'kode' => 'DUMMY-04',
            'judul' => 'SPIP Terintegrasi untuk Pimpinan Unit',
            'kategori' => 'GRC',
            'thumbnail' => 'manajemen-risiko-instansi-pemerintah.jpg',
            'durasi_jam' => 12,
            'modul_total' => 5,
            'level' => 'Lanjutan',
            'deskripsi' => 'Peran pimpinan unit dalam membangun dan memantau Sistem Pengendalian Intern Pemerintah.',
            'tags' => ['SPIP Terintegrasi', 'Kapabilitas APIP'],
        ],
        [
            'kode' => 'DUMMY-05',
            'judul' => 'Manajemen Risiko Pembangunan Nasional',
            'kategori' => 'Manajemen Risiko',
            'thumbnail' => 'keamanan-informasi-untuk-asn.jpg',
            'durasi_jam' => 24,
            'modul_total' => 9,
            'level' => 'Menengah',
            'deskripsi' => 'Mengidentifikasi, menilai, dan memitigasi risiko pada program prioritas pembangunan nasional.',
            'tags' => ['Tata Kelola, Manajemen Risiko, dan Pengendalian', 'Analisis Isu Strategis'],
        ],
        [
            'kode' => 'DUMMY-06',
            'judul' => 'Fraud Risk Assessment',
            'kategori' => 'Manajemen Risiko',
            'thumbnail' => 'teknik-wawancara-audit.jpg',
            'durasi_jam' => 18,
            'modul_total' => 7,
            'level' => 'Lanjutan',
            'deskripsi' => 'Menilai risiko kecurangan dan merancang pengendalian pencegahan fraud di instansi pemerintah.',
            'tags' => ['Fraud Risk Management', 'Asset Tracing'],
        ],
        [
            'kode' => 'DUMMY-07',
            'judul' => 'Audit Pengadaan Barang/Jasa Pemerintah',
            'kategori' => 'Pengawasan Intern',
            'thumbnail' => 'akuntansi-sektor-publik-dasar.jpg',
            'durasi_jam' => 20,
            'modul_total' => 8,
            'level' => 'Menengah',
            'deskripsi' => 'Teknik audit atas proses perencanaan hingga serah terima pengadaan barang/jasa pemerintah.',
            'tags' => ['Audit Pengadaan Barang dan/atau Jasa', 'Mengelola Pengadaan Barang /Jasa Pemerintah'],
        ],
        [
            'kode' => 'DUMMY-08',
            'judul' => 'Audit Berbasis Data dan Teknologi',
            'kategori' => 'Pengawasan Intern',
            'thumbnail' => 'pelatihan-kelas-komputer.jpg',
            'durasi_jam' => 16,
            'modul_total' => 6,
            'level' => 'Pemula',
            'deskripsi' => 'Memanfaatkan analisis data dan teknologi digital untuk meningkatkan efektivitas pengawasan.',
            'tags' => ['Analisis Data', 'Cybersecurity and Forensic Digital Audit'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->courses as $course) {
            LearningCategory::firstOrCreate(['name' => $course['kategori']]);

            $judul = 'DUMMY - '.$course['judul'];

            $model = Course::updateOrCreate(
                ['kode' => $course['kode']],
                [
                    'judul' => $judul,
                    'slug' => Str::slug($course['kode'].'-'.$course['judul']),
                    'kategori' => $course['kategori'],
                    'instansi' => 'Internal',
                    'instruktur' => 'Tim Pengajar Pusdiklatwas BPKP',
                    'thumbnail' => $this->publishThumbnail($course['thumbnail']),
                    'durasi_jam' => $course['durasi_jam'],
                    'modul_total' => $course['modul_total'],
                    'level' => $course['level'],
                    'rating' => 0,
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
