@extends('layouts.app')

@section('title', __('Tugas'))

@section('content')
    @include('peserta.partials.page-header', ['title' => __('Tugas')])

    <div class="card custom-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Kursus') }}</th>
                            <th>{{ __('Judul tugas') }}</th>
                            <th>{{ __('Tipe') }}</th>
                            <th>{{ __('Bobot') }}</th>
                            <th>{{ __('Batas waktu') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td class="text-muted fs-13" style="max-width: 180px;">{{ $item['kursus'] }}</td>
                                <td class="fw-medium">{{ $item['judul'] }}</td>
                                <td>{{ $item['tipe'] }}</td>
                                <td>{{ $item['bobot'] }}</td>
                                <td>{{ $item['deadline'] }}</td>
                                <td>{{ $item['status'] }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary-light" disabled>{{ __('Kerjakan') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
