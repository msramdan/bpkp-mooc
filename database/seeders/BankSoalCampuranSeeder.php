<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyOption;
use Illuminate\Support\Facades\DB;

class BankSoalCampuranSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $title = 'Evaluasi Komprehensif Pelatihan & Penguasaan Kompetensi (35 Soal Campur)';
            
            // Bersihkan data lama jika dilakukan pengujian seeder berulang
            Survey::where('title', $title)->delete();

            // 1. Buat Bank Soal & Kuesioner Komprehensif (35 Soal Campuran)
            $survey = Survey::create([
                'title' => $title,
                'description' => 'Bank soal terintegrasi yang mencakup pengujian materi substansial (Pilihan Ganda & Checkbox Multi-Kunci) serta survei kepuasan pelatihan (Skala Rating & Esai Kualitatif). Kunci jawaban tersembah selalu di posisi teratas (Opsi A untuk Pilihan Ganda, dan Opsi A & B untuk Checkbox) guna efisiensi pengetesan sistem.',
                'is_active' => true,
            ]);

            // 2. Daftar 35 Soal Campuran
            $soalCampur = [
                // ---- Bagian 1: 15 Soal Pilihan Ganda (Radio - Selalu Kunci di Opsi A / Indeks 0) [Nomor 1 - 15] ----
                [
                    'type' => 'radio',
                    'q' => 'Fungsi utama dari aparat pengawasan intern pemerintah (APIP) dalam sistem ketatanegaraan modern diposisikan sebagai...',
                    'opts' => ['Quality Assurance dan Consulting Services', 'Pencari kesalahan administrasi bawahan', 'Pemutus perkara hukum pidana aparat', 'Pengganti tanggung jawab pejabat pembuat komitmen'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Unsur SPIP yang mengatur pembentukan suasana kerja, kepemimpinan kondusif, dan penegakan etika organisasi adalah...',
                    'opts' => ['Lingkungan Pengendalian', 'Penilaian Risiko', 'Kegiatan Pengendalian', 'Pemantauan Berkelanjutan'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Tingkat kematangan (Maturity Level) SPIP yang menunjukkan bahwa prosedur pengendalian telah terdiversifikasi dan diatur dalam pedoman tertulis resmi adalah...',
                    'opts' => ['Level 3 (Defined)', 'Level 1 (Initial)', 'Level 2 (Repeatable)', 'Level 5 (Optimised)'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Proses verifikasi silang (cross-check) bukti transaksi pengeluaran anggaran sebelum dilakukan pencairan disebut sebagai pengendalian...',
                    'opts' => ['Preventif (Pencegahan)', 'Detektif (Pendeteksian)', 'Korektif (Perbaikan)', 'Direktif (Pengarahan)'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Standar yang memuat prinsip, kriteria, dan pedoman kerja mutlak bagi seluruh tim auditor APIP di lingkungan pemerintah RI adalah...',
                    'opts' => ['Standar Audit Intern Pemerintah Indonesia (SAIPI)', 'Standar Profesional Akuntan Publik (SPAP)', 'Standar Akuntansi Keuangan (SAK)', 'Pedoman Umum Ejaan Bahasa Indonesia'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Elemen pada Fraud Triangle (Segitiga Kecurangan) yang paling dapat dikontrol dan ditekankan mitigasinya melalui penguatan SPIP adalah...',
                    'opts' => ['Kesempatan (Opportunity)', 'Tekanan Keuangan Pribadi (Pressure)', 'Pembenaran Pribadi (Rationalization)', 'Sikap Keserakahan (Greed)'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Prinsip kode etik di mana auditor harus menghargai kerahasiaan informasi yang dikelola dalam proses pemeriksaan disebut...',
                    'opts' => ['Kerahasiaan (Confidentiality)', 'Objektivitas (Objectivity)', 'Integritas (Integrity)', 'Kompetensi Profesional'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Lini Kedua (Second Line of Defense) dalam percontohan Tata Kelola Manajemen Risiko dipandu oleh...',
                    'opts' => ['Unit Manajemen Risiko dan Fungsi Kepatuhan / Verifikator', 'Unit Operasional Harian di Kantor', 'Aparat Pengawasan Intern Pemerintah (APIP)', 'Badan Pemeriksa Keuangan (BPK)'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Pada pemeriksaan aspek Efisiensi dalam Audit Kinerja, indikator kinerja yang diamati adalah hubungan antara...',
                    'opts' => ['Input (Masukan) dengan Output (Keluaran)', 'Output dengan Outcome (Dampak)', 'Rencana Anggaran dengan Sisa Saldo Kas', 'Jumlah Pegawai dengan Jumlah PC'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Apa yang dimaksud dengan Risiko Sisa (Residual Risk)?',
                    'opts' => ['Risiko yang tetap bertahan dan melekat setelah perlakuan mitigasi manajemen dijalankan', 'Risiko murni tanpa kontrol apapun', 'Risiko alamiah akibat perubahan iklim dunia', 'Risiko kesalahan tik huruf dalam penyampaian memo internal'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Salah satu wujud nyata dari pelaksanaan pemantauan berkelanjutan (continuous monitoring) oleh pimpinan instansi adalah...',
                    'opts' => ['Rapat reviu mingguan atas perkembangan capaian indikator dan penyerapan anggaran', 'Mengontrak konsultan asing setiap dekade sekali', 'Menghubungi kepolisian saat timbul kasus korupsi', 'Mengunci ruangan arsip sepanjang tahun'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Berdasarkan PP 60 Tahun 2008, penanggung jawab utama atas efektivitas penyelenggaraan SPIP pada sebuah instansi daerah/pusat adalah...',
                    'opts' => ['Menteri / Gubernur / Bupati / Walikota (Pimpinan Instansi)', 'Kepala BPKP Wilayah', 'Kepala Perwakilan BPK', 'Ketua Pengadilan Negeri'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Apa yang menjadi keunggulan utama pelaksanaan Control Self Assessment (CSA)?',
                    'opts' => ['Menghadirkan kesadaran dan tanggung jawab pengendalian dari pemilik proses kerja itu sendiri', 'Membuat auditor bisa mengambil masa purna tugas lebih awal', 'Menurunkan besaran gaji pegawai lapis bawah', 'Menutup kemungkinan masuknya pemeriksaan aparat luar negeri'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Pengalihan beban finansial atas kerusakan sarana prasarana vital pemerintah melalui mekanisme asuransi barang milik negara tergolong perlakuan risiko...',
                    'opts' => ['Risk Transfer / Sharing (Mengalihkan Risiko)', 'Risk Avoidance (Menghindari Risiko)', 'Risk Reduction (Mitigasi Fisik)', 'Risk Acceptance (Menerima Keterpurukan)'],
                    'ans' => [0]
                ],
                [
                    'type' => 'radio',
                    'q' => 'Langkah pengamanan dokumen elektronik LMS BPKP melalui kewantahan kombinasi sandi kuat dan autentikasi multi-faktor (MFA) merupakan pengendalian...',
                    'opts' => ['Pengendalian Umum Sistem Informasi dan Akses (IT General Control)', 'Pengendalian Fisik Ruang Kantin', 'Pengendalian Pengiriman Barang Gudang', 'Pengendalian Kas Obat Klinik Kantor'],
                    'ans' => [0]
                ],

                // ---- Bagian 2: 10 Soal Kotak Centang (Checkbox - Kunci di Opsi Teratas A & B / A, B, & C) [Nomor 16 - 25] ----
                [
                    'type' => 'checkbox',
                    'q' => 'Pilih SEMUA jawaban yang benar: Yang merupakan sub-unsur dari Lingkungan Pengendalian menurut PP Nomor 60 Tahun 2008 adalah... (Pilih 3 Opsi Teratas)',
                    'opts' => [
                        'Penegakan integritas dan nilai etika (Benar - Kunci 1)',
                        'Komitmen terhadap kompetensi (Benar - Kunci 2)',
                        'Struktur organisasi yang bersesuaian dengan kebutuhan (Benar - Kunci 3)',
                        'Pemisahan fungsi pencarian kesalahan eksternal (Salah)'
                    ],
                    'ans' => [0, 1, 2]
                ],
                [
                    'type' => 'checkbox',
                    'q' => 'Manakah dari pernyataan berikut yang TEPAT mengenai peran APIP dalam peningkatan Kapabilitas IA-CM? (Pilih 2 Opsi Teratas A & B)',
                    'opts' => [
                        'Menekankan peran sebagai trusted advisor dan strategic partner bagi instansi (Benar)',
                        'Melakukan penjaminan mutu atas manajemen risiko dan tata kelola (Benar)',
                        'Hanya memprioritaskan tangkapan tangkap tangan terhadap staf auditi (Salah)',
                        'Menolak melakukan koordinasi dengan pemeriksa eksternal BPK (Salah)'
                    ],
                    'ans' => [0, 1]
                ],
                [
                    'type' => 'checkbox',
                    'q' => 'Pilih 3 aspek esensial yang dievaluasi secara simultan dalam pelaksanaan Audit Kinerja 3E: (Pilih 3 Opsi Teratas A, B, C)',
                    'opts' => [
                        'Ekonomi / Hemat biaya masukan (Benar)',
                        'Efisiensi / Rasio output per input berdaya guna (Benar)',
                        'Efektivitas / Ketercapaian dampak dan sasaran (Benar)',
                        'Eskalasi / Peningkatan konflik ketegasan internal (Salah)'
                    ],
                    'ans' => [0, 1, 2]
                ],
                [
                    'type' => 'checkbox',
                    'q' => 'Tindakan pengendalian pencegahan (Preventive Controls) yang wajar diterapkan untuk pengamanan anggaran kas instansi meliputi: (Pilih 2 Opsi Teratas A & B)',
                    'opts' => [
                        'Penerapan kewenangan otorisasi bertingkat pada sistem pembayaran digital (Benar)',
                        'Pemisahan pejabat penandatangan spm dengan penyimpan uang kas (Benar)',
                        'Melaporkan kerugian kepada penegak hukum setelah uang dibawa lari oknum (Salah)',
                        'Pemeriksaan resep obat oleh satpam gedung kantor (Salah)'
                    ],
                    'ans' => [0, 1]
                ],
                [
                    'type' => 'checkbox',
                    'q' => 'Manakah dari faktor berikut yang berpotensi menyuburkan celah Kesempatan (Opportunity) untuk tindak kecurangan / Fraud di tempat kerja? (Pilih 2 Opsi Teratas A & B)',
                    'opts' => [
                        'Ketiadaan rotasi tugas rutin pada jabatan strategis yang sensitif (Benar)',
                        'Lemahnya sanksi hukum dan tidak ada penindakan atas pelanggaran kecil (Benar)',
                        'Prosedur rekonsiliasi yang diawasi ketat dan transparan (Salah)',
                        'Adanya saluran aduan Whistleblowing System terproteksi (Salah)'
                    ],
                    'ans' => [0, 1]
                ],
                [
                    'type' => 'checkbox',
                    'q' => 'Kewajiban utama seorang auditor eksternal maupun internal berdasarkan Kode Etik profesi AAIPI adalah: (Pilih 2 Opsi Teratas A & B)',
                    'opts' => [
                        'Menjaga martabat dan kehormatan korps auditor (Benar)',
                        'Meningkatkan pengetahuan dan keahlian berkelanjutan melalui diklat/MOOC (Benar)',
                        'Menyuarakan temuan sementara yang belum terverifikasi ke media sosial (Salah)',
                        'Menerima pemberian cinderamata yang berpotensi memicu benturan kepentingan (Salah)'
                    ],
                    'ans' => [0, 1]
                ],
                [
                    'type' => 'checkbox',
                    'q' => 'Elemen pokok yang wajib tercantum dalam susunan Laporan Hasil Pemeriksaan (LHP) atau Laporan Hasil Audit (LHA) mencakup: (Pilih 3 Opsi Teratas A, B, C)',
                    'opts' => [
                        'Kondisi / Fakta sebenarnya yang ditemukan di lapangan (Benar)',
                        'Kriteria / Peraturan dan standar ukur yang seharusnya dipatuhi (Benar)',
                        'Rekomendasi tindakan korektif untuk manajemen auditi (Benar)',
                        'Daftar foto kegiatan liburan pribadi tim pengawas (Salah)'
                    ],
                    'ans' => [0, 1, 2]
                ],
                [
                    'type' => 'checkbox',
                    'q' => 'Kriteria yang menunjukkan Sistem Pengendalian Intern Pemerintah (SPIP) berpredikat Level 3 (Terdefinisi / Defined) adalah: (Pilih 2 Opsi Teratas A & B)',
                    'opts' => [
                        'Kebijakan dan standar operasi prosedur (SOP) terpendokumentasi secara sah (Benar)',
                        'Evaluasi dan pemantauan pelaksanaan kontrol dicatat dan dijalankan teratur (Benar)',
                        'Praktik pengendalian intern belum dipahami oleh sebagian besar staf (Salah)',
                        'Instansi dibubarkan oleh keputusan parlemen (Salah)'
                    ],
                    'ans' => [0, 1]
                ],
                [
                    'type' => 'checkbox',
                    'q' => 'Aktivitas pengawasan manakah di bawah ini yang dikelompokkan ke dalam kategori Layanan Konsultasi oleh APIP? (Pilih 2 Opsi Teratas A & B)',
                    'opts' => [
                        'Pendampingan penyusunan laporan keuangan daerah (Benar)',
                        'Fasilitasi penyegaran Bimbingan Teknis Manajemen Risiko dan CSA (Benar)',
                        'Audit Investigatif atas kasus dugaan penyelewengan dana bencana (Salah)',
                        'Pemeriksaan fisik kejutan atas uang persediaan (Salah)'
                    ],
                    'ans' => [0, 1]
                ],
                [
                    'type' => 'checkbox',
                    'q' => 'Ciri utama pengelolaan platform pembelajaran daring (MOOC) BPKP yang akuntabel dan berketahanan tinggi mencakup: (Pilih 3 Opsi Teratas A, B, C)',
                    'opts' => [
                        'Enkripsi lalu lintas data dan pencatatan riwayat peninjauan kursus (Benar)',
                        'Validasi dan asesmen kemantapan materi oleh Widyaiswara berlisensi (Benar)',
                        'Ketersediaan evaluasi umpan balik berkala dari para peserta didik (Benar)',
                        'Pemberian sertifikat otomatis kepada pihak yang tidak pernah login materi (Salah)'
                    ],
                    'ans' => [0, 1, 2]
                ],

                // ---- Bagian 3: 5 Soal Skala Rating (1-5 Poin) [Nomor 26 - 30] ----
                [
                    'type' => 'rating',
                    'q' => 'Berapa penilaian (rating) yang Anda berikan atas kejernihan penyajian materi dan audio/visual pada pelatihan daring MOOC ini? (1 = Sangat Buruk/Terendah, 5 = Sangat Baik/Tertinggi)',
                ],
                [
                    'type' => 'rating',
                    'q' => 'Seberapa besar kemudahan penaklukan navigasi, kelengkapan menu survei, dan kecepatan antarmuka platform LMS BPKP saat ini? (1-5 Poin)',
                ],
                [
                    'type' => 'rating',
                    'q' => 'Seberapa aplikatif dan bermanfaatkah studi kasus audit dan penguatan SPIP yang disampaikan dalam menunjang beban tugas instansi Anda?',
                ],
                [
                    'type' => 'rating',
                    'q' => 'Bagaimana tingkat responsivitas, keramahan, dan ketuntasan bantuan fasilitator/tim dukungan admin jika menghadapi kedaruratan sistem?',
                ],
                [
                    'type' => 'rating',
                    'q' => 'Secara menyeluruh, seberapa besar tingkat kepuasan dan kesediaan Anda merekomendasikan kursus evaluasi pengawasan ini kepada sejawat aparat lainnya?',
                ],

                // ---- Bagian 4: 5 Soal Isian Teks / Esai (Penilaian Manual) [Nomor 31 - 35] ----
                [
                    'type' => 'text',
                    'q' => 'Jelaskan dengan bahasa Anda sendiri, mengapa penerapan pemisahan fungsi (segregation of duties) sangat penting untuk menekan risiko kolusi dan kecurangan finansial di instansi pemerintah!',
                ],
                [
                    'type' => 'text',
                    'q' => 'Sebutkan minimal tiga tantangan atau kendala nyata yang kerap dihadapi instansi pemerintah saat hendak meningkatkan Maturitas SPIP ke Level 3 (Defined) dan bagaimana solusinya menurut Anda!',
                ],
                [
                    'type' => 'text',
                    'q' => 'Bagaimana Anda menempatkan keseimbangan antara peran APIP sebagai "Polisi Pengawas" dengan peran modern sebagai "Mitra Konsultasi (Trusted Advisor)" bagi unit operasional auditi?',
                ],
                [
                    'type' => 'text',
                    'q' => 'Berikan kritik, saran, serta usulan modul topik pembelajaran pengawasan atau keuangan baru yang menurut Anda paling mendesak untuk ditambahkan pada kurikulum MOOC BPKP kedepannya!',
                ],
                [
                    'type' => 'text',
                    'q' => 'Uraikan langkah strategis apa yang akan Anda terapkan sesampainya di instansi Anda guna mewujudkan budaya pelaporan pelanggaran (Whistleblowing) yang terpercaya dan bebas dari kebencian interpersonal!',
                ]
            ];

            // 3. Masukkan ke Database
            foreach ($soalCampur as $idx => $item) {
                $question = SurveyQuestion::create([
                    'survey_id' => $survey->id,
                    'type' => $item['type'],
                    'question_text' => $item['q'],
                    'is_required' => true,
                    'urutan' => $idx + 1,
                ]);

                if (in_array($item['type'], ['radio', 'checkbox']) && isset($item['opts'])) {
                    foreach ($item['opts'] as $optIndex => $optText) {
                        $isCorrect = in_array($optIndex, $item['ans']);
                        SurveyOption::create([
                            'survey_question_id' => $question->id,
                            'option_text' => $optText,
                            'urutan' => $optIndex + 1,
                            'score_value' => $isCorrect ? 100 : 0,
                            'is_correct' => $isCorrect,
                        ]);
                    }
                }
            }
        });
    }
}
