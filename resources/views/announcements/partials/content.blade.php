{{-- Isi satu pengumuman: teks, gambar lampiran, tombol tautan & unduhan. Semua input di-escape. --}}
@php
    /** @var \App\Models\Announcement $a */
    $tz = \App\Http\Requests\AnnouncementRequest::TZ;
    $attachmentUrl = $a->exists && $a->hasAttachment() ? route('announcements.attachment', $a) : null;
@endphp

@if (filled($a->body))
    <div class="ann-body">{{ \App\Services\AnnouncementService::renderBody($a->body) }}</div>
@endif

@if ($attachmentUrl && $a->attachmentIsImage())
    <figure class="ann-figure">
        <img src="{{ $attachmentUrl }}?inline=1" alt="{{ $a->attachment_name }}" loading="lazy">
    </figure>
@endif

@if ($attachmentUrl || filled($a->link_url))
    <div class="ann-actions">
        @if ($attachmentUrl)
            <a class="ann-file" href="{{ $attachmentUrl }}" download>
                <span class="ann-file__ext">{{ $a->attachmentExtension() }}</span>
                <span class="ann-file__info">
                    <span class="ann-file__name">{{ $a->attachment_name }}</span>
                    <span class="ann-file__size">{{ __('Lampiran') }} · {{ $a->attachmentSizeLabel() }}</span>
                </span>
                <i class="ri-download-2-line ann-file__dl" aria-hidden="true"></i>
            </a>
        @endif
        @if (filled($a->link_url))
            <a class="ann-link-btn" href="{{ $a->link_url }}" target="_blank" rel="noopener noreferrer nofollow">
                {{ $a->link_label ?: __('Buka tautan') }} <i class="ri-arrow-right-up-line" aria-hidden="true"></i>
            </a>
        @endif
    </div>
@endif
