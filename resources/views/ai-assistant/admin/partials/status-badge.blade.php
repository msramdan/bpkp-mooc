@php
    $labels = ['success' => 'Berhasil', 'failed' => 'Gagal', 'rejected' => 'Ditolak', 'processing' => 'Diproses',
        'passed' => 'Lolos', 'blocked' => 'Diblokir', 'redacted' => 'Disensor'];
@endphp
<span class="aiad-badge aiad-badge--{{ $value }}">{{ __($labels[$value] ?? $value) }}</span>
