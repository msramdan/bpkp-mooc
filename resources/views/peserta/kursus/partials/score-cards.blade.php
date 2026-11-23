{{-- Nilai PG (otomatis) dan Nilai Esai (dinilai admin) ditampilkan TERPISAH. --}}
@php
    $hasPg = $response->max_possible_score > 0;
    $essay = \App\Services\SurveyScoring::essayStatus($response);
@endphp
<div class="score-cards">
    @if ($hasPg)
        <div class="score-card">
            <span class="score-card__label">{{ __('Nilai PG') }}</span>
            <span class="score-card__value">{{ number_format(\App\Services\SurveyScoring::percentage($response), 1) }}<small>/100</small></span>
            <span class="score-card__meta">{{ __('Benar :c dari :t soal', ['c' => number_format($response->total_score / 100, 0), 't' => number_format($response->max_possible_score / 100, 0)]) }}</span>
        </div>
    @endif

    @if ($essay === 'graded')
        <div class="score-card score-card--essay">
            <span class="score-card__label">{{ __('Nilai Esai') }}</span>
            <span class="score-card__value">{{ number_format((float) $response->essay_score, 1) }}<small>/100</small></span>
            <span class="score-card__meta">{{ __(':n esai dinilai pengajar', ['n' => $response->essay_graded_count]) }}</span>
        </div>
    @elseif ($essay === 'pending')
        <div class="score-card score-card--pending">
            <span class="score-card__label">{{ __('Nilai Esai') }}</span>
            <span class="score-card__value score-card__value--text"><i class="bi bi-hourglass-split me-1"></i>{{ __('Menunggu') }}</span>
            <span class="score-card__meta">{{ __('Esai sedang dinilai pengajar (:g/:n)', ['g' => $response->essay_graded_count, 'n' => $response->essay_answered_count]) }}</span>
        </div>
    @endif

    @if (! $hasPg && $essay === 'none')
        <div class="score-card score-card--sent">
            <span class="score-card__label">{{ __('Status') }}</span>
            <span class="score-card__value score-card__value--text"><i class="bi bi-check-all me-1"></i>{{ __('Terkirim') }}</span>
        </div>
    @endif
</div>
