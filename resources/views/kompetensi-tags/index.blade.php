@extends('layouts.app')

@section('title', __('Tag Kompetensi'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Tag Kompetensi') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Tag Kompetensi') }}</li>
            </ol>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <form method="POST" action="{{ route('kompetensi-tags.refresh') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-wave">
                    <i class="bi bi-arrow-repeat me-1"></i>{{ __('Muat ulang dari TNA') }}
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="card-title mb-0">{{ __('Tag Kompetensi') }}</div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="badge bg-primary-transparent">{{ $tags->count() }} {{ __('kompetensi') }}</span>
                        <span class="badge bg-success-transparent">{{ $usedCount }} {{ __('dipakai kursus') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <x-alert />

                    <div class="alert alert-primary-transparent d-flex align-items-start gap-2 mb-3" role="alert">
                        <i class="bi bi-info-circle mt-1"></i>
                        <div>
                            {{ __('Tag kompetensi diambil dari Kamus Kompetensi Aplikasi TNA dan hanya dapat diubah di aplikasi tersebut. Pilih tag di form kursus.') }}
                        </div>
                    </div>

                    @if (! $isAvailable)
                        <div class="alert alert-warning mb-0" role="alert">
                            {{ __('Data kompetensi dari Aplikasi TNA belum dapat dimuat. Periksa koneksi atau TNA_API_URL / TNA_API_KEY.') }}
                        </div>
                    @else
                        <div class="table-responsive">
                            <table id="kompetensi-tags-table" class="table table-striped table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 60px;">#</th>
                                        <th>{{ __('Nama Kompetensi') }}</th>
                                        <th class="text-center" style="width: 140px;">{{ __('Jumlah Kursus') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tags as $tag)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $tag['name'] }}</td>
                                            <td class="text-center" data-order="{{ $tag['courses_count'] }}">
                                                @if ($tag['courses_count'] > 0)
                                                    <span class="badge bg-success-transparent">{{ $tag['courses_count'] }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function() {
            if (!$('#kompetensi-tags-table').length) {
                return;
            }

            $('#kompetensi-tags-table').DataTable({
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                order: [
                    [0, 'asc']
                ],
                language: (document.documentElement.getAttribute('lang') || '').indexOf('en') === 0
                    ? {}
                    : { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
            });
        });
    </script>
@endpush
