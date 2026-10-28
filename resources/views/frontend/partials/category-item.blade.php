@php
    // Foto dokumentasi pelatihan Pusdiklatwas BPKP, diputar per kategori (4 variasi).
    $imageNumber = (($loop->index ?? 0) % 4) + 1;
    $categoryImage = asset('landing-page')."/assets/images/category/bpkp-category-hover-{$imageNumber}.jpg";
@endphp
<div class="item">
    <a href="#pelatihan" class="landing-category-card">
        <img src="{{ $categoryImage }}" alt="{{ $category->kategori }}" class="landing-category-card__img" loading="lazy" />
        <span class="landing-category-card__icon"><span class="icon-education"></span></span>
        <span class="landing-category-card__content">
            <span class="landing-category-card__title">{{ $category->kategori }}</span>
            <span class="landing-category-card__meta">
                {{ number_format($category->courses_count) }} {{ __('Kursus') }}
                <i class="icon-arrow"></i>
            </span>
        </span>
    </a>
</div>
