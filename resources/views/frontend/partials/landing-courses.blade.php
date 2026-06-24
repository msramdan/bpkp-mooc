<section id="pelatihan" class="course-one" style="background-image: url({{ asset('landing-page') }}/assets/images/shapes/course-bg-1.png);">
    <div class="container">
        <div class="section-title text-center">
            <h5 class="section-title__tagline">
                Pelatihan Yang Sedang Berlangsung
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 133 13" fill="none">
                    <path d="M9.76794 0.395L0.391789 9.72833C-0.130596 10.2483 -0.130596 11.095 0.391789 11.615C0.914174 12.135 1.76472 12.135 2.28711 11.615L11.6633 2.28167C12.1856 1.76167 12.1856 0.915 11.6633 0.395C11.1342 -0.131667 10.2903 -0.131667 9.76794 0.395Z" fill="#F1F2FD" />
                </svg>
            </h5>
            <h2 class="section-title__title">Pelatihan Unggulan</h2>
        </div>
        <div class="row align-items-stretch">
            @forelse ($featuredCourses ?? [] as $course)
                @include('frontend.partials.course-card', ['course' => $course, 'loop' => $loop])
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted mb-0">Belum ada pelatihan yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
