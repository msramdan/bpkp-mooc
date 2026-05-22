@php
    $course = $enrollment->course;
    $statusClass = match ($enrollment->status) {
        'Selesai' => 'success',
        'Berlangsung' => 'primary',
        default => 'secondary',
    };
@endphp

<a href="{{ route('peserta.kursus.show', $course) }}" class="peserta-kursus-card peserta-kursus-card--link peserta-kursus-item"
    data-status="{{ $enrollment->status }}"
    data-kategori="{{ $course->kategori }}"
    data-search="{{ strtolower($course->judul.' '.$course->kode.' '.$course->kategori.' '.$course->instruktur) }}">
    <div class="peserta-kursus-card__thumb">
        <img src="{{ $course->thumbnail }}" alt="{{ $course->judul }}" loading="lazy"
            onerror="this.src='https://placehold.co/640x360/2b478b/ffffff?text={{ urlencode($course->kode) }}'">
        <span class="peserta-kursus-card__status badge bg-{{ $statusClass }}">{{ $enrollment->status }}</span>
        <span class="peserta-kursus-card__duration">{{ $course->durasi_jam }} {{ __('jam') }} ·
            {{ $course->modul_total }} {{ __('modul') }}</span>
        <div class="peserta-kursus-card__progress" title="{{ $enrollment->progress }}%">
            <div class="peserta-kursus-card__progress-bar" style="width: {{ $enrollment->progress }}%"></div>
        </div>
        <div class="peserta-kursus-card__play" aria-hidden="true">
            <span class="peserta-kursus-card__play-icon"><i class="bi bi-play-fill"></i></span>
        </div>
    </div>
    <div class="peserta-kursus-card__body">
        <h3 class="peserta-kursus-card__title">{{ $course->judul }}</h3>
        <p class="peserta-kursus-card__meta mb-0">
            <strong>{{ $course->instruktur }}</strong><br>
            {{ $course->kategori }} · {{ __('Modul') }} {{ $enrollment->modulLabel() }}
        </p>
        <div class="peserta-kursus-card__footer">
            <span class="badge bg-light text-dark">{{ $course->kode }}</span>
            <span class="text-muted">{{ __('Progres') }} {{ $enrollment->progress }}%</span>
            @if ($enrollment->deadline)
                <span class="text-muted">· {{ __('Batas waktu') }} {{ $enrollment->deadline->format('d M Y') }}</span>
            @endif
        </div>
    </div>
</a>
