@php
    $fileUrl = trim((string) ($url ?? ''));
@endphp
@if ($fileUrl !== '')
    @php
        $path = parse_url($fileUrl, PHP_URL_PATH) ?: $fileUrl;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: '');
        $fileName = basename($path) ?: ($title ?? __('Berkas'));
        $kind = match (true) {
            in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'bmp', 'svg'], true) => 'image',
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['mp4', 'webm', 'mov', 'm4v', 'ogg'], true) => 'video',
            in_array($ext, ['txt', 'csv', 'log', 'md'], true) => 'text',
            default => 'other',
        };
        $displayTitle = $title ?? $fileName;
        $compact = (bool) ($compact ?? false);
    @endphp
    <div class="activity-file-preview {{ $compact ? 'activity-file-preview--compact' : '' }}">
        @unless ($compact)
            <span class="activity-file-preview__name" title="{{ $fileName }}">
                <i class="ri-file-line me-1" aria-hidden="true"></i>{{ $fileName }}
            </span>
        @endunless
        <div class="activity-file-preview__actions">
            <button type="button"
                class="btn btn-sm btn-primary-light btn-wave js-activity-file-preview"
                data-url="{{ $fileUrl }}"
                data-title="{{ $displayTitle }}"
                data-kind="{{ $kind }}"
                data-ext="{{ $ext }}"
                title="{{ __('Preview') }}">
                <i class="ri-eye-line"></i>
                <span>{{ __('Preview') }}</span>
            </button>
            <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer"
                class="btn btn-sm btn-light btn-wave"
                title="{{ __('Buka di tab baru') }}">
                <i class="ri-external-link-line"></i>
                <span>{{ __('Tab baru') }}</span>
            </a>
        </div>
    </div>
@endif
