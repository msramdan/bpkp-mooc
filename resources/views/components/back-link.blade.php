@props(['href'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn btn-outline-secondary btn-wave']) }}>
    <i class="ri-arrow-left-line align-middle me-1"></i>{{ __('Back') }}
</a>
