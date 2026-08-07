<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyOption;
use Illuminate\Support\Facades\DB;

class BankSoalPilihanGandaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $title = 'Ujian Evaluasi Kompetensi Pengawasan dan SPIP (35 Pilihan Ganda)';
            
            // Bersihkan data survei dengan judul sama sebelumnya agar tidak duplikat saat dikanjutkan pengetesan
            Survey::where('title', $title)->delete();

            // 1. Buat Bank Soal Ujian Evaluasi (35 Pilihan Ganda)
            $survey = Survey::create([
                'title' => $title,
                'description' => 'Bank soal evaluasi kompetensi pengawasan keuangan negara dan Sistem Pengendalian Intern Pemerintah (SPIP). Terdiri dari 35 soal Pilihan Ganda dengan penilaian otomatis (100 untuk jawaban benar, 0 untuk jawaban salah). Seluruh kunci jawaban ditempatkan pada opsi pertama (A) untuk kenyamanan uji coba sistem.',
                'is_active' => true,
            ]);

            // 2. Daftar 35 Soal Pilihan Ganda (SEMUA JAWABAN BENAR DITARUH DI OPSI PERTAMA / A / INDEKS 0)
            $soalPilihanGanda = [
                [
                    'q' => 'Landasan hukum utama pelaksanaan Sistem Pengendalian Intern Pemerintah (SPIP) diatur dalam...',
                    'opts' => ['PP Nomor 60 Tahun 2008', 'PP Nomor 71 Tahun 2010', 'UU Nomor 17 Tahun 2003', 'Perpres Nomor 192 Tahun 2014'],
                    'ans' => 0
                ],
                [
                    'q' => 'Unsur pertama dan menjadi fondasi utama dalam kerangka kerja SPIP menurut PP 60/2008 adalah...',
                    'opts' => ['Lingkungan Pengendalian', 'Penilaian Risiko', 'Kegiatan Pengendalian', 'Pemantauan Pengendalian Intern'],
                    'ans' => 0
                ],
                [
                    'q' => 'Tindakan yang diperlukan untuk mengatasi risiko serta menetapkan dan melaksanakan kebijakan serta prosedur merupakan definisi dari unsur SPIP yaitu...',
                    'opts' => ['Kegiatan Pengendalian', 'Lingkungan Pengendalian', 'Informasi dan Komunikasi', 'Penilaian Risiko'],
                    'ans' => 0
                ],
                [
                    'q' => 'Manakah yang BUKAN merupakan sub-unsur dari Lingkungan Pengendalian dalam SPIP?',
                    'opts' => ['Identifikasi dan analisis risiko', 'Penegakan integritas dan nilai etika', 'Komitmen terhadap kompetensi', 'Kepemimpinan yang kondusif'],
                    'ans' => 0
                ],
                [
                    'q' => 'Proses pengidentifikasian dan analisis atas risiko yang relevan terhadap pencapaian tujuan instansi pemerintah disebut...',
                    'opts' => ['Penilaian Risiko', 'Pemantauan Berkelanjutan', 'Reviu Kinerja', 'Audit Investigatif'],
                    'ans' => 0
                ],
                [
                    'q' => 'Pengawasan Intern atas penyelenggaraan tugas dan fungsi instansi pemerintah termasuk akuntabilitas keuangan negara diatur sebagai peran utama dari...',
                    'opts' => ['Badan Pengawasan Keuangan dan Pembangunan (BPKP)', 'Badan Pemeriksa Keuangan (BPK)', 'Kementerian Hukum dan HAM', 'DPR / DPRD'],
                    'ans' => 0
                ],
                [
                    'q' => 'Dalam kerangka Tiga Lini (Three Lines Model), peranan Auditor Intern Pemerintah (APIP) menempati posisi pada...',
                    'opts' => ['Lini Ketiga (Third Line)', 'Lini Pertama (First Line)', 'Lini Kedua (Second Line)', 'Lini Keempat (External Audit)'],
                    'ans' => 0
                ],
                [
                    'q' => 'Lini Pertama dalam manajemen risiko dan pengendalian intern pada struktur organisasi dipraktekkan oleh...',
                    'opts' => ['Manajemen Operasional dan Pelaksana Kegiatan', 'Unit Kepatuhan dan Manajemen Risiko', 'Aparat Pengawasan Intern Pemerintah (APIP)', 'Auditor Eksternal'],
                    'ans' => 0
                ],
                [
                    'q' => 'Standar Audit Intern Pemerintah Indonesia (SAIPI) disusun dan diterbitkan oleh organisasi profesi yaitu...',
                    'opts' => ['AAIPI (Asosiasi Auditor Intern Pemerintah Indonesia)', 'IAI (Ikatan Akuntan Indonesia)', 'IAPI (Institut Akuntan Publik Indonesia)', 'IIA (Institute of Internal Auditors)'],
                    'ans' => 0
                ],
                [
                    'q' => 'Salah satu prinsip kode etik yang mewajibkan auditor intern pemerintah untuk jujur dan bersikap adil serta tidak tercela dalam menjalanan kewajibannya adalah...',
                    'opts' => ['Integritas', 'Objektivitas', 'Kerahasiaan', 'Kompetensi'],
                    'ans' => 0
                ],
                [
                    'q' => 'Prinsip yang menuntut auditor untuk bersikap tidak memihak dan tidak terpangaruh oleh kepentingan tertentu dalam merumuskan pendapat disebut...',
                    'opts' => ['Objektivitas', 'Integritas', 'Keterbukaan', 'Prinsip Kehati-hatian'],
                    'ans' => 0
                ],
                [
                    'q' => 'Apa yang dimakud dengan risiko bawaan (Inherent Risk) dalam proses penilaian risiko?',
                    'opts' => ['Risiko sebelum dilakukannya tindakan pengendalian oleh manajemen', 'Risiko yang tersisa setelah pengendalian diterapkan', 'Risiko karena kesalahan teknik sampling auditor', 'Risiko sistem ketiadaan fasilitas TI'],
                    'ans' => 0
                ],
                [
                    'q' => 'Tingkat risiko yang tersisa setelah manajemen menerapkan perlakuan atau mitigasi risiko disebut...',
                    'opts' => ['Risiko Sisa (Residual Risk)', 'Risiko Bawaan (Inherent Risk)', 'Risiko Pengendalian (Control Risk)', 'Risiko Deteksi (Detection Risk)'],
                    'ans' => 0
                ],
                [
                    'q' => 'Dalam penanganan risiko (Risk Treatment), keputusan untuk tidak melanjutkkan atau menghentikan kegiatan yang berisiko tinggi disebut...',
                    'opts' => ['Menghindari Risiko (Risk Avoidance)', 'Mengurangi Risiko (Risk Reduction)', 'Membagi Risiko (Risk Sharing / Sharing)', 'Menerima Risiko (Risk Acceptance)'],
                    'ans' => 0
                ],
                [
                    'q' => 'Memasang alat pemadam kebakaran dan melakukan pelatihan pemadaman darurat merupakan bentuk tindakan perlakuan risiko jenis...',
                    'opts' => ['Mengurangi Dampak dan Kemungkinan Risiko (Mitigasi)', 'Menghindari Risiko', 'Mengalihkan Risiko (Asuransi)', 'Menerima Risiko Tanpa Syarat'],
                    'ans' => 0
                ],
                [
                    'q' => 'Salah satu teknik pemantauan SPIP yang dilakukan oleh pelaksana kegiatan itu sendiri untuk mengevaluasi efektivitas pengendalian operasionalnya adalah...',
                    'opts' => ['Penilaian Sendiri (Control Self Assessment / CSA)', 'Evaluasi Terpisah (Separate Evaluation)', 'Audit Investigatif Eksternal', 'Pemeriksaan Kasus Korupsi'],
                    'ans' => 0
                ],
                [
                    'q' => 'Level kematangan (Maturity Level) penyelenggaraan SPIP pada instansi pemerintah diukur dalam rentang level...',
                    'opts' => ['Level 0 (Belum Ada) sampai dengan Level 5 (Optimum)', 'Level 1 sampai dengan Level 10', 'Level A sampai dengan Level E', 'Level Dasar dan Level Lanjut'],
                    'ans' => 0
                ],
                [
                    'q' => 'Maturity Level SPIP pada level 3 dalam penamaan standar evaluasi kematangan dinamakan level...',
                    'opts' => ['Terdefinisi (Defined)', 'Rintisan', 'Berkembang', 'Terkelola (Managed)'],
                    'ans' => 0
                ],
                [
                    'q' => 'Upaya untuk mencegah terjadinya perlakuan benturan kepentingan (Conflict of Interest) dalam pelaksanaan kegiatan belanja barang/jasa termasuk ke dalam unsur SPIP...',
                    'opts' => ['Lingkungan Pengendalian', 'Penilaian Risiko', 'Kegiatan Pengendalian', 'Pemantauan'],
                    'ans' => 0
                ],
                [
                    'q' => 'Pemisahan fungsi antara yang menguasai anggaran, pelaksana verifikasi, dan pihak penyimpan uang/barang merupakan instrumen dari...',
                    'opts' => ['Kegiatan Pengendalian', 'Penilaian Risiko', 'Informasi dan Komunikasi', 'Audit Kinerja'],
                    'ans' => 0
                ],
                [
                    'q' => 'Rekonsiliasi berkala antara catatan pembukuan akuntansi dengan rekening koran bank termasuk kegiatan pengendalian tipe...',
                    'opts' => ['Pengendalian dan Verifikasi Catatan Akuntansi', 'Pengendalian Fisik Atas Aset', 'Pengendalian Terhadap Sistem Informasi', 'Otorisasi atas Transaksi'],
                    'ans' => 0
                ],
                [
                    'q' => 'Dokumentasi yang menggambarkan bagan alir (flowchart) serta prosedur operasional standar (SOP) dari sebuah proses kerja bertujuan utama untuk...',
                    'opts' => ['Memastikan kepatuhan dan standarisasi pelaksanaan pengendalian', 'Menghabiskan anggaran cetak dokumen', 'Mempersulit koordinasi antar pegawai', 'Menggantikan kewenangan pimpinan instansi'],
                    'ans' => 0
                ],
                [
                    'q' => 'Apa yang dimaksud dengan Pengawalan dan Pengawasan Berkelanjutan (Continuous Monitoring)?',
                    'opts' => ['Pemantauan rutinitas operasi melekat secara reguler sewaktu kegiatan berlangsung', 'Pemeriksaan rutin mingguan oleh auditor luar', 'Audit sesaat setelah terjadinya tindak kecurangan', 'Penyusunan laporan tahunan akhir'],
                    'ans' => 0
                ],
                [
                    'q' => 'Perbedaan mendasar antara tugas BPK (Badan Pemeriksa Keuangan) dengan BPKP (Badan Pengawasan Keuangan dan Pembangunan) adalah...',
                    'opts' => ['BPK adalah lembaga pemeriksa ekstern (UU), BPKP adalah auditor intern pemerintah (Presiden)', 'BPK adalah pengawas intern, BPKP pengawas ekstern', 'BPK berada di bawah Kementerian Keuangan, BPKP di bawah DPR', 'BPKP bertugas memvonis sanksi pidana hukum'],
                    'ans' => 0
                ],
                [
                    'q' => 'Berdasarkan Peraturan Presiden Nomor 192 Tahun 2014, BPKP berada di bawah dan bertanggung jawab kepada...',
                    'opts' => ['Presiden Republik Indonesia', 'Menteri Keuangan RI', 'Menteri Dalam Negeri RI', 'Ketua Badan Pemeriksa Keuangan'],
                    'ans' => 0
                ],
                [
                    'q' => 'Dalam pelaksanaan Audit Kinerja (Performance Audit), aspek utama yang dinilai secara konseptual adalah unsur 3E, yaitu...',
                    'opts' => ['Ekonomi, Efisiensi, dan Efektivitas', 'Ekspansi, Eksplorasi, dan Estimasi', 'Etika, Estetika, dan Eksakta', 'Ekonomi, Ekosistem, dan Evaluasi'],
                    'ans' => 0
                ],
                [
                    'q' => 'Aspek yang membandingkan antara masukan (input) berupa sumber daya yang digunakan dengan keluaran (output) yang dihasilkan disebut...',
                    'opts' => ['Efisiensi', 'Ekonomi', 'Efektivitas', 'Ekuitas'],
                    'ans' => 0
                ],
                [
                    'q' => 'Aspek yang mengevaluasi apakah keluaran (output) yang dihasilkan dari program telah berhasil mencapai sasaran atau tujuan (outcome) yang diinginkan disebut...',
                    'opts' => ['Efektivitas', 'Ekonomi', 'Efisiensi', 'Ekualitas'],
                    'ans' => 0
                ],
                [
                    'q' => 'Audit yang bertujuan untuk mengidentifikasi dan mengungkap fakta adanya dugaan tindak pidana kecurangan (fraud/korupsi) disebut...',
                    'opts' => ['Audit Investigatif', 'Audit Kinerja', 'Audit Finansial Reguler', 'Audit Kepatuhan Dasar'],
                    'ans' => 0
                ],
                [
                    'q' => 'Manakah yang tergolong ke dalam 3 komponen utama Segitiga Kecurangan (Fraud Triangle) menurut Donald R. Cressey?',
                    'opts' => ['Tekanan (Pressure), Kesempatan (Opportunity), dan Rasionalisasi (Rationalization)', 'Kewenangan, Anggaran, dan Rekam Jejak', 'Integritas, Objektivitas, dan Kompetensi', 'Perencanaan, Pelaksanaan, dan Pelapor'],
                    'ans' => 0
                ],
                [
                    'q' => 'Kelemahan pada sistem pengendalian intern atau rendahnya pemantauan pengawasan di instansi paling mungkin memicu timbulnya komponen Fraud Triangle yaitu...',
                    'opts' => ['Kesempatan (Opportunity)', 'Tekanan (Pressure)', 'Rasionalisasi (Rationalization)', 'Karakter Individu'],
                    'ans' => 0
                ],
                [
                    'q' => 'Sistem yang memungkinkan pelapor (whistleblower) untuk menyampaikan laporan dugaan tindak pidana korupsi atau pelanggaran secara rahasia dan aman disebut...',
                    'opts' => ['Whistleblowing System (WBS)', 'Enterprise Resource Planning (ERP)', 'Sistem Akuntansi Instansi (SAI)', 'Customer Relationship Management (CRM)'],
                    'ans' => 0
                ],
                [
                    'q' => 'Penguatan kapabilitas APIP di Indonesia pada umumnya dinilai dan distandardisasi menggunakan kerangka kerja dunia yaitu...',
                    'opts' => ['IA-CM (Internal Audit Capability Model)', 'ISO 9001 Manajemen Mutu', 'COSO Enterprise Risk Management edisi 1992', 'Sistem Manajemen Keamanan Informasi ISO 27001'],
                    'ans' => 0
                ],
                [
                    'q' => 'Laporan Hasil Audit (LHA) atau Laporan Hasil Pemeriksaan yang diserahkan APIP wajib bersifat...',
                    'opts' => ['Objektif, akurat, jelas, ringkas, dan tepat waktu', 'Subjektif dan memihak keinginan pimpinan auditi', 'Samar-samar agar tidak terjadi kekisruhan', 'Tanpa disertai lampiran bukti dukung yang cukup'],
                    'ans' => 0
                ],
                [
                    'q' => 'Tindak Lanjut Rekomendasi Hasil Pemeriksaan (TLRHP) merupakan tonggak terpenting dalam audit karena...',
                    'opts' => ['Menunjukkan bahwa perbaikan nyata dan penguatan pengendalian telah diemban oleh instansi auditi', 'Hanya sebagai syarat pelengkap administrasi kantor', 'Bertujuan untuk menghitung bonus honor rutin tim auditor', 'Menutup kemungkinan masuknya pemeriksaan instansi penegak hukum eksternal'],
                    'ans' => 0
                ],
            ];

            // 3. Masukkan ke database (Opsi indeks 0 selalu benar / bernilai 100)
            foreach ($soalPilihanGanda as $idx => $item) {
                $question = SurveyQuestion::create([
                    'survey_id' => $survey->id,
                    'type' => 'radio',
                    'question_text' => $item['q'],
                    'is_required' => true,
                    'urutan' => $idx + 1,
                ]);

                foreach ($item['opts'] as $optIndex => $optText) {
                    $isCorrect = ($optIndex === 0);
                    SurveyOption::create([
                        'survey_question_id' => $question->id,
                        'option_text' => $optText,
                        'urutan' => $optIndex + 1,
                        'score_value' => $isCorrect ? 100 : 0,
                        'is_correct' => $isCorrect,
                    ]);
                }
            }
        });
    }
}
