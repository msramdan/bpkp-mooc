@extends('layouts.app')

@section('title', __('Backup Database'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Backup Database') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Backup Database') }}</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card custom-card backup-card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4 p-lg-5">
                    <x-alert />

                    <div class="backup-hero mb-4">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                            <div>
                                <span class="badge bg-primary-transparent text-primary mb-2">
                                    <i class="ri-shield-check-line me-1"></i>{{ __('Utilities') }}
                                </span>
                                <h4 class="mb-2 fw-semibold">{{ __('Download database backup') }}</h4>
                                <p class="text-muted mb-0">{{ __('Backup will be generated in SQL format and downloaded directly to your computer.') }}</p>
                            </div>
                            <span class="avatar avatar-lg bg-primary-transparent text-primary">
                                <i class="ri-database-2-line fs-24"></i>
                            </span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="backup-metric-card h-100">
                                <div class="backup-metric-label">{{ __('Format') }}</div>
                                <div class="backup-metric-value">SQL (.sql)</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="backup-metric-card h-100">
                                <div class="backup-metric-label">{{ __('Generated at') }}</div>
                                <div class="backup-metric-value">{{ now()->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="backup-metric-card h-100">
                                <div class="backup-metric-label">{{ __('Status') }}</div>
                                <div class="backup-metric-value text-success">
                                    <i class="ri-checkbox-circle-line me-1"></i>{{ __('Ready') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="backup-action-panel">
                        <div class="small text-muted mb-3">
                            {{ __('Click the button below to download the latest database backup file.') }}
                        </div>
                        <div class="d-grid d-md-flex">
                            @can('database backup download')
                                <form action="{{ route('database-backups.download') }}" method="POST" class="d-grid d-md-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-wave px-4">
                                        <i class="ri-download-2-line align-middle me-1"></i>{{ __('Download backup now') }}
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-warning mb-0 py-2">
                                    {{ __('You do not have permission to download database backup.') }}
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .backup-card {
            border-radius: 0.9rem;
        }

        .backup-hero {
            border: 1px solid rgba(var(--primary-rgb), 0.14);
            border-radius: 0.8rem;
            padding: 1rem 1rem;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.09) 0%, rgba(var(--primary-rgb), 0.02) 100%);
        }

        .backup-metric-card {
            border: 1px solid var(--default-border);
            border-radius: 0.75rem;
            padding: 0.9rem 1rem;
            background-color: var(--custom-white);
        }

        .backup-metric-label {
            color: var(--text-muted);
            font-size: 0.73rem;
            margin-bottom: 0.35rem;
        }

        .backup-metric-value {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .backup-action-panel {
            border: 1px dashed rgba(var(--primary-rgb), 0.3);
            border-radius: 0.75rem;
            padding: 1rem;
            background-color: rgba(var(--primary-rgb), 0.02);
        }
    </style>
@endpush
