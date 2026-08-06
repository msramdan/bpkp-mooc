<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use Illuminate\Database\Seeder;

class CertificateTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CertificateTemplate::firstOrCreate(
            ['title' => 'Template Sertifikat E-Learning BPKP'],
            [
                'background_image_url' => 'backend/assets/images/certificate-default-bg.png',
                'signer_name' => 'Raden Murwantara, Ak., M.EcDev., Ph.D, CA, CGCAE, CIAE, CRGP, CGRE, Askom.',
                'signer_title' => 'Kepala Pusat',
                'is_default' => true,
            ]
        );

        CertificateTemplate::firstOrCreate(
            ['title' => 'Template Sertifikat Audit Spesifik'],
            [
                'background_image_url' => 'backend/assets/images/certificate-default-bg.png',
                'signer_name' => 'Prof. Dr. Ir. Siti Nurhaliza, CA, CPA',
                'signer_title' => 'Deputi Kepala BPKP Bidang Investigasi & Audit',
                'is_default' => false,
            ]
        );
    }
}
