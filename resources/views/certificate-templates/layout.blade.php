@extends('layouts.app')

@section('title', __('Atur Tata Letak Sertifikat — ') . $certificateTemplate->title)

@push('css')
<style>
    /* Ensure no parent overflow breaks canvas dragging */
    .canvas-container-outer {
        overflow: auto;
        max-height: 80vh;
        border-radius: 8px;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
    }
    .palette-item {
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .palette-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .palette-item.active {
        border-color: var(--primary) !important;
        background-color: rgba(13, 110, 253, 0.04);
    }
    .inspector-card {
        border-left: 4px solid var(--primary);
    }
    #canvas-grid-lines {
        pointer-events: none;
    }
    
    @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
    .var-judul_sertifikat { font-family: 'Cinzel', serif !important; letter-spacing: 2px; text-transform: uppercase; }
    .var-nama_peserta { font-family: 'Playfair Display', serif !important; }
    .var-nama_kursus { font-family: 'Playfair Display', serif !important; }
    .var-brand_instansi { letter-spacing: 3px; text-transform: uppercase; }
    .var-nama_penandatangan { text-decoration: underline; }

    @media (max-width: 1400px) {
        .editor-col-canvas {
            order: -1; /* Keep canvas prominent on smaller screens */
        }
    }
</style>
@endpush

@section('content')
<div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Atur Tata Letak Sertifikat') }}</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('certificate-templates.index') }}">{{ __('Template Sertifikat') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $certificateTemplate->title }}</li>
        </ol>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('certificate-templates.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('Kembali ke Daftar') }}
        </a>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-reset-layout"
                title="Kembalikan ke susunan bawaan standar">
            <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('Reset Default') }}
        </button>
    </div>
</div>

<div class="row g-4 mb-5">
    {{-- Left Column: Palette --}}
    <div class="col-xl-3 col-lg-4 col-md-12">
        <div class="card custom-card shadow-sm h-100">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <i class="bi bi-grid-fill text-primary me-2"></i> {{ __('Variabel Sertifikat') }}
                </div>
                <span class="badge bg-primary-transparent">{{ count($variables) }} {{ __('Elemen') }}</span>
            </div>
            <div class="card-body p-3 overflow-auto" style="max-height: 70vh;">
                <p class="fs-13 text-muted mb-3">
                    {{ __('Klik pada elemen di bawah untuk menambahkannya atau memilihnya langsung pada area kanvas sertifikat.') }}
                </p>

                <div class="d-flex flex-column gap-2" id="palette-list">
                    @foreach($variables as $key => $meta)
                        <div class="border rounded p-2 palette-item bg-white d-flex align-items-center justify-content-between"
                             data-variable-key="{{ $key }}">
                            <div>
                                <div class="fw-semibold fs-14">{{ $meta['label'] }}</div>
                                <div class="fs-11 text-muted text-truncate" style="max-width: 180px;">
                                    {{ $meta['sample'] ?? __('Gambar Aksen') }}
                                </div>
                            </div>
                            <span class="badge bg-light text-dark rounded-circle border p-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="bi bi-plus-lg fs-12"></i>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Center Column: Interactive Canvas --}}
    <div class="col-xl-6 col-lg-8 col-md-12 editor-col-canvas">
        <div class="card custom-card shadow-sm">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="card-title mb-0">
                    <i class="bi bi-laptop text-warning me-2"></i> {{ __('Kanvas A4 Landscape') }}
                </div>
                <div class="form-check form-switch fs-13 mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="toggle-grid">
                    <label class="form-check-label text-muted cursor-pointer" for="toggle-grid">{{ __('Tampilkan Grid') }}</label>
                </div>
            </div>
            <div class="card-body p-2 bg-light d-flex align-items-center justify-content-center canvas-container-outer">
                {{-- Canvas element handled entirely via Vanilla JS --}}
                <div id="cert-canvas-container" class="position-relative shadow overflow-hidden my-3"
                     style="width: 800px; height: 565px; background-image: url('{{ $certificateTemplate->backgroundUrl() }}'); background-size: 100% 100%; background-position: center; border-radius: 4px; container-type: inline-size;">

                    {{-- Optional decorative grid lines (10% increments) --}}
                    <div id="canvas-grid-lines" class="position-absolute w-100 h-100 d-none"
                         style="background-size: 10% 10%; background-image: linear-gradient(to right, rgba(0,0,0,0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(0,0,0,0.05) 1px, transparent 1px);">
                    </div>

                    {{-- Draggable items inserted here dynamically by JS --}}
                </div>
            </div>
            <div class="card-footer bg-white border-top p-3 text-center">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ __('Gunakan kursor atau mouse untuk menggeser elemen. Gunakan tombol panah pada keyboard untuk pergeseran presisi.') }}
                </small>
            </div>
        </div>
    </div>

    {{-- Right Column: Inspector --}}
    <div class="col-xl-3 col-lg-12 col-md-12">
        <div class="card custom-card shadow-sm inspector-card h-100">
            <div class="card-header border-bottom">
                <div class="card-title mb-0">
                    <i class="bi bi-sliders text-success me-2"></i> {{ __('Pengaturan Elemen') }}
                </div>
            </div>
            <div class="card-body p-3">
                <div id="no-selection-notice" class="text-center py-5 text-muted">
                    <i class="bi bi-cursor-text fs-24 d-block mb-2 text-secondary"></i>
                    <p class="fs-13 mb-0">{{ __('Pilih salah satu variabel di kanvas atau palet untuk memodifikasi posisi dan format teks.') }}</p>
                </div>

                <div id="inspector-form" class="d-none">
                    <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary" id="inspector-var-label">Nama Variabel</h6>

                    {{-- D-Pad Directional Positioning Control --}}
                    <div class="bg-light p-3 rounded border mb-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fs-12 fw-semibold mb-0 text-dark"><i class="bi bi-arrows-move text-primary me-1"></i> {{ __('Kendali Posisi') }}</label>
                            <span class="badge bg-white text-dark border px-2 py-1 fw-bold font-monospace shadow-sm fs-11" id="coord-display">X: 50% | Y: 50%</span>
                        </div>
                        
                        <div class="btn-group w-100 my-2 shadow-sm" role="group" aria-label="Kendali Arah Posisi">
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" id="btn-nudge-left" title="Geser ke kiri">
                                <i class="bi bi-arrow-left"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" id="btn-nudge-right" title="Geser ke kanan">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" id="btn-nudge-up" title="Geser ke atas">
                                <i class="bi bi-arrow-up"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" id="btn-nudge-down" title="Geser ke bawah">
                                <i class="bi bi-arrow-down"></i>
                            </button>
                        </div>

                        <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between">
                            <small class="text-muted fs-11"><i class="bi bi-speedometer2 me-1"></i> Jarak Geser:</small>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="nudge-step" id="step-fine" value="0.2">
                                <label class="btn btn-outline-secondary py-0 px-2 text-dark fs-11" for="step-fine">Halus</label>
                                <input type="radio" class="btn-check" name="nudge-step" id="step-normal" value="1.0" checked>
                                <label class="btn btn-outline-secondary py-0 px-2 text-dark fs-11" for="step-normal">Normal</label>
                                <input type="radio" class="btn-check" name="nudge-step" id="step-fast" value="5.0">
                                <label class="btn btn-outline-secondary py-0 px-2 text-dark fs-11" for="step-fast">Cepat</label>
                            </div>
                        </div>

                        {{-- Hidden inputs to store X/Y values --}}
                        <input type="hidden" id="prop-x">
                        <input type="hidden" id="prop-y">
                    </div>

                    {{-- Image properties (only displayed when variable is image, e.g., tanda_tangan) --}}
                    <div id="image-properties" class="mb-3" style="display: none;">
                        <label class="form-label fs-12 fw-semibold mb-1"><i class="bi bi-arrows-angle-expand text-success me-1"></i> {{ __('Skala Lebar Gambar (%)') }}</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.5" min="2" max="80" id="prop-width" class="form-control form-control-sm" placeholder="14">
                            <span class="input-group-text bg-white fw-bold">%</span>
                        </div>
                        <small class="text-muted fs-11 mt-1 d-block">Sesuaikan proporsi besar-kecilnya spesimen tanda tangan di atas kanvas kertas.</small>
                    </div>

                    <div id="text-properties">
                        <div class="mb-3">
                            <label class="form-label fs-12 fw-semibold mb-1"><i class="bi bi-type text-info me-1"></i> {{ __('Ukuran Font (pt)') }}</label>
                            <div class="d-flex align-items-center gap-2">
                                {{-- MS Word Style Combo Box --}}
                                <div class="input-group input-group-sm flex-grow-1 shadow-sm">
                                    <input type="number" list="msword-fontsizes-list" id="prop-fontSize" min="6" max="100" step="1" class="form-control form-control-sm text-center font-monospace fw-bold px-2" placeholder="12" style="background-color: #fff;">
                                    <button class="btn btn-outline-secondary dropdown-toggle px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Daftar Ukuran Font Standar"></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1" id="dropdown-fontsize-list" style="max-height: 240px; overflow-y: auto; min-width: 85px;">
                                        @foreach([8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36, 48, 72] as $size)
                                            <li><a class="dropdown-item fs-13 text-center py-1 font-monospace" href="javascript:void(0);" data-val="{{ $size }}">{{ $size }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>

                                {{-- MS Word Style "Increase Font Size" (A▲) & "Decrease Font Size" (A▼) Buttons --}}
                                <div class="btn-group btn-group-sm shadow-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary px-2 d-flex align-items-center justify-content-center" id="btn-fontsize-up" title="Perbesar Ukuran Font (A▲)" style="width: 38px;">
                                        <span class="fw-bold fs-13 me-1" style="font-family: 'Times New Roman', serif;">A</span><i class="bi bi-caret-up-fill fs-9 text-primary"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary px-2 d-flex align-items-center justify-content-center" id="btn-fontsize-down" title="Perkecil Ukuran Font (A▼)" style="width: 38px;">
                                        <span class="fw-bold fs-11 me-1" style="font-family: 'Times New Roman', serif;">A</span><i class="bi bi-caret-down-fill fs-9 text-primary"></i>
                                    </button>
                                </div>
                            </div>
                            <datalist id="msword-fontsizes-list">
                                @foreach([8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36, 48, 72] as $size)
                                    <option value="{{ $size }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-12 fw-semibold mb-1">{{ __('Ketebalan Font') }}</label>
                            <select id="prop-fontWeight" class="form-select form-select-sm">
                                <option value="400">{{ __('Normal (400)') }}</option>
                                <option value="500">{{ __('Medium (500)') }}</option>
                                <option value="600">{{ __('Semi Bold (600)') }}</option>
                                <option value="700">{{ __('Bold (700)') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-12 fw-semibold mb-1">{{ __('Gaya Font') }}</label>
                            <select id="prop-fontStyle" class="form-select form-select-sm">
                                <option value="normal">{{ __('Normal') }}</option>
                                <option value="italic">{{ __('Italic (Miring)') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-12 fw-semibold mb-1">{{ __('Perataan (Align)') }}</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="prop-textAlign" id="align-left" value="left">
                                <label class="btn btn-outline-light text-dark fs-12 border" for="align-left"><i class="bi bi-text-left"></i> Left</label>

                                <input type="radio" class="btn-check" name="prop-textAlign" id="align-center" value="center">
                                <label class="btn btn-outline-light text-dark fs-12 border" for="align-center"><i class="bi bi-text-center"></i> Center</label>

                                <input type="radio" class="btn-check" name="prop-textAlign" id="align-right" value="right">
                                <label class="btn btn-outline-light text-dark fs-12 border" for="align-right"><i class="bi bi-text-right"></i> Right</label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fs-12 fw-semibold mb-1">{{ __('Warna Teks (Hex)') }}</label>
                            <div class="input-group input-group-sm">
                                <input type="color" id="prop-color-picker" class="form-control form-control-color border-end-0" title="Pilih warna">
                                <input type="text" id="prop-color-hex" class="form-control text-uppercase" placeholder="#0F2A4A" maxlength="7">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4 pt-2 border-top">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn-remove-element">
                            <i class="bi bi-trash3 me-1"></i> {{ __('Sembunyikan dari Kanvas') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Floating Save Layout Footer / Bar --}}
<div class="card custom-card shadow border-0 position-sticky bottom-0 z-3 mb-4 bg-dark text-white">
    <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span id="unsaved-badge" class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle-fill"></i> {{ __('Tersimpan') }}</span>
            <span class="fs-13 text-light">{{ __('Setiap perubahan posisi dan atribut warna pada kanvas bersifat lokal hingga Anda menekan tombol simpan.') }}</span>
        </div>
        <div>
            <button type="button" id="btn-save-layout" class="btn btn-primary px-4 fw-medium shadow">
                <i class="bi bi-check-circle-fill me-1"></i> {{ __('Simpan Layout') }}
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.CertLayoutConfig = {
        updateUrl: @json(route('certificate-templates.layout.update', $certificateTemplate)),
        csrfToken: @json(csrf_token()),
        definitions: @json($variables),
        samples: @json($sampleData),
        existingLayout: @json($existingLayout),
        defaultLayout: @json(\App\Support\CertificateVariables::defaultLayout()),
        signatureUrl: @json($certificateTemplate->signatureUrl()),
    };
</script>
<script src="{{ asset('backend/assets/js/certificate-layout-editor.js') }}"></script>
@endpush
