@php
    $placeholder = asset((string) config('mooc.course_placeholder', 'images/course-no-image.png'));
@endphp
<div class="item">
    <div class="category-one__item">
        <div class="category-one__wrapper" style="background-image: url({{ asset('landing-page') }}/assets/images/shapes/category-shape.png);">
            <div class="category-one__thumb">
                <img src="{{ $placeholder }}" alt="{{ $category->kategori }}" loading="lazy" />
            </div>
            <div class="category-one__content">
                <div class="category-one__icon">
                    <span class="icon-education"></span>
                </div>
                <h3 class="category-one__title">{{ $category->kategori }}</h3>
                <p class="category-one__text">{{ number_format($category->courses_count) }} Kursus</p>
            </div>
        </div>
        <div class="category-one__hover">
            <div class="category-one__hover__thumb">
                <img src="{{ $placeholder }}" alt="{{ $category->kategori }}" loading="lazy" />
            </div>
            <div class="category-one__hover__content">
                <div class="category-one__hover__icon">
                    <span class="icon-education"></span>
                </div>
                <h3 class="category-one__hover__title">
                    <a href="#pelatihan">{{ $category->kategori }}</a>
                </h3>
                <p class="category-one__hover__text">{{ number_format($category->courses_count) }} Kursus</p>
            </div>
        </div>
    </div>
</div>
