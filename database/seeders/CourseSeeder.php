<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
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
            'thumbnail' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=640&h=360&fit=crop&q=80',
            'durasi_jam' => 24,
            'modul_total' => 12,
            'level' => 'Menengah',
            'rating' => 4.7,
            'deskripsi' => 'Memahami standar audit SPIP dan pendekatan berbasis risiko di instansi pemerintah.',
        ],
        [
            'kode' => 'MR-205',
            'judul' => 'Manajemen Risiko Instansi Pemerintah',
            'kategori' => 'Manajemen Risiko',
            'instruktur' => 'Bambang Setyawan, SE., M.Si.',
            'thumbnail' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=640&h=360&fit=crop&q=80',
            'durasi_jam' => 32,
            'modul_total' => 10,
            'level' => 'Lanjutan',
            'rating' => 4.9,
            'deskripsi' => 'Menyusun profil risiko, kontrol, dan pemantauan risiko organisasi.',
        ],
        [
            'kode' => 'AK-088',
            'judul' => 'Akuntansi Sektor Publik Dasar',
            'kategori' => 'Akuntansi',
            'instruktur' => 'Dewi Kartika, S.E., Ak., CA.',
            'thumbnail' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=640&h=360&fit=crop&q=80',
            'durasi_jam' => 18,
            'modul_total' => 6,
            'level' => 'Pemula',
            'rating' => 4.8,
            'deskripsi' => 'Dasar akuntansi dan pelaporan keuangan sektor publik.',
        ],
        [
            'kode' => 'GOV-301',
            'judul' => 'Tata Kelola Pemerintahan yang Baik',
            'kategori' => 'Tata Kelola',
            'instruktur' => 'Prof. Agus Pramono, MPA.',
            'thumbnail' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=640&h=360&fit=crop&q=80',
            'durasi_jam' => 24,
            'modul_total' => 14,
            'level' => 'Menengah',
            'rating' => 4.8,
            'deskripsi' => 'Prinsip tata kelola, akuntabilitas, dan transparansi pemerintahan.',
        ],
        [
            'kode' => 'AUD-102',
            'judul' => 'Teknik Wawancara Audit',
            'kategori' => 'Audit Internal',
            'instruktur' => 'Maya Sari, CIA.',
            'thumbnail' => 'https://images.unsplash.com/photo-1573164713714-d95e436faf8f?w=640&h=360&fit=crop&q=80',
            'durasi_jam' => 16,
            'modul_total' => 8,
            'level' => 'Pemula',
            'rating' => 4.6,
            'deskripsi' => 'Teknik wawancara efektif dalam proses audit internal.',
        ],
        [
            'kode' => 'IT-040',
            'judul' => 'Keamanan Informasi untuk ASN',
            'kategori' => 'TI & Keamanan',
            'instruktur' => 'Rizky Pratama, CISA.',
            'thumbnail' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=640&h=360&fit=crop&q=80',
            'durasi_jam' => 12,
            'modul_total' => 6,
            'level' => 'Pemula',
            'rating' => 4.7,
            'deskripsi' => 'Keamanan data, phishing, dan kebijakan TI di lingkungan pemerintah.',
        ],
    ];

    public function run(): void
    {
        foreach ($this->courses as $course) {
            Course::updateOrCreate(
                ['kode' => $course['kode']],
                [
                    'judul' => $course['judul'],
                    'slug' => Str::slug($course['kode'].'-'.$course['judul']),
                    'kategori' => $course['kategori'],
                    'instruktur' => $course['instruktur'],
                    'thumbnail' => $course['thumbnail'],
                    'durasi_jam' => $course['durasi_jam'],
                    'modul_total' => $course['modul_total'],
                    'level' => $course['level'],
                    'rating' => $course['rating'],
                    'deskripsi' => $course['deskripsi'],
                    'is_published' => true,
                ]
            );
        }
    }
}
