@extends('layouts.app')

@section('title', __('Pengumuman'))

@section('content')
    @php $tz = \App\Http\Requests\AnnouncementRequest::TZ; @endphp

    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Pengumuman') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ auth()->user()->hasRole(\App\Support\Roles::PESERTA) ? route('peserta.dashboard') : route('dashboard') }}">{{ __('Beranda') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Pengumuman') }}</li>
            </ol>
        </div>
    </div>

    <section class="ann-feed-hero">
        <i class="ri-megaphone-line ann-feed-hero__horn" aria-hidden="true"></i>
        <h2>{{ __('Papan Pengumuman') }}</h2>
        <p>
            @if ($announcements->isEmpty())
                {{ __('Belum ada pengumuman yang sedang berlaku untuk Anda.') }}
            @else
                {{ __(':n pengumuman sedang berlaku. Informasi resmi dari penyelenggara MOOC BPKP.', ['n' => $announcements->count()]) }}
            @endif
        </p>
    </section>

    @if ($announcements->isEmpty())
        <div class="ann-empty">
            <i class="ri-inbox-2-line" aria-hidden="true"></i>
            <h6 class="fw-semibold mb-1">{{ __('Belum ada pengumuman') }}</h6>
            <p class="mb-0 fs-13">{{ __('Pengumuman baru akan muncul di sini dan sebagai pop up setelah Anda login.') }}</p>
        </div>
    @else
        <div class="ann-list">
            @foreach ($announcements as $a)
                @php $date = ($a->starts_at ?? $a->created_at)->timezone($tz); @endphp
                <article class="ann-item ann-cat--{{ $a->category }}" id="pengumuman-{{ $a->id }}">
                    <div class="ann-item__stamp" aria-hidden="true">
                        <span class="ann-item__day">{{ $date->format('d') }}</span>
                        <span class="ann-item__mon">{{ $date->translatedFormat('M') }}</span>
                        <span class="ann-item__yr">{{ $date->format('Y') }}</span>
                    </div>
                    <div class="ann-item__main">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="ann-chip"><span class="ann-dot"></span>{{ \App\Models\Announcement::CATEGORIES[$a->category] ?? $a->category }}</span>
                            @unless (in_array($a->id, $seenIds, true))
                                <span class="ann-new">{{ __('BARU') }}</span>
                            @endunless
                            <span class="ann-meta">
                                <span class="visually-hidden">{{ $date->translatedFormat('d F Y') }}</span>
                                @if ($a->ends_at)
                                    <span><i class="ri-timer-line"></i>{{ __('Berlaku s.d. :d', ['d' => $a->ends_at->timezone($tz)->translatedFormat('d M Y, H:i')]) }} WIB</span>
                                @endif
                            </span>
                        </div>
                        <h2 class="ann-item__title">{{ $a->title }}</h2>
                        @include('announcements.partials.content', ['a' => $a])
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
