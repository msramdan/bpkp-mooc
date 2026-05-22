@php
    $isTerbit = $item['is_terbit'] ?? ($item['status'] === 'Terbit');
@endphp

<article class="peserta-sertifikat-card {{ $isTerbit ? 'peserta-sertifikat-card--issued' : 'peserta-sertifikat-card--pending' }}">
    <div class="peserta-sertifikat-card__preview">
        <div class="peserta-sertifikat-card__pattern" aria-hidden="true"></div>
        @if ($isTerbit)
            <div class="peserta-sertifikat-card__ribbon">{{ __('Terverifikasi') }}</div>
        @endif
        <div class="peserta-sertifikat-card__medal">
            <i class="bi {{ $isTerbit ? 'bi-award-fill' : 'bi-hourglass-split' }}"></i>
        </div>
        <p class="peserta-sertifikat-card__brand mb-0">{{ config('app.brand_display') }} MOOC</p>
        <h3 class="peserta-sertifikat-card__title">{{ $item['kursus'] }}</h3>
        <div class="peserta-sertifikat-card__nomor-wrap">
            <span class="peserta-sertifikat-card__nomor">
                {{ $isTerbit ? $item['nomor'] : __('Menunggu penerbitan') }}
            </span>
        </div>
        <div class="peserta-sertifikat-card__meta-row">
            <div class="peserta-sertifikat-card__meta-item">
                <span>{{ __('Tanggal terbit') }}</span>
                <strong>{{ $item['tanggal'] }}</strong>
            </div>
            <div class="peserta-sertifikat-card__meta-item">
                <span>{{ __('Nilai akhir') }}</span>
                <strong>{{ $isTerbit ? $item['nilai'] : '—' }}</strong>
            </div>
        </div>
    </div>
    <div class="peserta-sertifikat-card__footer">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="peserta-sertifikat-card__course-info">
                @if (! empty($item['thumbnail']))
                    <img src="{{ $item['thumbnail'] }}" alt="" class="peserta-sertifikat-card__thumb" loading="lazy">
                @else
                    <span class="peserta-sertifikat-card__thumb d-flex align-items-center justify-content-center">
                        <i class="bi bi-mortarboard text-muted"></i>
                    </span>
                @endif
                <div>
                    @if (! empty($item['kode']))
                        <span class="peserta-sertifikat-card__course-kode">{{ $item['kode'] }}</span>
                    @endif
                    @include('peserta.partials.status-badge', ['status' => $item['status']])
                </div>
            </div>
        </div>
        <div class="peserta-sertifikat-card__actions">
            @if ($isTerbit)
                <button type="button" class="btn btn-primary btn-sm btn-wave" disabled>
                    <i class="bi bi-download me-1"></i>{{ __('Unduh PDF') }}
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm btn-wave" disabled>
                    <i class="bi bi-share me-1"></i>{{ __('Bagikan') }}
                </button>
            @else
                <button type="button" class="btn btn-outline-primary btn-sm btn-wave w-100" disabled>
                    <i class="bi bi-arrow-right-circle me-1"></i>{{ __('Lanjutkan kursus') }}
                </button>
            @endif
        </div>
    </div>
</article>
