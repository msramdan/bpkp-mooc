@props([
    'course',
    'alt' => null,
])

@php
    $placeholder = asset((string) config('mooc.course_placeholder', 'images/course-no-image.png'));
@endphp

<img
    src="{{ $course->thumbnail_url }}"
    alt="{{ $alt ?? $course->judul }}"
    {{ $attributes->merge(['class' => 'course-thumbnail-img']) }}
    loading="lazy"
    onerror="this.onerror=null;this.src='{{ $placeholder }}';"
>
