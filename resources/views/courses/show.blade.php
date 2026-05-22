@extends('layouts.app')

@section('title', $course->judul)

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ $course->judul }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">{{ __('Kursus') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $course->kode }}</li>
            </ol>
        </div>
        @can('course delete')
            <form action="{{ route('courses.destroy', $course) }}" method="post" class="d-inline">
                @csrf
                @method('delete')
                <button type="button" class="btn btn-sm btn-danger-light btn-wave js-delete-confirm">
                    <i class="ri-delete-bin-line me-1"></i>{{ __('Hapus kursus') }}
                </button>
            </form>
        @endcan
    </div>

    <x-alert />

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info" type="button">
                {{ __('Informasi kursus') }}
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-modules" type="button">
                {{ __('Modul & materi') }}
                <span class="badge bg-primary-transparent ms-1">{{ $course->modules->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-peserta" type="button">
                {{ __('Peserta terdaftar') }}
                <span class="badge bg-primary-transparent ms-1">{{ $course->enrollments->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-info">
            @can('course edit')
                <div class="card custom-card">
                    <div class="card-header"><span class="card-title mb-0">{{ __('Edit kursus') }}</span></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('courses.update', $course) }}">
                            @csrf
                            @method('PUT')
                            @include('courses.partials.form-fields', ['course' => $course])
                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-primary btn-wave">{{ __('Simpan perubahan') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card custom-card">
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ __('Kode') }}:</strong> {{ $course->kode }}</p>
                        <p class="mb-1"><strong>{{ __('Instruktur') }}:</strong> {{ $course->instruktur }}</p>
                        <p class="mb-0"><strong>{{ __('Deskripsi') }}:</strong> {{ $course->deskripsi }}</p>
                    </div>
                </div>
            @endcan
        </div>

        <div class="tab-pane fade" id="tab-modules">
            <div class="card custom-card">
                <div class="card-header">
                    <span class="card-title mb-0">{{ __('Struktur modul') }}</span>
                    <span class="text-muted fs-12">{{ __('Konten diisi via seeder / migrasi konten.') }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width:4rem">#</th>
                                    <th>{{ __('Judul modul') }}</th>
                                    <th>{{ __('Materi') }}</th>
                                    <th>{{ __('Durasi') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($course->modules as $module)
                                    <tr>
                                        <td>{{ $module->urutan }}</td>
                                        <td>
                                            <span class="fw-medium">{{ $module->judul }}</span>
                                            @if ($module->deskripsi)
                                                <br><small class="text-muted">{{ $module->deskripsi }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $module->lessons_count }}</td>
                                        <td>{{ $module->durasi_menit }} {{ __('menit') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            {{ __('Belum ada modul. Jalankan CourseContentSeeder.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-peserta">
            <div class="row g-3">
                @can('course enrollment manage')
                    <div class="col-lg-4">
                        <div class="card custom-card">
                            <div class="card-header"><span class="card-title mb-0">{{ __('Daftarkan peserta') }}</span></div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('courses.enrollments.store', $course) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Peserta') }}</label>
                                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                            <option value="">{{ __('Pilih peserta...') }}</option>
                                            @foreach ($pesertaUsers as $user)
                                                @if (! $enrolledUserIds->contains($user->id))
                                                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                                        {{ $user->name }} ({{ $user->email }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-wave w-100">{{ __('Daftarkan') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endcan
                <div class="@can('course enrollment manage') col-lg-8 @else col-12 @endcan">
                    <div class="card custom-card">
                        <div class="card-header"><span class="card-title mb-0">{{ __('Daftar peserta') }}</span></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Nama') }}</th>
                                            <th>{{ __('Surel') }}</th>
                                            <th>{{ __('Progres') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Modul') }}</th>
                                            @can('course enrollment manage')
                                                <th class="text-center">{{ __('Aksi') }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($course->enrollments as $enrollment)
                                            <tr>
                                                <td>{{ $enrollment->user->name }}</td>
                                                <td>{{ $enrollment->user->email }}</td>
                                                <td>{{ $enrollment->progress }}%</td>
                                                <td>{{ $enrollment->status }}</td>
                                                <td>{{ $enrollment->modul_selesai }} / {{ $course->modul_total }}</td>
                                                @can('course enrollment manage')
                                                    <td class="text-center">
                                                        <form action="{{ route('courses.enrollments.destroy', [$course, $enrollment]) }}"
                                                            method="post" class="d-inline">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="button" class="btn btn-sm btn-danger-light btn-wave js-delete-confirm"
                                                                title="{{ __('Cabut pendaftaran') }}">
                                                                <i class="ri-user-unfollow-line"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                @endcan
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    {{ __('Belum ada peserta terdaftar.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
