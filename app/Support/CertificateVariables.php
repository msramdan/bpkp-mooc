<?php

namespace App\Support;

use App\Models\Certificate;

class CertificateVariables
{
    /**
     * Variable definitions consumed by the admin editor palette,
     * the print page resolver, and the controller validator.
     */
    public const DEFINITIONS = [
        'brand_instansi' => [
            'label'  => 'Brand / Instansi',
            'type'   => 'text',
            'sample' => 'PUSDIKLATWAS BPKP',
        ],
        'judul_sertifikat' => [
            'label'  => 'Judul Sertifikat',
            'type'   => 'text',
            'sample' => 'SERTIFIKAT',
        ],
        'nomor_sertifikat' => [
            'label'  => 'Nomor Sertifikat',
            'type'   => 'text',
            'sample' => 'Nomor: SERT-123/MOOC/08/2026',
        ],
        'teks_pengantar' => [
            'label'  => 'Teks Pengantar',
            'type'   => 'text',
            'sample' => 'Diberikan kepada',
        ],
        'nama_peserta' => [
            'label'  => 'Nama Peserta',
            'type'   => 'text',
            'sample' => 'Agus Reza Pahlevi',
        ],
        'nip_peserta' => [
            'label'  => 'NIP Peserta',
            'type'   => 'text',
            'sample' => "NIP                        : 199004212012101001",
        ],
        'pangkat_golongan' => [
            'label'  => 'Pangkat, Golongan',
            'type'   => 'text',
            'sample' => "Pangkat, Golongan : Penata, III/c",
        ],
        'jabatan_peserta' => [
            'label'  => 'Jabatan Peserta',
            'type'   => 'text',
            'sample' => "Jabatan                 : Auditor Ahli Muda",
        ],
        'unit_organisasi' => [
            'label'  => 'Unit Organisasi',
            'type'   => 'text',
            'sample' => "Unit Organisasi     : Pusat Pendidikan dan Pelatihan Pengawasan",
        ],
        'teks_penutup' => [
            'label'  => 'Teks Penutup',
            'type'   => 'text',
            'sample' => 'Telah Mengikuti Pendidikan dan Pelatihan:',
        ],
        'nama_kursus' => [
            'label'  => 'Nama Kursus',
            'type'   => 'text',
            'sample' => 'Terampil APIP | 20 - 31 Juli 2026 | 0610A',
        ],
        'jam_pelatihan' => [
            'label'  => 'Jam Pelatihan',
            'type'   => 'text',
            'sample' => '15 Jam Pelatihan',
        ],
        'tanggal_terbit' => [
            'label'  => 'Tanggal Terbit',
            'type'   => 'text',
            'sample' => 'Bogor,    20 Juli 2026    s.d.    30 Juli 2026',
        ],
        'pasfoto_peserta' => [
            'label'  => 'Pas Foto Peserta',
            'type'   => 'image',
            'sample' => null, // Needs dummy URL
        ],
        'jabatan_penandatangan' => [
            'label'  => 'Jabatan Penandatangan',
            'type'   => 'text',
            'sample' => 'Kepala Pusat',
        ],
        'nama_penandatangan' => [
            'label'  => 'Nama Penandatangan',
            'type'   => 'text',
            'sample' => 'Raden Murwantara, Ak., M.EcDev., Ph.D, CA, CGCAE, CIAE, CRGP, CGRE, Askom.',
        ],
        'tanda_tangan' => [
            'label'  => 'Gambar Tanda Tangan',
            'type'   => 'image',
            'sample' => null,
        ],
        'nilai_akhir' => [
            'label'  => 'Nilai Akhir',
            'type'   => 'text',
            'sample' => 'Nilai: 88.5 / 100',
        ],
        'verifikasi_digital' => [
            'label'  => 'Verifikasi Digital',
            'type'   => 'text',
            'sample' => 'Verifikasi: MOOC-GIA-a1b2c3d4',
        ],
        'kota_terbit' => [
            'label'  => 'Kota Terbit',
            'type'   => 'text',
            'sample' => 'Jakarta',
        ],
    ];

    /**
     * Return a default layout that reproduces the exact reference layout.
     */
    public static function defaultLayout(): array
    {
        return [
            'judul_sertifikat' => [
                'x' => 50, 'y' => 20, 'fontSize' => 36, 'fontWeight' => '700',
                'fontStyle' => 'normal', 'textAlign' => 'center', 'color' => '#000000',
            ],
            'nomor_sertifikat' => [
                'x' => 50, 'y' => 25, 'fontSize' => 11, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'center', 'color' => '#000000',
            ],
            'teks_pengantar' => [
                'x' => 50, 'y' => 28, 'fontSize' => 11, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'center', 'color' => '#000000',
            ],
            'nama_peserta' => [
                'x' => 50, 'y' => 33, 'fontSize' => 20, 'fontWeight' => '700',
                'fontStyle' => 'normal', 'textAlign' => 'center', 'color' => '#000000',
            ],
            'nip_peserta' => [
                'x' => 34, 'y' => 40, 'fontSize' => 10, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'left', 'color' => '#000000',
            ],
            'pangkat_golongan' => [
                'x' => 34, 'y' => 43, 'fontSize' => 10, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'left', 'color' => '#000000',
            ],
            'jabatan_peserta' => [
                'x' => 34, 'y' => 46, 'fontSize' => 10, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'left', 'color' => '#000000',
            ],
            'unit_organisasi' => [
                'x' => 34, 'y' => 51, 'fontSize' => 10, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'left', 'color' => '#000000',
            ],
            'teks_penutup' => [
                'x' => 50, 'y' => 56, 'fontSize' => 10, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'center', 'color' => '#000000',
            ],
            'nama_kursus' => [
                'x' => 50, 'y' => 59, 'fontSize' => 11, 'fontWeight' => '700',
                'fontStyle' => 'normal', 'textAlign' => 'center', 'color' => '#000000',
            ],
            'jam_pelatihan' => [
                'x' => 50, 'y' => 62, 'fontSize' => 10, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'center', 'color' => '#000000',
            ],
            'tanggal_terbit' => [
                'x' => 50, 'y' => 65, 'fontSize' => 10, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'center', 'color' => '#000000',
            ],
            'pasfoto_peserta' => [
                'x' => 28, 'y' => 74, 'width' => 9,
            ],
            'jabatan_penandatangan' => [
                'x' => 65, 'y' => 69, 'fontSize' => 10, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'center', 'color' => '#000000',
            ],
            'nama_penandatangan' => [
                'x' => 65, 'y' => 84, 'fontSize' => 10, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'center', 'color' => '#000000',
            ],
            'tanda_tangan' => [
                'x' => 65, 'y' => 77, 'width' => 12,
            ],
            'brand_instansi' => [
                'x' => 95, 'y' => 95, 'fontSize' => 0, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'left', 'color' => '#ffffff',
            ],
            'nilai_akhir' => [
                'x' => 95, 'y' => 95, 'fontSize' => 0, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'left', 'color' => '#ffffff',
            ],
            'verifikasi_digital' => [
                'x' => 95, 'y' => 95, 'fontSize' => 0, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'left', 'color' => '#ffffff',
            ],
            'kota_terbit' => [
                'x' => 95, 'y' => 95, 'fontSize' => 0, 'fontWeight' => '400',
                'fontStyle' => 'normal', 'textAlign' => 'left', 'color' => '#ffffff',
            ],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    /**
     * Return sample values keyed by variable name, for the editor canvas.
     */
    public static function samples(): array
    {
        $samples = [];

        foreach (self::DEFINITIONS as $key => $def) {
            $samples[$key] = $def['sample'] ?? $def['label'];
        }
        
        // Add dummy pasfoto to the samples so layout editor can render it as image
        $samples['pasfoto_peserta'] = asset('backend/assets/images/dummy-pasfoto.png');

        return $samples;
    }

    /**
     * Resolve real values for a Certificate instance.
     */
    public static function resolve(Certificate $certificate): array
    {
        $template = $certificate->template;
        $course = $certificate->course;
        $user = $certificate->user;

        return [
            'judul_sertifikat'     => $template?->title ?? 'SERTIFIKAT',
            'nomor_sertifikat'     => 'Nomor: ' . ($certificate->nomor ?? 'SERT-123/MOOC/08/2026'),
            'teks_pengantar'       => 'Diberikan kepada',
            'nama_peserta'         => $user?->name ?? 'Peserta Pelatihan',
            'nip_peserta'          => "NIP                        : " . ($user?->nip ?? '-'),
            'pangkat_golongan'     => "Pangkat, Golongan : " . ($user?->pangkat_golongan ?? '-'),
            'jabatan_peserta'      => "Jabatan                 : " . ($user?->jabatan ?? '-'),
            'unit_organisasi'      => "Unit Organisasi     : " . ($user?->unit_organisasi ?? '-'),
            'teks_penutup'         => 'Telah Mengikuti Pendidikan dan Pelatihan:',
            'nama_kursus'          => ($course->judul ?? 'Kursus') . ' | ' . ($certificate->issued_at?->translatedFormat('d - t M Y') ?? date('d - t M Y')),
            'jam_pelatihan'        => '15 Jam Pelatihan',
            'tanggal_terbit'       => 'Bogor,    ' . ($certificate->issued_at?->translatedFormat('d F Y') ?? date('d F Y')) . '    s.d.    ' . date('d F Y', strtotime('+10 days')),
            'pasfoto_peserta'      => $user?->avatar ? asset('storage/' . $user->avatar) : asset('backend/assets/images/dummy-pasfoto.png'),
            'jabatan_penandatangan' => $template?->signer_title ?? 'Kepala Pusat',
            'nama_penandatangan'   => $template?->signer_name ?? 'Raden Murwantara',
            'tanda_tangan'         => $template?->signatureUrl(),
            
            // Legacy off-canvas variables
            'brand_instansi'       => '',
            'nilai_akhir'          => '',
            'verifikasi_digital'   => '',
            'kota_terbit'          => '',
        ];
    }
}
