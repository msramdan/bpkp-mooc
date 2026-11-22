{{-- Tampilkan nilai before/after dengan aman (selalu di-escape). --}}
@php
    $display = match (true) {
        $value === null => null,
        is_bool($value) => $value ? 'ya' : 'tidak',
        is_array($value) => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        default => (string) $value,
    };
@endphp
@if ($display === null || $display === '')
    <span class="is-null">kosong</span>
@else
    {{ \Illuminate\Support\Str::limit(strip_tags($display), $limit ?? 300) }}
@endif
