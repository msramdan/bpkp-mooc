{{-- Status penyelesaian otomatis video / dokumen. Diperbarui oleh lesson-tracker.js. --}}
@php
    $isVideo = $tracking['kind'] === 'video';
@endphp
<div class="lesson-track {{ $tracking['completed'] ? 'is-done' : '' }}" id="lessonTrack"
    data-url="{{ route('peserta.kursus.lessons.progress', [$course, $lesson]) }}"
    data-state='@json($tracking)'
    data-next-url="{{ $nextLesson ? route('peserta.kursus.lessons.show', [$course, $nextLesson]) : '' }}">
    <span class="lesson-track__icon"><i class="bi {{ $isVideo ? 'bi-play-btn' : 'bi-file-earmark-text' }}" data-track-icon></i></span>
    <div class="lesson-track__body">
        <div class="lesson-track__title" data-track-title>
            @if ($tracking['completed'])
                {{ __('Materi selesai') }}
            @elseif ($isVideo)
                {{ __('Tonton video sampai selesai') }}
            @else
                {{ __('Baca dokumen sampai halaman terakhir') }}
            @endif
        </div>
        <div class="lesson-track__meter"><span data-track-bar style="width: {{ $tracking['percent'] }}%"></span></div>
        <div class="lesson-track__hint" data-track-hint>
            @if ($tracking['completed'])
                {{ __('Progres tercatat otomatis.') }}
            @elseif ($isVideo)
                {{ __('Video tidak bisa dipercepat atau dilompati. Materi selesai otomatis saat video habis.') }}
            @else
                {{ __('Gulir sampai halaman terakhir. Materi selesai otomatis.') }}
            @endif
        </div>
    </div>
    <span class="lesson-track__pct" data-track-pct>{{ $tracking['percent'] }}%</span>
</div>
