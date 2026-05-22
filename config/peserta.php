<?php

return [

    'sidebars' => [
        [
            'header' => 'Portal Peserta',
            'permissions' => [
                'peserta dashboard view',
                'peserta kursus view',
                'peserta katalog view',
                'peserta tugas view',
                'peserta ujian view',
                'peserta progres view',
                'peserta sertifikat view',
                'peserta jadwal view',
            ],
            'menus' => [
                [
                    'title' => 'Beranda',
                    'icon' => '<i class="bi bi-house-door"></i>',
                    'route' => 'peserta.dashboard',
                    'permission' => 'peserta dashboard view',
                    'permissions' => ['peserta dashboard view'],
                    'submenus' => [],
                ],
                [
                    'title' => 'Pembelajaran',
                    'icon' => '<i class="bi bi-mortarboard"></i>',
                    'route' => null,
                    'permission' => null,
                    'permissions' => ['peserta kursus view', 'peserta katalog view'],
                    'submenus' => [
                        [
                            'title' => 'Kursus Saya',
                            'route' => 'peserta.kursus.index',
                            'permission' => 'peserta kursus view',
                        ],
                        [
                            'title' => 'Katalog Kursus',
                            'route' => 'peserta.katalog.index',
                            'permission' => 'peserta katalog view',
                        ],
                    ],
                ],
                [
                    'title' => 'Penilaian',
                    'icon' => '<i class="bi bi-clipboard-check"></i>',
                    'route' => null,
                    'permission' => null,
                    'permissions' => ['peserta tugas view', 'peserta ujian view', 'peserta progres view'],
                    'submenus' => [
                        [
                            'title' => 'Tugas',
                            'route' => 'peserta.tugas.index',
                            'permission' => 'peserta tugas view',
                        ],
                        [
                            'title' => 'Ujian & Kuis',
                            'route' => 'peserta.ujian.index',
                            'permission' => 'peserta ujian view',
                        ],
                        [
                            'title' => 'Nilai & Progres',
                            'route' => 'peserta.progres.index',
                            'permission' => 'peserta progres view',
                        ],
                    ],
                ],
                [
                    'title' => 'Sertifikat',
                    'icon' => '<i class="bi bi-award"></i>',
                    'route' => 'peserta.sertifikat.index',
                    'permission' => 'peserta sertifikat view',
                    'permissions' => ['peserta sertifikat view'],
                    'submenus' => [],
                ],
                [
                    'title' => 'Jadwal Belajar',
                    'icon' => '<i class="bi bi-calendar-week"></i>',
                    'route' => 'peserta.jadwal.index',
                    'permission' => 'peserta jadwal view',
                    'permissions' => ['peserta jadwal view'],
                    'submenus' => [],
                ],
            ],
        ],
    ],

    'dummy' => [
        'tugas' => [
            [
                'kursus' => 'Audit Internal Berbasis Risiko',
                'judul' => 'Studi Kasus: Identifikasi Risiko Proses Pengadaan',
                'tipe' => 'Essay',
                'deadline' => '2026-05-28',
                'status' => 'Belum dikumpulkan',
                'bobot' => '15%',
            ],
            [
                'kursus' => 'Manajemen Risiko Instansi Pemerintah',
                'judul' => 'Upload Matriks Risiko Unit Kerja',
                'tipe' => 'Upload dokumen',
                'deadline' => '2026-05-25',
                'status' => 'Sudah dikumpulkan',
                'bobot' => '20%',
            ],
            [
                'kursus' => 'Akuntansi Sektor Publik Dasar',
                'judul' => 'Latihan Jurnal Akuntansi APBD',
                'tipe' => 'Kuis singkat',
                'deadline' => '2026-04-20',
                'status' => 'Dinilai — 88',
                'bobot' => '10%',
            ],
        ],
        'ujian' => [
            [
                'kursus' => 'Audit Internal Berbasis Risiko',
                'judul' => 'Ujian Tengah: Standar Audit SPIP',
                'durasi' => '90 menit',
                'jumlah_soal' => 40,
                'jadwal' => '2026-06-05 09:00',
                'status' => 'Terjadwal',
            ],
            [
                'kursus' => 'Manajemen Risiko Instansi Pemerintah',
                'judul' => 'Kuis Modul 4: Profil Risiko',
                'durasi' => '30 menit',
                'jumlah_soal' => 15,
                'jadwal' => 'Tersedia sekarang',
                'status' => 'Bisa dikerjakan',
            ],
            [
                'kursus' => 'Akuntansi Sektor Publik Dasar',
                'judul' => 'Ujian Akhir',
                'durasi' => '120 menit',
                'jumlah_soal' => 50,
                'jadwal' => '2026-04-15',
                'status' => 'Lulus — 82',
            ],
        ],
        'sertifikat' => [
            [
                'kursus' => 'Akuntansi Sektor Publik Dasar',
                'nomor' => 'BPKP-MOOC-2026-004521',
                'tanggal' => '2026-05-01',
                'nilai' => '88',
                'status' => 'Terbit',
            ],
            [
                'kursus' => 'Audit Internal Berbasis Risiko',
                'nomor' => '—',
                'tanggal' => '—',
                'nilai' => '—',
                'status' => 'Menunggu kelulusan',
            ],
        ],
        'jadwal' => [
            ['tanggal' => '2026-05-22', 'waktu' => '09:00', 'kegiatan' => 'Live Session: Review Risiko Pengadaan', 'kursus' => 'Audit Internal Berbasis Risiko'],
            ['tanggal' => '2026-05-25', 'waktu' => '23:59', 'kegiatan' => 'Batas pengumpulan tugas', 'kursus' => 'Manajemen Risiko Instansi Pemerintah'],
            ['tanggal' => '2026-05-28', 'waktu' => '23:59', 'kegiatan' => 'Batas pengumpulan tugas', 'kursus' => 'Audit Internal Berbasis Risiko'],
            ['tanggal' => '2026-06-05', 'waktu' => '09:00', 'kegiatan' => 'Ujian Tengah', 'kursus' => 'Audit Internal Berbasis Risiko'],
        ],
    ],
];
