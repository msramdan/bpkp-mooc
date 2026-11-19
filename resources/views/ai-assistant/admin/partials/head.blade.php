{{-- Header halaman + tab navigasi AI Assistant (Super Admin) --}}
@push('css')
    <link href="{{ asset('backend') }}/assets/css/ai-assistant-admin.css?v={{ @filemtime(public_path('backend/assets/css/ai-assistant-admin.css')) ?: time() }}" rel="stylesheet">
@endpush

<div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title fw-medium fs-18 mb-2">{{ $title }}</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item">{{ __('AI Assistant') }}</li>
            <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
        </ol>
    </div>
</div>

<nav class="aiad-tabs" aria-label="{{ __('Menu AI Assistant') }}">
    @can('ai assistant monitor view')
        <a href="{{ route('ai-assistant-logs.index') }}" class="{{ $active === 'dashboard' ? 'is-active' : '' }}"><i class="ri-dashboard-3-line"></i>{{ __('Statistik') }}</a>
        <a href="{{ route('ai-assistant-logs.users') }}" class="{{ $active === 'users' ? 'is-active' : '' }}"><i class="ri-group-line"></i>{{ __('Aktivitas User') }}</a>
        <a href="{{ route('ai-assistant-logs.requests') }}" class="{{ $active === 'requests' ? 'is-active' : '' }}"><i class="ri-file-list-3-line"></i>{{ __('Log & Penolakan') }}</a>
        <a href="{{ route('ai-assistant-logs.audit') }}" class="{{ $active === 'audit' ? 'is-active' : '' }}"><i class="ri-shield-user-line"></i>{{ __('Audit Admin') }}</a>
    @endcan
    @can('ai assistant setting manage')
        <a href="{{ route('ai-assistant-settings.index') }}" class="{{ $active === 'settings' ? 'is-active' : '' }}"><i class="ri-settings-4-line"></i>{{ __('Settings') }}</a>
    @endcan
</nav>
