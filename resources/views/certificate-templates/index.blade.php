@extends('layouts.app')

@section('title', __('Master Template Sertifikat'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Master Template Sertifikat') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Template Sertifikat') }}</li>
            </ol>
        </div>
        <div>
            <a href="{{ route('certificate-templates.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> {{ __('Tambah Template Baru') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card custom-card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title mb-0">{{ __('Daftar Template Sertifikat Terselaras') }}</div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-nowrap w-100 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th style="max-width: 200px;">Nama Template</th>
                            <th>Pratinjau Gambar Latar</th>
                            <th style="max-width: 220px;">Pejabat Penandatangan</th>
                            <th>Status Default</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $index => $template)
                            <tr>
                                <td class="text-center">{{ $templates->firstItem() + $index }}</td>
                                <td class="fw-bold text-dark" style="max-width: 200px; white-space: normal; word-break: break-word;">{{ Str::limit($template->title, 40) }}</td>
                                <td>
                                    <a href="javascript:void(0);" class="js-preview-certificate text-decoration-none d-inline-block"
                                        data-title="{{ $template->title }}" 
                                        data-bg="{{ $template->backgroundUrl() }}"
                                        data-sig="{{ $template->signatureUrl() ?? '' }}"
                                        data-signer-name="{{ $template->signer_name ?? 'Dr. H. Ahmad Nurman, M.Sc.' }}"
                                        data-signer-title="{{ $template->signer_title ?? 'Kepala Pusat Pendidikan dan Pelatihan BPKP' }}"
                                        data-is-default="{{ $template->is_default ? '1' : '0' }}"
                                        data-positions="{{ json_encode($template->layout()) }}"
                                        title="{{ __('Klik gambar untuk melihat pratinjau sertifikat') }}" data-bs-toggle="modal" data-bs-target="#certificatePreviewModal">
                                        @if($template->background_image_url)
                                            <img src="{{ $template->backgroundUrl() }}" alt="BG" class="img-thumbnail shadow-sm cursor-pointer" style="height: 50px; border-radius: 4px; transition: transform 0.2s;">
                                        @else
                                            <span class="badge bg-secondary-transparent text-secondary cursor-pointer py-2 px-2"><i class="bi bi-eye me-1 text-primary"></i> Pratinjau Default</span>
                                        @endif
                                    </a>
                                </td>
                                <td style="max-width: 220px; white-space: normal; word-break: break-word;">
                                    <div class="fw-semibold">{{ Str::limit($template->signer_name ?? '-', 35) }}</div>
                                    <small class="text-muted">{{ Str::limit($template->signer_title ?? '-', 45) }}</small>
                                </td>
                                <td>
                                    @if($template->is_default)
                                        <span class="badge bg-success-transparent text-success border border-success border-opacity-25 px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i> Utama (Default)
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border px-3 py-2">Opsional</span>
                                    @endif
                                </td>
                                <td class="text-nowrap text-center">
                                    <button type="button" class="btn btn-sm btn-success btn-icon btn-wave js-preview-certificate"
                                        data-title="{{ $template->title }}" 
                                        data-bg="{{ $template->backgroundUrl() }}"
                                        data-sig="{{ $template->signatureUrl() ?? '' }}"
                                        data-signer-name="{{ $template->signer_name ?? 'Dr. H. Ahmad Nurman, M.Sc.' }}"
                                        data-signer-title="{{ $template->signer_title ?? 'Kepala Pusat Pendidikan dan Pelatihan BPKP' }}"
                                        data-is-default="{{ $template->is_default ? '1' : '0' }}"
                                        data-positions="{{ json_encode($template->layout()) }}"
                                        title="{{ __('Preview / View') }}" data-bs-toggle="modal" data-bs-target="#certificatePreviewModal">
                                        <i class="ri-eye-line"></i>
                                    </button>

                                    <a href="{{ route('certificate-templates.layout.edit', $template) }}" class="btn btn-sm btn-info btn-icon btn-wave" title="{{ __('Atur Layout') }}">
                                        <i class="ri-layout-3-line"></i>
                                    </a>

                                    <a href="{{ route('certificate-templates.edit', $template) }}" class="btn btn-sm btn-primary btn-icon btn-wave" title="{{ __('Edit') }}">
                                        <i class="ri-pencil-line"></i>
                                    </a>

                                    <form action="{{ route('certificate-templates.destroy', $template) }}" method="post" class="d-inline">
                                        @csrf
                                        @method('delete')
                                        <button type="button" class="btn btn-sm btn-danger btn-icon btn-wave js-delete-confirm" title="{{ __('Delete') }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-warning"></i>
                                    {{ __('Belum ada data template sertifikat di sistem. Klik Tambah Template untuk membuat baru.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $templates->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Pratinjau Sertifikat (A4 Landscape Scale) -->
    <div class="modal fade" id="certificatePreviewModal" tabindex="-1" aria-labelledby="certificatePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-dark text-white px-4 py-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-award fs-3 text-warning me-3"></i>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0" id="certificatePreviewModalLabel">
                                {{ __('Pratinjau Template Sertifikat:') }} <span id="modal-preview-title" class="text-warning"></span>
                            </h5>
                            <small class="text-white-50">{{ __('Visualisasi simulasi penerbitan sertifikat bagi peserta kursus MOOC BPKP') }}</small>
                        </div>
                        <span id="modal-preview-badge" class="ms-3"></span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light d-flex justify-content-center">
                    <!-- Scaled A4 Landscape Simulation -->
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap');
                        .cert-preview-box {
                            width: 100%;
                            max-width: 960px;
                            aspect-ratio: 297 / 210;
                            position: relative;
                            background-color: #ffffff;
                            background-size: 100% 100%;
                            background-position: center;
                            background-repeat: no-repeat;
                            box-shadow: 0 15px 35px rgba(0,0,0,0.18);
                            border: 1px solid #dee2e6;
                            display: flex;
                            flex-direction: column;
                            justify-content: space-between;
                            font-family: 'Plus Jakarta Sans', sans-serif;
                            overflow: hidden;
                            container-type: inline-size;
                        }
                        .cert-preview-element {
                            position: absolute;
                            line-height: 1.25;
                            white-space: nowrap;
                            user-select: none;
                        }
                        .var-judul_sertifikat { font-family: 'Cinzel', serif; letter-spacing: 2px; text-transform: uppercase; }
                        .var-nama_peserta { font-family: 'Playfair Display', serif; }
                        .var-nama_kursus { font-family: 'Playfair Display', serif; }
                        .var-brand_instansi { letter-spacing: 3px; text-transform: uppercase; }
                        .var-nama_penandatangan { text-decoration: underline; }
                    </style>

                    <div class="cert-preview-box" id="modal-cert-sheet">
                        <!-- Dynamic customized elements injected via JS -->
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 bg-white d-flex justify-content-between align-items-center">
                    <span class="text-muted fs-13"><i class="bi bi-info-circle me-1 text-primary"></i> {{ __('Catatan: Saat dicetak oleh peserta, Nama, Judul Kursus, Nomor, Nilai, dan Tanggal akan disesuaikan secara otomatis oleh sistem.') }}</span>
                    <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">{{ __('Tutup Pratinjau') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const CERT_DEFINITIONS = @json(\App\Support\CertificateVariables::DEFINITIONS);
                const CERT_SAMPLES = @json(\App\Support\CertificateVariables::samples());

                const previewButtons = document.querySelectorAll('.js-preview-certificate');
                previewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const title = this.getAttribute('data-title');
                        const bg = this.getAttribute('data-bg');
                        const sig = this.getAttribute('data-sig');
                        const signerName = this.getAttribute('data-signer-name');
                        const signerTitle = this.getAttribute('data-signer-title');
                        const isDefault = this.getAttribute('data-is-default') === '1';
                        const rawPositions = this.getAttribute('data-positions');

                        let positions = {};
                        try {
                            positions = rawPositions ? JSON.parse(rawPositions) : {};
                        } catch (e) {
                            console.error('Failed to parse certificate positions', e);
                        }

                        const titleEl = document.getElementById('modal-preview-title');
                        const sheetEl = document.getElementById('modal-cert-sheet');
                        const badgeEl = document.getElementById('modal-preview-badge');

                        if (titleEl) titleEl.textContent = title;
                        if (sheetEl) {
                            sheetEl.style.backgroundImage = 'url("' + bg + '")';
                            sheetEl.innerHTML = ''; // bersihkan elemen kanvas sebelumnya

                            // Render setiap elemen sesuai koordinat tersimpan dari layout editor
                            for (const [key, props] of Object.entries(positions)) {
                                const def = CERT_DEFINITIONS[key] || {};
                                const isImage = def.type === 'image';

                                const el = document.createElement('div');
                                el.className = 'cert-preview-element var-' + key;
                                el.style.left = (props.x !== undefined ? props.x : 50) + '%';
                                el.style.top = (props.y !== undefined ? props.y : 50) + '%';
                                el.style.fontSize = `calc(${(props.fontSize !== undefined ? props.fontSize : 14)} * 0.1188cqi)`;
                                el.style.fontWeight = props.fontWeight || '400';
                                el.style.fontStyle = props.fontStyle || 'normal';
                                el.style.textAlign = props.textAlign || 'center';
                                el.style.color = props.color || '#0F2A4A';
                                
                                let anchorX = '-50%';
                                if (!isImage) {
                                    if (props.textAlign === 'left') anchorX = '0%';
                                    else if (props.textAlign === 'right') anchorX = '-100%';
                                }
                                el.style.transform = `translate(${anchorX}, -50%)`;

                                if (isImage && key === 'tanda_tangan') {
                                    if (sig && sig !== '') {
                                        el.innerHTML = '<img src="' + sig + '" alt="Tanda Tangan" style="max-height: 75px; max-width: 170px; object-fit: contain; display: block; margin: 0 auto;">';
                                    } else {
                                        el.innerHTML = '<div class="border border-secondary border-dashed px-2 py-1 bg-white bg-opacity-75 rounded text-muted fs-11"><i class="bi bi-vector-pen me-1"></i>Spesimen TTD</div>';
                                    }
                                } else if (key === 'nama_penandatangan') {
                                    el.textContent = signerName || CERT_SAMPLES[key] || key;
                                } else if (key === 'jabatan_penandatangan') {
                                    el.textContent = signerTitle || CERT_SAMPLES[key] || key;
                                } else {
                                    el.textContent = CERT_SAMPLES[key] || key;
                                }

                                sheetEl.appendChild(el);
                            }
                        }

                        if (badgeEl) {
                            if (isDefault) {
                                badgeEl.className = 'badge bg-success ms-2 px-3 py-2';
                                badgeEl.innerHTML = '<i class="bi bi-check-circle me-1"></i> Utama (Default)';
                            } else {
                                badgeEl.className = 'badge bg-light text-dark border ms-2 px-3 py-2';
                                badgeEl.textContent = 'Opsional';
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
