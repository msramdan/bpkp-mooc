{{-- Ringkasan perubahan before -> after (maks 4 field) untuk daftar. --}}
@php $diff = $log->diff(); @endphp
@if ($diff)
    <div class="alog-chg">
        @foreach (array_slice($diff, 0, 4, true) as $field => [$old, $new])
            <div>
                <code>{{ $field }}</code>
                @if ($log->event !== 'created')
                    <del>@include('activity-logs.partials.value', ['value' => $old, 'limit' => 60])</del> →
                @endif
                @if ($log->event !== 'deleted')
                    <ins>@include('activity-logs.partials.value', ['value' => $new, 'limit' => 60])</ins>
                @endif
            </div>
        @endforeach
        @if (count($diff) > 4)
            <div class="alog-meta">+{{ count($diff) - 4 }} {{ __('field lainnya') }}</div>
        @endif
    </div>
@endif
