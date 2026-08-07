@php
    use App\Support\CertificateVariables;
    $template = $certificate->template;
    $layout = $template ? $template->layout() : CertificateVariables::defaultLayout();
    $values = CertificateVariables::resolve($certificate);
    $definitions = CertificateVariables::DEFINITIONS;
    $bgUrl = $template ? $template->backgroundUrl() : asset('backend/assets/images/certificate-default-bg.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Sertifikat Kelulusan') }} — {{ $certificate->nomor }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            margin: 0;
            padding: 0;
            background: #e9eef6;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            justify-content: center;
        }

        .toolbar {
            background: #ffffff;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin: 20px 0;
            display: flex;
            gap: 16px;
            align-items: center;
            z-index: 100;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0f2a4a;
            color: white;
            border: none;
        }

        .btn-primary:hover { background: #1a3c66; }

        .btn-light {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .btn-light:hover { background: #e2e8f0; }

        .certificate-sheet {
            width: 297mm;
            height: 210mm;
            position: relative;
            background-color: #ffffff;
            background-image: url('{{ $bgUrl }}');
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .cert-element {
            position: absolute;
            line-height: 1.25;
            white-space: nowrap;
        }

        /* Specialized font mapping based on legacy visual design */
        .var-judul_sertifikat { font-family: 'Cinzel', serif; letter-spacing: 2px; text-transform: uppercase; }
        .var-nama_peserta { font-family: 'Playfair Display', serif; }
        .var-nama_kursus { font-family: 'Playfair Display', serif; }
        .var-brand_instansi { letter-spacing: 3px; text-transform: uppercase; }
        .var-nama_penandatangan { text-decoration: underline; }

        @media print {
            body {
                background: transparent;
                padding: 0;
                display: block;
            }
            .toolbar {
                display: none !important;
            }
            .certificate-sheet {
                box-shadow: none;
                margin: 0;
                width: 297mm;
                height: 210mm;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            {{ __('Cetak / Simpan PDF (A4 Landscape)') }}
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-light">{{ __('Kembali') }}</a>
    </div>

    <div class="certificate-sheet">
        @foreach($layout as $key => $props)
            @php
                $val = $values[$key] ?? null;
                $def = $definitions[$key] ?? [];
                $isImg = ($def['type'] ?? '') === 'image';
                
                // If it's the score and it's null in DB, render dash
                if ($key === 'nilai_akhir' && $certificate->nilai_akhir === null) {
                    $val = '&mdash;';
                }
            @endphp

            @php
                $anchorX = '-50%';
                if (! $isImg) {
                    $align = $props['textAlign'] ?? 'center';
                    if ($align === 'left') $anchorX = '0%';
                    elseif ($align === 'right') $anchorX = '-100%';
                }
            @endphp
            @if($val || $isImg)
                <div class="cert-element var-{{ $key }}" style="
                    left: {{ $props['x'] ?? 50 }}%;
                    top: {{ $props['y'] ?? 50 }}%;
                    transform: translate({{ $anchorX }}, -50%);
                    font-size: {{ $props['fontSize'] ?? 14 }}pt;
                    font-weight: {{ $props['fontWeight'] ?? '400' }};
                    font-style: {{ $props['fontStyle'] ?? 'normal' }};
                    text-align: {{ $props['textAlign'] ?? 'center' }};
                    color: {{ $props['color'] ?? '#000000' }};
                    @if(isset($props['width']) && $isImg) width: {{ $props['width'] }}%; @endif
                ">
                    @if($isImg && $val)
                        <img src="{{ $val }}" alt="Tanda Tangan" style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
                    @elseif(! $isImg)
                        {!! $val !!}
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</body>
</html>
