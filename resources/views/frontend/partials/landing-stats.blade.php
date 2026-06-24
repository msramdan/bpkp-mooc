<section class="landing-stats counter-one" style="background-image: linear-gradient(rgba(33, 75, 154, 0.92), rgba(33, 75, 154, 0.92)), url({{ asset('landing-page') }}/assets/images/shapes/counter-bg-1.jpg);">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 wow fadeInLeft" data-wow-delay="200ms">
                <div class="counter-one__left landing-stats__intro">
                    <h4 class="counter-one__left__title">Bergabung dengan Komunitas Belajar BPKP</h4>
                    <div class="counter-one__left__content landing-stats__intro-text">
                        Akses kursus daring, pantau progres pembelajaran, dan dapatkan sertifikat setelah menyelesaikan pelatihan.
                    </div>
                </div>
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="300ms">
                <div class="landing-stats__panel">
                    <div class="landing-stats__grid">
                        <div class="landing-stats__item">
                            <span class="landing-stats__value">{{ number_format($stats['courses'] ?? 0) }}</span>
                            <span class="landing-stats__label">Kursus Aktif</span>
                        </div>
                        <div class="landing-stats__item">
                            <span class="landing-stats__value">{{ number_format($stats['participants'] ?? 0) }}</span>
                            <span class="landing-stats__label">Peserta Terdaftar</span>
                        </div>
                        <div class="landing-stats__item">
                            <span class="landing-stats__value">{{ number_format($stats['enrollments'] ?? 0) }}</span>
                            <span class="landing-stats__label">Pendaftaran Kursus</span>
                        </div>
                        <div class="landing-stats__item">
                            <span class="landing-stats__value">{{ number_format($stats['certificates'] ?? 0) }}</span>
                            <span class="landing-stats__label">Sertifikat Terbit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
