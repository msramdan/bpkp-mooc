@extends('layouts.app')

@section('title', $course->judul)

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-kursus-detail.css" rel="stylesheet">
@endpush

@section('content')
    @include('peserta.partials.page-header', [
        'title' => $course->judul,
        'parent' => __('Kursus Saya'),
        'parentRoute' => 'peserta.kursus.index',
    ])

    <div class="peserta-course-detail" data-peserta-course-detail>
        {{-- Hero ringkas --}}
        <div class="peserta-course-detail__hero card custom-card">
            <div class="peserta-course-detail__hero-inner">
                <img src="{{ $course->thumbnail }}" alt="" class="peserta-course-detail__hero-thumb"
                    onerror="this.src='https://placehold.co/120x68/2b478b/ffffff?text={{ urlencode($course->kode) }}'">
                <div class="peserta-course-detail__hero-main">
                    <div class="peserta-course-detail__hero-badges">
                        <span class="badge bg-light text-dark">{{ $course->kode }}</span>
                        <span class="badge bg-primary-transparent">{{ $course->kategori }}</span>
                        @include('peserta.partials.status-badge', ['status' => $enrollment->status])
                    </div>
                    <h2 class="peserta-course-detail__hero-title">{{ $course->judul }}</h2>
                    <p class="peserta-course-detail__hero-meta mb-0">
                        <i class="bi bi-person"></i> {{ $course->instruktur }}
                        <span class="mx-2">·</span>
                        <i class="bi bi-layers"></i> {{ $moduleCount }} {{ __('modul') }}
                        <span class="mx-2">·</span>
                        <i class="bi bi-collection-play"></i> {{ $totalLessons }} {{ __('materi') }}
                    </p>
                </div>
                <div class="peserta-course-detail__hero-progress">
                    <span class="peserta-course-detail__hero-pct">{{ $enrollment->progress }}%</span>
                    <div class="progress peserta-course-detail__hero-bar">
                        <div class="progress-bar" style="width: {{ $enrollment->progress }}%"></div>
                    </div>
                    <small class="text-muted">{{ __('Modul') }} {{ $enrollment->modulLabel() }}</small>
                </div>
                <a href="{{ route('peserta.kursus.index') }}" class="btn btn-sm btn-light btn-wave peserta-course-detail__back">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>

        {{-- Layout: sidebar modul + panel materi --}}
        <div class="peserta-course-detail__layout card custom-card">
            <aside class="peserta-course-detail__sidebar">
                <div class="peserta-course-detail__sidebar-head">
                    <span class="fw-semibold fs-13">{{ __('Daftar modul') }}</span>
                    <span class="badge bg-primary-transparent">{{ $completedModules }}/{{ $moduleCount }}</span>
                </div>
                <div class="peserta-course-detail__search">
                    <i class="bi bi-search"></i>
                    <input type="search" class="form-control form-control-sm" data-module-search
                        placeholder="{{ __('Cari modul...') }}" autocomplete="off">
                </div>
                <nav class="peserta-course-detail__nav" data-module-nav aria-label="{{ __('Navigasi modul') }}">
                    @foreach ($course->modules as $module)
                        @php
                            $moduleDone = $module->urutan <= $completedModules;
                            $moduleCurrent = $module->id === $activeModule?->id;
                            $navState = $moduleDone ? 'done' : ($moduleCurrent ? 'current' : 'locked');
                        @endphp
                        <button type="button"
                            class="peserta-course-detail__nav-item {{ $moduleCurrent ? 'is-active' : '' }}"
                            data-module-nav data-module-id="{{ $module->id }}"
                            data-search-text="{{ strtolower($module->urutan.' '.$module->judul) }}"
                            data-state="{{ $navState }}">
                            <span class="peserta-course-detail__nav-num">{{ $module->urutan }}</span>
                            <span class="peserta-course-detail__nav-text">
                                <span class="peserta-course-detail__nav-title">{{ $module->judul }}</span>
                                <span class="peserta-course-detail__nav-meta">
                                    {{ $module->lessons->count() }} {{ __('materi') }} · {{ $module->durasi_menit }} {{ __('menit') }}
                                </span>
                            </span>
                            <span class="peserta-course-detail__nav-icon">
                                @if ($moduleDone)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif ($moduleCurrent)
                                    <i class="bi bi-play-circle-fill text-primary"></i>
                                @else
                                    <i class="bi bi-lock text-muted"></i>
                                @endif
                            </span>
                        </button>
                    @endforeach
                </nav>
                <p class="peserta-course-detail__nav-empty d-none mb-0" data-module-empty>
                    {{ __('Tidak ada modul yang cocok.') }}
                </p>
            </aside>

            <main class="peserta-course-detail__main">
                @foreach ($course->modules as $module)
                    @php
                        $moduleDone = $module->urutan <= $completedModules;
                        $moduleCurrent = $module->urutan === $completedModules + 1;
                        $isActivePanel = $module->id === $activeModule?->id;
                    @endphp
                    <section class="peserta-course-detail__panel {{ $isActivePanel ? 'is-visible' : '' }}"
                        data-module-panel data-module-id="{{ $module->id }}">
                        <header class="peserta-course-detail__panel-head">
                            <div>
                                <span class="peserta-course-detail__panel-label">{{ __('Modul') }} {{ $module->urutan }}</span>
                                <h3 class="peserta-course-detail__panel-title mb-1">{{ $module->judul }}</h3>
                                @if ($module->deskripsi)
                                    <p class="peserta-course-detail__panel-desc mb-0">{{ $module->deskripsi }}</p>
                                @endif
                            </div>
                            <div class="peserta-course-detail__panel-badges">
                                @if ($moduleDone)
                                    <span class="badge bg-success-transparent"><i class="bi bi-check-lg me-1"></i>{{ __('Selesai') }}</span>
                                @elseif ($moduleCurrent)
                                    <span class="badge bg-primary-transparent">{{ __('Sedang dipelajari') }}</span>
                                @else
                                    <span class="badge bg-secondary-transparent"><i class="bi bi-lock me-1"></i>{{ __('Terkunci') }}</span>
                                @endif
                                <span class="text-muted fs-12">{{ $module->lessons->count() }} {{ __('materi') }}</span>
                            </div>
                        </header>

                        <div class="peserta-course-detail__lessons">
                            @foreach ($module->lessons as $lesson)
                                @php
                                    $lessonDone = $moduleDone;
                                    $lessonCurrent = $moduleCurrent && $loop->first;
                                    $lessonLocked = ! $moduleDone && ! $lessonCurrent;
                                @endphp
                                <div class="peserta-course-detail__lesson {{ $lessonLocked ? 'is-locked' : '' }} {{ $lessonCurrent ? 'is-current' : '' }}">
                                    <span class="peserta-course-detail__lesson-order">{{ $lesson->urutan }}</span>
                                    <span class="peserta-course-detail__lesson-icon {{ $lesson->tipe }}">
                                        <i class="bi {{ $lesson->iconClass() }}"></i>
                                    </span>
                                    <div class="peserta-course-detail__lesson-body">
                                        <span class="peserta-course-detail__lesson-title">{{ $lesson->judul }}</span>
                                        <span class="peserta-course-detail__lesson-meta">
                                            <span class="peserta-course-detail__type">{{ $lesson->typeLabel() }}</span>
                                            <span>{{ $lesson->durasi_menit }} {{ __('menit') }}</span>
                                            @if ($lesson->is_preview)
                                                <span class="text-primary">· {{ __('Pratinjau') }}</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="peserta-course-detail__lesson-action">
                                        @if ($lessonDone)
                                            <span class="peserta-course-detail__lesson-btn is-done" title="{{ __('Selesai') }}">
                                                <i class="bi bi-check-lg"></i>
                                            </span>
                                        @elseif ($lessonCurrent)
                                            <button type="button" class="peserta-course-detail__lesson-btn is-play" disabled>
                                                <i class="bi bi-play-fill"></i> {{ __('Mulai') }}
                                            </button>
                                        @else
                                            <span class="peserta-course-detail__lesson-btn is-lock">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </main>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend') }}/assets/js/peserta-kursus-detail.js"></script>
@endpush
