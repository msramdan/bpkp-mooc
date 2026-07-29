<div class="row g-3 course-forum-admin">
    <div class="col-12">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="fw-semibold fs-16">{{ __('Forum diskusi kursus') }}</div>
                    <div class="text-muted fs-13">
                        {{ $course->is_forum_open
                            ? __('Forum sedang dibuka. Admin dan peserta bisa saling bertanya dan membalas di sini.')
                            : __('Forum sedang ditutup. Aktifkan dari pengaturan kursus jika ingin membuka diskusi untuk peserta.') }}
                    </div>
                </div>
                <span class="badge {{ $course->is_forum_open ? 'bg-success-transparent text-success' : 'bg-secondary-transparent text-muted' }} px-3 py-2">
                    <i class="bi {{ $course->is_forum_open ? 'bi-chat-dots-fill' : 'bi-lock-fill' }} me-1"></i>
                    {{ $course->is_forum_open ? __('Forum aktif') : __('Forum nonaktif') }}
                </span>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card custom-card border-0 shadow-sm h-100">
            <div class="card-header border-bottom-0 pb-0">
                <span class="card-title mb-0">{{ __('Mulai diskusi baru') }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('courses.forum.store', $course) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="adminForumTitle">{{ __('Judul diskusi') }}</label>
                        <input type="text" name="title" id="adminForumTitle"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}" maxlength="255"
                            placeholder="{{ __('Contoh: Pengumuman tugas / jawaban pertanyaan') }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="adminForumBody">{{ __('Isi diskusi') }}</label>
                        <textarea name="thread_body" id="adminForumBody" rows="5"
                            class="form-control @error('thread_body') is-invalid @enderror"
                            placeholder="{{ __('Tulis pengumuman atau mulai topik diskusi...') }}" required>{{ old('thread_body') }}</textarea>
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
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-header border-bottom-0 pb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="card-title mb-0">{{ __('Percakapan terbaru') }}</span>
                <span class="badge bg-primary-transparent">{{ $forumThreads->count() }} {{ __('thread') }}</span>
            </div>
            <div class="card-body">
                @forelse ($forumThreads as $thread)
                    @php $authorIsAdmin = $thread->user?->hasRole(\App\Support\Roles::SUPER_ADMIN); @endphp
                    <div class="course-forum-admin__thread border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <h5 class="mb-1 fs-15">{{ $thread->title }}</h5>
                                <div class="text-muted fs-12 d-flex align-items-center flex-wrap gap-1">
                                    <span class="fw-medium text-dark">{{ $thread->user->name }}</span>
                                    @if ($authorIsAdmin)
                                        <span class="badge bg-primary-transparent">{{ __('Admin') }}</span>
                                    @endif
                                    <span>· {{ $thread->created_at?->translatedFormat('d M Y H:i') }}</span>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark">{{ $thread->replies->count() }} {{ __('balasan') }}</span>
                        </div>
                        <p class="mb-0 mt-3">{{ $thread->body }}</p>

                        <div class="mt-3 pt-3 border-top">
                            @foreach ($thread->replies as $reply)
                                @php $replyIsAdmin = $reply->user?->hasRole(\App\Support\Roles::SUPER_ADMIN); @endphp
                                <div class="course-forum-admin__reply {{ $replyIsAdmin ? 'is-admin' : '' }} {{ $loop->last ? '' : 'mb-2' }}">
                                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <strong class="fs-13">{{ $reply->user->name }}</strong>
                                            @if ($replyIsAdmin)
                                                <span class="badge bg-primary-transparent">{{ __('Admin') }}</span>
                                            @endif
                                        </div>
                                        <span class="text-muted fs-12">{{ $reply->created_at?->translatedFormat('d M Y H:i') }}</span>
                                    </div>
                                    <div class="mt-2 fs-13">{{ $reply->body }}</div>
                                </div>
                            @endforeach

                            <form method="POST" action="{{ route('courses.forum.reply', [$course, $thread]) }}" class="mt-3">
                                @csrf
                                <label class="form-label fs-12" for="admin-reply-{{ $thread->id }}">{{ __('Tulis balasan') }}</label>
                                <textarea name="reply_body" id="admin-reply-{{ $thread->id }}" rows="2"
                                    class="form-control form-control-sm @error('reply_body') is-invalid @enderror"
                                    placeholder="{{ __('Balas diskusi ini sebagai admin...') }}" required></textarea>
                                @error('reply_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-sm btn-primary btn-wave">
                                        <i class="bi bi-reply me-1"></i>{{ __('Kirim balasan') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-chat-square-text d-block fs-1 opacity-50 mb-2"></i>
                        {{ __('Belum ada diskusi forum untuk kursus ini.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
.course-forum-admin__reply {
    background: #f8fafc;
    border: 1px solid rgba(15, 23, 42, 0.06);
    border-radius: 12px;
    padding: 0.85rem 0.95rem;
}
.course-forum-admin__reply.is-admin {
    background: rgba(var(--primary-rgb), 0.06);
    border-color: rgba(var(--primary-rgb), 0.18);
}
</style>
@endpush
