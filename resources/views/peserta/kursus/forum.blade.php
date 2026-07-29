@extends('layouts.app')

@section('title', __('Forum').' - '.$course->judul)

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-kursus-detail.css" rel="stylesheet">
@endpush

@section('content')
    @include('peserta.partials.page-header', [
        'title' => __('Forum kursus'),
        'parent' => $course->judul,
        'parentUrl' => route('peserta.kursus.show', $course),
    ])

    <x-alert />

    <div class="peserta-course-detail">
        <div class="peserta-course-detail__hero card custom-card mb-3">
            <div class="peserta-course-detail__hero-inner">
                <x-course-thumbnail :course="$course" class="peserta-course-detail__hero-thumb" />
                <div class="peserta-course-detail__hero-main">
                    <div class="peserta-course-detail__hero-badges">
                        <span class="badge bg-primary-transparent">{{ __('Forum aktif') }}</span>
                        <span class="badge bg-light text-dark">{{ $threads->count() }} {{ __('thread') }}</span>
                    </div>
                    <h2 class="peserta-course-detail__hero-title mb-1">{{ $course->judul }}</h2>
                    <p class="peserta-course-detail__hero-meta mb-0">{{ __('Tanya pengajar atau peserta lain langsung dari ruang diskusi kursus ini.') }}</p>
                </div>
                <a href="{{ route('peserta.kursus.show', $course) }}" class="btn btn-sm btn-light btn-wave peserta-course-detail__back">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card custom-card border-0 shadow-sm h-100">
                    <div class="card-header border-bottom-0 pb-0">
                        <span class="card-title mb-0">{{ __('Mulai diskusi baru') }}</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('peserta.kursus.forum.store', $course) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="forumThreadTitle">{{ __('Judul diskusi') }}</label>
                                <input type="text" name="title" id="forumThreadTitle"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title') }}" maxlength="255"
                                    placeholder="{{ __('Contoh: Tanya tugas minggu ini') }}">
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="forumThreadBody">{{ __('Isi diskusi') }}</label>
                                <textarea name="thread_body" id="forumThreadBody" rows="6"
                                    class="form-control @error('thread_body') is-invalid @enderror"
                                    placeholder="{{ __('Tulis pertanyaan, ide, atau kendala Anda...') }}">{{ old('thread_body') }}</textarea>
                                @error('thread_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-wave w-100">
                                <i class="bi bi-send me-1"></i>{{ __('Publikasikan diskusi') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="d-grid gap-3">
                    @forelse ($threads as $thread)
                        @php $authorIsAdmin = $thread->user?->hasRole(\App\Support\Roles::SUPER_ADMIN); @endphp
                        <div class="card custom-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $thread->user->avatar }}" alt="{{ $thread->user->name }}"
                                            class="rounded-circle object-fit-cover" width="48" height="48">
                                        <div>
                                            <h3 class="fs-16 mb-1">{{ $thread->title }}</h3>
                                            <div class="text-muted fs-12 d-flex align-items-center flex-wrap gap-1">
                                                <span>{{ $thread->user->name }}</span>
                                                @if ($authorIsAdmin)
                                                    <span class="badge bg-primary-transparent">{{ __('Admin') }}</span>
                                                @endif
                                                <span>· {{ $thread->created_at?->translatedFormat('d M Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark">{{ $thread->replies->count() }} {{ __('balasan') }}</span>
                                </div>

                                <div class="mt-3 fs-14">{{ $thread->body }}</div>

                                @if ($thread->replies->isNotEmpty())
                                    <div class="mt-4">
                                        <div class="fw-semibold fs-13 mb-2">{{ __('Balasan') }}</div>
                                        @foreach ($thread->replies as $reply)
                                            @php $replyIsAdmin = $reply->user?->hasRole(\App\Support\Roles::SUPER_ADMIN); @endphp
                                            <div class="peserta-course-detail__forum-reply {{ $replyIsAdmin ? 'is-admin' : '' }}">
                                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                                    <img src="{{ $reply->user->avatar }}" alt="{{ $reply->user->name }}"
                                                        class="rounded-circle object-fit-cover" width="32" height="32">
                                                    <strong class="fs-13">{{ $reply->user->name }}</strong>
                                                    @if ($replyIsAdmin)
                                                        <span class="badge bg-primary-transparent">{{ __('Admin') }}</span>
                                                    @endif
                                                    <span class="text-muted fs-12">{{ $reply->created_at?->translatedFormat('d M Y H:i') }}</span>
                                                </div>
                                                <div class="fs-13">{{ $reply->body }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('peserta.kursus.forum.reply', [$course, $thread]) }}" class="mt-4">
                                    @csrf
                                    <label class="form-label" for="reply-{{ $thread->id }}">{{ __('Tulis balasan') }}</label>
                                    <textarea name="reply_body" id="reply-{{ $thread->id }}" rows="3" class="form-control @error('reply_body') is-invalid @enderror"
                                        placeholder="{{ __('Balas diskusi ini...') }}"></textarea>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-outline-primary btn-wave">
                                            <i class="bi bi-reply me-1"></i>{{ __('Kirim balasan') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="card custom-card border-0 shadow-sm">
                            <div class="card-body text-center py-5 text-muted">
                                <i class="bi bi-chat-square-heart d-block fs-1 opacity-50 mb-3"></i>
                                <div class="fw-semibold mb-1">{{ __('Belum ada diskusi.') }}</div>
                                <div>{{ __('Jadilah yang pertama memulai percakapan di forum kursus ini.') }}</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
