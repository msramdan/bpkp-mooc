@extends('layouts.app')

@section('title', __('Forum').' - '.$course->judul)

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-kursus-detail.css?v={{ @filemtime(public_path('backend/assets/css/peserta-kursus-detail.css')) ?: time() }}" rel="stylesheet">
    <link href="{{ asset('backend') }}/assets/css/peserta-forum.css?v={{ @filemtime(public_path('backend/assets/css/peserta-forum.css')) ?: time() }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $authUser = auth()->user();
        $openCreateModal = $errors->has('title') || $errors->has('thread_body');
    @endphp

    @include('peserta.partials.page-header', [
        'title' => __('Forum kursus'),
        'parent' => $course->judul,
        'parentUrl' => route('peserta.kursus.show', $course),
    ])

    <x-alert />

    <div class="pf pf--simple">
        <div class="pf-topbar card custom-card">
            <div class="pf-topbar__inner">
                <x-course-thumbnail :course="$course" class="pf-topbar__thumb" />
                <div class="pf-topbar__copy min-w-0">
                    <div class="pf-topbar__chips">
                        <span class="badge bg-primary-transparent">{{ __('Forum aktif') }}</span>
                        <span class="badge bg-light text-dark">{{ $threads->count() }} {{ __('diskusi') }}</span>
                    </div>
                    <h2 class="pf-topbar__title">{{ $course->judul }}</h2>
                    <p class="pf-topbar__sub mb-0">{{ __('Tanya pengajar atau peserta lain langsung dari ruang diskusi kursus ini.') }}</p>
                </div>
            </div>
        </div>

        <button type="button" class="pf-launch" data-bs-toggle="modal" data-bs-target="#forumCreateModal">
            <img src="{{ $authUser?->avatar }}" alt="{{ $authUser?->name }}" class="pf-avatar pf-avatar--md">
            <span class="pf-launch__placeholder">{{ __('Mau mulai diskusi baru? Ketuk di sini...') }}</span>
            <span class="pf-launch__icon"><i class="bi bi-pencil-square"></i></span>
        </button>

        <div class="pf-feed-meta">
            <h2 class="pf-feed-meta__title">{{ __('Timeline diskusi') }}</h2>
            <span class="pf-feed-meta__count" data-forum-count>{{ $threads->count() }}</span>
        </div>

        <div class="pf-feed" data-forum-feed>
            @forelse ($threads as $thread)
                @php
                    $authorIsAdmin = $thread->user?->hasRole(\App\Support\Roles::SUPER_ADMIN);
                    $mentionables = $thread->mentionableParticipants();
                @endphp
                <article class="pf-post {{ $authorIsAdmin ? 'is-admin' : '' }}" data-thread-id="{{ $thread->id }}">
                    <header class="pf-post__head">
                        <img src="{{ $thread->user->avatar }}" alt="{{ $thread->user->name }}" class="pf-avatar pf-avatar--lg">
                        <div class="pf-post__who">
                            <div class="pf-post__name-row">
                                <strong class="pf-post__name">{{ $thread->user->name }}</strong>
                                @if ($authorIsAdmin)
                                    <span class="pf-badge">{{ __('Admin') }}</span>
                                @endif
                            </div>
                            <time class="pf-post__time">{{ $thread->created_at?->translatedFormat('d M Y · H:i') }}</time>
                        </div>
                        <span class="pf-post__stat">
                            <i class="bi bi-chat-left-text"></i>
                            <span data-reply-count>{{ $thread->replies->count() }}</span>
                        </span>
                    </header>

                    <div class="pf-post__toolbar">
                        <h3 class="pf-post__title">{{ $thread->title }}</h3>
                        @if ((string) $thread->user_id === (string) $authUser?->id)
                            <button type="button"
                                class="pf-link-btn js-thread-edit"
                                data-title="{{ $thread->title }}"
                                data-body="{{ $thread->body }}"
                                data-update-url="{{ route('peserta.kursus.forum.update', [$course, $thread]) }}">
                                <i class="bi bi-pencil-square me-1"></i>{{ __('Edit') }}
                            </button>
                        @endif
                    </div>
                    <p class="pf-post__body" data-thread-body>{!! \App\Models\CourseForumThread::renderBodyWithMentions($thread->body, $mentionables) !!}</p>

                    @if ($thread->replies->isNotEmpty())
                        <div class="pf-replies">
                            @foreach ($thread->replies as $reply)
                                @php $replyIsAdmin = $reply->user?->hasRole(\App\Support\Roles::SUPER_ADMIN); @endphp
                                <div class="pf-reply {{ $replyIsAdmin ? 'is-admin' : '' }}">
                                    <img src="{{ $reply->user->avatar }}" alt="{{ $reply->user->name }}" class="pf-avatar pf-avatar--sm">
                                    <div class="pf-reply__bubble">
                                        <div class="pf-reply__meta">
                                            <strong>{{ $reply->user->name }}</strong>
                                            @if ($replyIsAdmin)
                                                <span class="pf-badge pf-badge--sm">{{ __('Admin') }}</span>
                                            @endif
                                            <time>{{ $reply->created_at?->translatedFormat('d M Y H:i') }}</time>
                                            @if ((string) $reply->user_id === (string) $authUser?->id)
                                                <button type="button"
                                                    class="pf-link-btn js-reply-edit"
                                                    data-body="{{ $reply->body }}"
                                                    data-update-url="{{ route('peserta.kursus.forum.reply.update', [$course, $thread, $reply]) }}">
                                                    {{ __('Edit') }}
                                                </button>
                                            @endif
                                        </div>
                                        <div class="pf-reply__text" data-reply-body>{!! \App\Models\CourseForumThread::renderBodyWithMentions($reply->body, $mentionables) !!}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('peserta.kursus.forum.reply', [$course, $thread]) }}" class="pf-reply-box js-forum-reply-form">
                        @csrf
                        <img src="{{ $authUser?->avatar }}" alt="{{ $authUser?->name }}" class="pf-avatar pf-avatar--sm">
                        <div class="pf-reply-box__fields">
                            <label class="visually-hidden" for="reply-{{ $thread->id }}">{{ __('Tulis balasan') }}</label>
                            <textarea name="reply_body" id="reply-{{ $thread->id }}" rows="2"
                                class="pf-textarea pf-textarea--compact"
                                data-mentionables='@json($mentionables)'
                                placeholder="{{ __('Tulis balasan... ketik @ untuk tag nama') }}"></textarea>
                            @if ($mentionables->isNotEmpty())
                                <div class="pf-mention-hint">{{ __('Tag hanya untuk nama yang sudah berkomentar di topik ini.') }}</div>
                            @endif
                            <button type="submit" class="pf-btn pf-btn--ghost">
                                <i class="bi bi-reply-fill"></i>
                                <span>{{ __('Balas') }}</span>
                            </button>
                        </div>
                    </form>
                </article>
            @empty
                <div class="pf-empty" data-forum-empty>
                    <div class="pf-empty__icon"><i class="bi bi-chat-heart"></i></div>
                    <h3>{{ __('Belum ada diskusi.') }}</h3>
                    <p>{{ __('Jadilah yang pertama memulai percakapan di forum kursus ini.') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="forumCreateModal" tabindex="-1" aria-labelledby="forumCreateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content pf-modal">
                <form method="POST" action="{{ route('peserta.kursus.forum.store', $course) }}" class="js-forum-create-form">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold" id="forumCreateModalLabel">{{ __('Mulai diskusi baru') }}</h5>
                            <p class="text-muted fs-13 mb-0">{{ __('Bagikan pertanyaan atau ide singkat agar mudah dibalas.') }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Tutup') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="forumThreadTitle">{{ __('Judul diskusi') }}</label>
                            <input type="text" name="title" id="forumThreadTitle"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}" maxlength="255"
                                placeholder="{{ __('Judul singkat, contoh: Tanya tugas minggu ini') }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="forumThreadBody">{{ __('Isi diskusi') }}</label>
                            <textarea name="thread_body" id="forumThreadBody" rows="5"
                                class="form-control @error('thread_body') is-invalid @enderror"
                                placeholder="{{ __('Ceritakan detailnya di sini...') }}" required>{{ old('thread_body') }}</textarea>
                            @error('thread_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                        <button type="submit" class="btn btn-primary btn-wave">
                            <i class="bi bi-send-fill me-1"></i>{{ __('Publikasikan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="forumEditModal" tabindex="-1" aria-labelledby="forumEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content pf-modal">
                <form method="POST" class="js-forum-edit-form" data-mode="thread">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold" id="forumEditModalLabel">{{ __('Edit diskusi') }}</h5>
                            <p class="text-muted fs-13 mb-0">{{ __('Hanya komentar milik Anda sendiri yang bisa diubah.') }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Tutup') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 js-edit-thread-title">
                            <label class="form-label" for="forumEditTitle">{{ __('Judul diskusi') }}</label>
                            <input type="text" name="title" id="forumEditTitle" class="form-control" maxlength="255">
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="forumEditBody">{{ __('Isi diskusi') }}</label>
                            <textarea name="thread_body" id="forumEditBody" rows="5" class="form-control"></textarea>
                        <label class="visually-hidden" for="forumEditReplyBody">{{ __('Balasan') }}</label>
                            <textarea name="reply_body" id="forumEditReplyBody" rows="5" class="form-control d-none"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                        <button type="submit" class="btn btn-primary btn-wave">
                            <i class="bi bi-save me-1"></i>{{ __('Simpan perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.pfForumAuth = {
            name: @json($authUser?->name),
            avatar: @json($authUser?->avatar),
        };
        window.pfForumI18n = {
            admin: @json(__('Admin')),
            reply: @json(__('Balas')),
            replyPlaceholder: @json(__('Tulis balasan... ketik @ untuk tag nama')),
            mentionHint: @json(__('Tag hanya untuk nama yang sudah berkomentar di topik ini.')),
            replyOk: @json(__('Balasan berhasil dikirim.')),
            threadOk: @json(__('Diskusi berhasil dibuat.')),
            replyRequired: @json(__('Silakan isi balasan.')),
            threadUpdated: @json(__('Diskusi berhasil diperbarui.')),
            replyUpdated: @json(__('Balasan berhasil diperbarui.')),
            edit: @json(__('Edit')),
            error: @json(__('Terjadi kesalahan. Coba lagi.')),
        };
    </script>
    <script src="{{ asset('backend/assets/js/peserta-forum-mention.js') }}?v={{ @filemtime(public_path('backend/assets/js/peserta-forum-mention.js')) ?: time() }}"></script>
    <script src="{{ asset('backend/assets/js/peserta-forum-live.js') }}?v={{ @filemtime(public_path('backend/assets/js/peserta-forum-live.js')) ?: time() }}"></script>
    @if ($openCreateModal)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalEl = document.getElementById('forumCreateModal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            });
        </script>
    @endif
@endpush
