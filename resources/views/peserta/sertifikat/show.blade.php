<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Sertifikat') }} — {{ $certificate->nomor }}</title>
    <style>
        body { font-family: Georgia, 'Times New Roman', serif; margin: 0; padding: 2rem; background: #f5f7fb; color: #1a2b4a; }
        .cert { max-width: 900px; margin: 0 auto; background: #fff; border: 8px double #2b478b; padding: 3rem; text-align: center; }
        .cert__brand { font-size: .9rem; letter-spacing: .15em; text-transform: uppercase; color: #2b478b; margin-bottom: 1rem; }
        .cert__title { font-size: 2rem; margin: 0 0 .5rem; }
        .cert__subtitle { color: #555; margin-bottom: 2rem; }
        .cert__name { font-size: 1.75rem; font-weight: bold; margin: 1rem 0; border-bottom: 1px solid #ddd; display: inline-block; min-width: 60%; padding-bottom: .5rem; }
        .cert__course { font-size: 1.25rem; margin: 1.5rem 0; }
        .cert__meta { display: flex; justify-content: center; gap: 3rem; margin-top: 2rem; font-size: .95rem; }
        .cert__meta span { display: block; color: #666; font-size: .8rem; }
        .no-print { text-align: center; margin-top: 1.5rem; }
        @media print { body { background: #fff; padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="cert">
        <p class="cert__brand">{{ config('app.brand_display', 'MOOC BPKP') }}</p>
        <h1 class="cert__title">{{ __('Sertifikat Penyelesaian') }}</h1>
        <p class="cert__subtitle">{{ __('Diberikan kepada') }}</p>
        <p class="cert__name">{{ $certificate->user->name }}</p>
        <p>{{ __('atas penyelesaian kursus') }}</p>
        <p class="cert__course"><strong>{{ $certificate->course->judul }}</strong></p>
        <div class="cert__meta">
            <div>
                <span>{{ __('Nomor sertifikat') }}</span>
                <strong>{{ $certificate->nomor }}</strong>
            </div>
            <div>
                <span>{{ __('Tanggal terbit') }}</span>
                <strong>{{ $certificate->issued_at?->format('d F Y') }}</strong>
            </div>
            <div>
                <span>{{ __('Nilai akhir') }}</span>
                <strong>{{ $certificate->nilai_akhir }}%</strong>
            </div>
        </div>
    </div>
    <div class="no-print">
        <button onclick="window.print()" style="padding:.5rem 1rem;cursor:pointer">{{ __('Cetak') }}</button>
        <a href="{{ route('peserta.sertifikat.index') }}" style="margin-left:1rem">{{ __('Kembali') }}</a>
    </div>
</body>
</html>
