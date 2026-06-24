@php
    $course = $enrollment->course;
    $statusClass = match ($enrollment->status) {
        'Selesai' => 'success',
        'Berlangsung' => 'primary',
        default => 'secondary',
    };
@endphp

<a href="{{ route('peserta.kursus.show', $course) }}"
    class="peserta-kursus-card peserta-kursus-card--elevated peserta-kursus-card--link peserta-kursus-item peserta-kursus-card--{{ $statusClass }}"
    data-status="{{ $enrollment->status }}"
    data-kategori="{{ $course->kategori }}"
    data-search="{{ strtolower($course->judul.' '.$course->kode.' '.$course->kategori.' '.$course->instruktur) }}">
    <div class="peserta-kursus-card__media">
        <x-course-thumbnail :course="$course" class="peserta-kursus-card__image" />
        <div class="peserta-kursus-card__media-shade" aria-hidden="true"></div>
        <span class="peserta-kursus-card__status peserta-kursus-card__status--{{ $statusClass }}">
            {{ $enrollment->status }}
        </span>
        <span class="peserta-kursus-card__duration">
            {{ $course->durasi_jam }} {{ __('jam') }}
            <span class="peserta-kursus-card__duration-dot">·</span>
            {{ $course->modul_total }} {{ __('modul') }}
        </span>
        <div class="peserta-kursus-card__progress" title="{{ $enrollment->progress }}%">
            <div class="peserta-kursus-card__progress-bar" style="width: {{ $enrollment->progress }}%"></div>
        </div>
        <div class="peserta-kursus-card__progress-badge">{{ $enrollment->progress }}%</div>
    </div>
    <div class="peserta-kursus-card__body">
        <span class="peserta-kursus-card__category">{{ $course->kategori }}</span>
        <h3 class="peserta-kursus-card__title">{{ $course->judul }}</h3>
        <p class="peserta-kursus-card__instructor">
            <i class="bi bi-person-circle"></i>
            <span>{{ $course->instruktur }}</span>
        </p>
        <div class="peserta-kursus-card__footer">
            <span class="peserta-kursus-card__chip">{{ $course->kode }}</span>
            <span class="peserta-kursus-card__chip peserta-kursus-card__chip--muted">
                {{ __('Modul') }} {{ $enrollment->modulLabel() }}
            </span>
            @if ($enrollment->deadline)
                <span class="peserta-kursus-card__chip peserta-kursus-card__chip--warn">
                    {{ $enrollment->deadline->format('d M Y') }}
                </span>
            @endif
        </div>
    </div>
</a>
