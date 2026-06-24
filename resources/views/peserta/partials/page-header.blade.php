@props(['title', 'parent' => __('Beranda'), 'parentRoute' => 'peserta.dashboard', 'parentUrl' => null])

<div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title fw-medium fs-18 mb-2">{{ $title }}</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ $parentUrl ?? route($parentRoute) }}">{{ $parent }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
        </ol>
    </div>
    @isset($actions)
        <div>{{ $actions }}</div>
    @endisset
</div>
