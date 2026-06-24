<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MOOCs BPKP</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('landing-page')}}/assets/images/favicons/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('landing-page')}}/assets/images/favicons/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('landing-page')}}/assets/images/favicons/favicon-16x16.png" />
    <link rel="manifest" href="{{asset('landing-page')}}/assets/images/favicons/site.webmanifest" />
    <meta name="description" content="Eduact HTML Template For Educaton & LMS" />
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;family=Water+Brush&amp;display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/bootstrap-select/bootstrap-select.min.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/jquery-ui/jquery-ui.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/animate/animate.min.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/eduact-icons/style.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/jarallax/jarallax.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/jquery-magnific-popup/jquery.magnific-popup.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/nouislider/nouislider.min.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/nouislider/nouislider.pips.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/odometer/odometer.min.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/tiny-slider/tiny-slider.min.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/owl-carousel/assets/owl.carousel.min.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/vendors/owl-carousel/assets/owl.theme.default.min.css" />
    <link rel="stylesheet" href="{{asset('landing-page')}}/assets/css/eduact.css" />
    <style>
        @media (max-width: 991px) {
            .mobile-nav__login {
                margin-top: 16px;
            }

            .mobile-nav__login .eduact-btn {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
        }

        .preloader__inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }

        .preloader__logo {
            width: 220px;
            height: 72px;
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
        }

        .preloader__text {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            text-decoration: none;
        }

        .brand-logo:hover {
            color: #ffffff;
        }

        .brand-logo img {
            width: 140px;
            height: auto;
            object-fit: contain;
        }

        .brand-logo__text {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.03em;
            line-height: 1;
            white-space: nowrap;
        }

        .main-menu__logo .brand-logo__text {
            font-size: 22px;
        }

        .main-menu__logo .brand-logo img {
            width: 120px;
        }

        .course-one__thumb img,
        .course-one__thumb .course-thumbnail-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            background-color: #e9ecef;
        }

        .category-one__thumb img,
        .category-one__hover__thumb img {
            object-fit: cover;
            background-color: #e9ecef;
        }

        .course-one .row > [class*="col-"] {
            display: flex;
        }

        .landing-course-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .landing-course-card__body {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 300px;
        }

        .landing-course-card__main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .landing-course-card__title {
            min-height: 58px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 22px;
        }

        .landing-course-card__title a {
            display: inline;
        }

        .landing-course-card__footer {
            margin-top: auto;
        }

        .landing-course-card__footer .course-one__author {
            padding-left: 0;
        }

        .landing-stats {
            padding: 72px 0;
        }

        .landing-stats .counter-one__left {
            padding-top: 0;
        }

        .landing-stats .counter-one__left__content::after {
            display: none;
        }

        .landing-stats__intro-text {
            font-size: 17px;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .landing-stats__panel {
            background: #ffffff;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 18px 50px rgba(15, 35, 80, 0.18);
            max-width: 460px;
            margin-left: auto;
            margin-right: auto;
        }

        .landing-stats__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0;
        }

        .landing-stats__item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 22px 16px;
            min-height: 118px;
        }

        .landing-stats__item:nth-child(1),
        .landing-stats__item:nth-child(2) {
            border-bottom: 1px solid #e8edf5;
        }

        .landing-stats__item:nth-child(odd) {
            border-right: 1px solid #e8edf5;
        }

        .landing-stats__value {
            display: block;
            font-size: clamp(1.75rem, 4vw, 2.25rem);
            font-weight: 700;
            line-height: 1.1;
            color: #214B9A;
            margin-bottom: 6px;
        }

        .landing-stats__label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.35;
            color: #5c6b82;
            max-width: 140px;
        }

        @media (max-width: 991px) {
            .landing-stats {
                padding: 56px 0;
            }

            .landing-stats__panel {
                max-width: 100%;
            }
        }

        .copyright {
            background-color: #214B9A;
            padding: 22px 0;
        }

        .footer-main {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            text-decoration: none;
        }

        .footer-brand img {
            width: 96px;
            height: auto;
            object-fit: contain;
        }

        .footer-brand__text {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.02em;
            line-height: 1;
        }

        .footer-contact {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px 14px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
        }

        .footer-contact a {
            color: inherit;
            text-decoration: none;
        }

        .footer-contact a:hover {
            color: #ffffff;
        }

        @media (max-width: 1199px) {
            .main-menu__logo .brand-logo__text {
                display: none;
            }
        }

        .mobile-nav__content .logo-box {
            margin-bottom: 24px;
        }

        .mobile-nav__content .brand-logo {
            gap: 8px;
            max-width: 100%;
        }

        .mobile-nav__content .brand-logo img {
            width: 92px;
        }

        .mobile-nav__content .brand-logo__text {
            font-size: 14px;
        }

        @media (max-width: 575px) {
            .mobile-nav__content {
                width: 85vw;
                padding-left: 12px;
                padding-right: 12px;
            }

            .footer-brand__text {
                font-size: 16px;
            }

            .footer-contact {
                font-size: 12px;
                gap: 6px 10px;
            }
        }
    </style>
</head>

<body class="custom-cursor">

    <div class="custom-cursor__cursor"></div>
    <div class="custom-cursor__cursor-two"></div>

    <div class="preloader">
        <div class="preloader__inner">
            <div class="preloader__image preloader__logo" style="background-image: url('https://bpkp.go.id/_next/image?url=%2Fassets%2Fimages%2Fbpkp-putih-hd.png&w=1920&q=75');"></div>
            <div class="preloader__text">MOOCs BPKP</div>
        </div>
    </div>

    <div class="page-wrapper">
        <header class="main-header">
            <nav class="main-menu">
                <div class="container">
                    <div class="main-menu__logo">
                        <a href="{{ route('landing-page.index') }}" class="brand-logo">
                            <img src="https://bpkp.go.id/_next/image?url=%2Fassets%2Fimages%2Fbpkp-putih-hd.png&w=1920&q=75" alt="BPKP">
                            <span class="brand-logo__text">MOOCs BPKP</span>
                        </a>
                    </div>
                    <div class="main-menu__nav">
                        <ul class="main-menu__list">
                            <li><a href="{{ route('landing-page.index') }}">Beranda</a></li>
                            <li><a href="#pelatihan">Daftar Pelatihan</a></li>
                        </ul>
                    </div>
                    <div class="main-menu__right">
                        <a href="#" class="main-menu__toggler mobile-nav__toggler">
                            <i class="fa fa-bars"></i>
                        </a>
                        <a href="#" class="main-menu__search search-toggler">
                            <i class="icon-Search"></i>
                        </a>
                        <a href="/login" class="main-menu__login" style="display:none;">
                            <i class="icon-account-1"></i>
                        </a>
                        <a href="/login" class="eduact-btn"><span class="eduact-btn__curve"></span>Login Aplikasi</a>
                    </div>
                </div>
            </nav>
            
        </header>

        <div class="stricky-header stricked-menu main-menu">
            <div class="sticky-header__content"></div>
        </div>
        
        <section class="hero-banner" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/banner-bg-1.png);">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="hero-banner__content">
                            <div class="hero-banner__bg-shape1 wow zoomIn" data-wow-delay="300ms">
                                <div class="hero-banner__bg-round">
                                    <div class="hero-banner__bg-round-border"></div>
                                </div>
                            </div>
                            <h2 class="hero-banner__title wow fadeInUp" data-wow-delay="400ms">MOOCs Pusdiklatwas BPKP</h2>
                            <p class="hero-banner__text wow fadeInUp" data-wow-delay="500ms">
                                Kembangkan kompetensi &amp; karir Anda dengan waktu belajar yang fleksibel menggunakan eLearning
                                <img src="{{asset('landing-page')}}/assets/images/shapes/banner-1-shape-1.png" alt="eduact">
                            </p>
                            <div class="hero-banner__btn wow fadeInUp" data-wow-delay="600ms">
                                <a href="#pelatihan" class="eduact-btn"><span class="eduact-btn__curve"></span>Lihat Pelatihan<i class="icon-arrow"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-banner__thumb wow fadeInUp" data-wow-delay="700ms">
                            <img src="{{ asset('landing-page') }}/assets/images/resources/banner-1-1.png" alt="MOOCs BPKP">
                            <div class="hero-banner__cap wow slideInDown" data-wow-delay="800ms"><img src="{{asset('landing-page')}}/assets/images/shapes/banner-cap.png" alt=""></div>
                            <div class="hero-banner__star wow slideInDown" data-wow-delay="850ms"><img src="{{asset('landing-page')}}/assets/images/shapes/banner-star.png" alt=""></div>
                            <div class="hero-banner__map wow slideInDown" data-wow-delay="900ms"><img src="{{asset('landing-page')}}/assets/images/shapes/banner-map.png" alt=""></div>
                            <div class="hero-banner__video wow zoomIn" data-wow-delay="950ms" style="background-image: url({{ asset('landing-page') }}/assets/images/resources/banner-video.png);">
                                <a href="#pelatihan" class="video-popup"><span class="icon-play"></span></a>
                            </div>
                            <div class="hero-banner__book wow slideInUp" data-wow-delay="1000ms"><img src="{{asset('landing-page')}}/assets/images/shapes/banner-book.png" alt=""></div>
                            <div class="hero-banner__star2 wow slideInUp" data-wow-delay="1050ms"><img src="{{asset('landing-page')}}/assets/images/shapes/banner-star2.png" alt=""></div>
                            <div class="hero-banner__cart wow slideInUp" data-wow-delay="1100ms">
                                <div class="hero-banner__cart__thumb"><img src="{{ asset('landing-page') }}/assets/images/resources/banner-author.png" alt=""></div>
                                <div class="hero-banner__cart__content">
                                    <div class="hero-banner__cart__content-inner">
                                        <h4 class="hero-banner__cart__title">{{ __('Pelatihan Terbaru') }}</h4>
                                        <p class="hero-banner__cart__text">
                                            @if ($highlightCourse)
                                                <a href="#pelatihan" class="text-reset text-decoration-none">{{ \Illuminate\Support\Str::limit($highlightCourse->judul, 42) }}</a>
                                            @else
                                                MOOCs Pilihan
                                            @endif
                                        </p>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 88 38">
                                            <path d="M0.702945 55.648L108.145 0.179688L87.0803 78.7928L0.702945 55.648Z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero-banner__border wow fadeInUp" data-wow-delay="1100ms"></div>
        </section>
        
        
        <section class="service-one">
            <div class="service-one__bg" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/service-bg-1.png);"></div>
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="100ms">
                        <div class="service-one__item">
                            <div class="service-one__wrapper">
                                <div class="service-one__icon">
                                    <span class="icon-education"></span>
                                </div>
                                <h3 class="service-one__title">
                                    <a href="#pelatihan">Katalog Kursus</a>
                                </h3>
                                <p class="service-one__text">Jelajahi pelatihan daring yang dipublikasikan dan pilih kursus sesuai kebutuhan kompetensi Anda.</p>
                                <a class="service-one__rm" href="#pelatihan">Selengkapnya<span class="icon-caret-right"></span></a>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 118 129" fill="none">
                                    <path d="M0.582052 143.759C135.395 113.682 145.584 0.974413 145.584 0.974413L173.881 89.6286C173.881 89.6286 0.582054 322.604 0.582052 143.759Z" fill="#F1F2FD" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="200ms">
                        <div class="service-one__item">
                            <div class="service-one__wrapper">
                                <div class="service-one__icon">
                                    <span class="icon-business"></span>
                                </div>
                                <h3 class="service-one__title">
                                    <a href="#pelatihan">Belajar Mandiri</a>
                                </h3>
                                <p class="service-one__text">Akses materi video, bacaan, dan PDF kapan saja. Tandai materi selesai untuk melacak progres belajar.</p>
                                <a class="service-one__rm" href="#pelatihan">Selengkapnya<span class="icon-caret-right"></span></a>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 118 129" fill="none">
                                    <path d="M0.582052 143.759C135.395 113.682 145.584 0.974413 145.584 0.974413L173.881 89.6286C173.881 89.6286 0.582054 322.604 0.582052 143.759Z" fill="#F1F2FD" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="300ms">
                        <div class="service-one__item">
                            <div class="service-one__wrapper">
                                <div class="service-one__icon">
                                    <span class="icon-webinar"></span>
                                </div>
                                <h3 class="service-one__title">
                                    <a href="#pelatihan">Progres & Sertifikat</a>
                                </h3>
                                <p class="service-one__text">Pantau progres per kursus dan dapatkan sertifikat digital setelah menyelesaikan seluruh materi wajib.</p>
                                <a class="service-one__rm" href="#pelatihan">Selengkapnya<span class="icon-caret-right"></span></a>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 118 129" fill="none">
                                    <path d="M0.582052 143.759C135.395 113.682 145.584 0.974413 145.584 0.974413L173.881 89.6286C173.881 89.6286 0.582054 322.604 0.582052 143.759Z" fill="#F1F2FD" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="400ms">
                        <div class="service-one__item">
                            <div class="service-one__wrapper">
                                <div class="service-one__icon">
                                    <span class="icon-neural"></span>
                                </div>
                                <h3 class="service-one__title">
                                    <a href="#pelatihan">Pendaftaran Mandiri</a>
                                </h3>
                                <p class="service-one__text">Daftar kursus langsung dari katalog tanpa menunggu persetujuan admin, seperti pengalaman belajar di MOOC.</p>
                                <a class="service-one__rm" href="#pelatihan">Selengkapnya<span class="icon-caret-right"></span></a>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 118 129" fill="none">
                                    <path d="M0.582052 143.759C135.395 113.682 145.584 0.974413 145.584 0.974413L173.881 89.6286C173.881 89.6286 0.582054 322.604 0.582052 143.759Z" fill="#F1F2FD" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        
        <section class="about-one">
            <div class="container">
                <div class="row">
                    <div class="col-xl-6">
                        <div class="about-one__thumb wow fadeInLeft" data-wow-delay="100ms">
                            <div class="about-one__thumb__one eduact-tilt" data-tilt-options='{ "glare": false, "maxGlare": 0, "maxTilt": 2, "speed": 700, "scale": 1 }'>
                                <img src="{{ asset('landing-page') }}/assets/images/resources/about-1-1.png" alt="MOOCs BPKP">
                            </div>
                            <div class="about-one__thumb__shape1 wow zoomIn" data-wow-delay="300ms">
                                <img src="{{asset('landing-page')}}/assets/images/shapes/about-shape-1-1.png" alt="">
                            </div>
                            <div class="about-one__thumb__shape2 wow zoomIn" data-wow-delay="400ms">
                                <img src="{{asset('landing-page')}}/assets/images/shapes/about-shape-1-2.png" alt="">
                            </div>
                            <div class="about-one__thumb__box wow fadeInLeft" data-wow-delay="600ms">
                                <div class="about-one__thumb__box__icon"><span class="icon-Headphone-Women"></span></div>
                                <h4 class="about-one__thumb__box__title">Butuh Informasi?</h4>
                                <p class="about-one__thumb__box__text"><a href="mailto:elearning@bpkp.go.id">elearning@bpkp.go.id</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="about-one__content">
                            <div class="section-title">
                                <h5 class="section-title__tagline">
                                    Tentang Kami
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 133 13" fill="none">
                                        <path d="M9.76794 0.395L0.391789 9.72833C-0.130596 10.2483 -0.130596 11.095 0.391789 11.615C0.914174 12.135 1.76472 12.135 2.28711 11.615L11.6633 2.28167C12.1856 1.76167 12.1856 0.915 11.6633 0.395C11.1342 -0.131667 10.2903 -0.131667 9.76794 0.395Z" fill="#F1F2FD" />
                                        <path d="M23.1625 0.395L13.7863 9.72833C13.2639 10.2483 13.2639 11.095 13.7863 11.615C14.3087 12.135 15.1593 12.135 15.6816 11.615L25.0578 2.28167C25.5802 1.76167 25.5802 0.915 25.0578 0.395C24.5287 -0.131667 23.6849 -0.131667 23.1625 0.395Z" fill="#F1F2FD" />
                                        <path d="M36.5569 0.395L27.1807 9.72833C26.6583 10.2483 26.6583 11.095 27.1807 11.615C27.7031 12.135 28.5537 12.135 29.076 11.615L38.4522 2.28167C38.9746 1.76167 38.9746 0.915 38.4522 0.395C37.9231 -0.131667 37.0793 -0.131667 36.5569 0.395Z" fill="#F1F2FD" />
                                        <path d="M49.9514 0.395L40.5753 9.72833C40.0529 10.2483 40.0529 11.095 40.5753 11.615C41.0976 12.135 41.9482 12.135 42.4706 11.615L51.8467 2.28167C52.3691 1.76167 52.3691 0.915 51.8467 0.395C51.3176 -0.131667 50.4738 -0.131667 49.9514 0.395Z" fill="#F1F2FD" />
                                        <path d="M63.3459 0.395L53.9698 9.72833C53.4474 10.2483 53.4474 11.095 53.9698 11.615C54.4922 12.135 55.3427 12.135 55.8651 11.615L65.2413 2.28167C65.7636 1.76167 65.7636 0.915 65.2413 0.395C64.7122 -0.131667 63.8683 -0.131667 63.3459 0.395Z" fill="#F1F2FD" />
                                        <path d="M76.7405 0.395L67.3643 9.72833C66.8419 10.2483 66.8419 11.095 67.3643 11.615C67.8867 12.135 68.7373 12.135 69.2596 11.615L78.6358 2.28167C79.1582 1.76167 79.1582 0.915 78.6358 0.395C78.1067 -0.131667 77.2629 -0.131667 76.7405 0.395Z" fill="#F1F2FD" />
                                        <path d="M90.1349 0.395L80.7587 9.72833C80.2363 10.2483 80.2363 11.095 80.7587 11.615C81.2811 12.135 82.1317 12.135 82.6541 11.615L92.0302 2.28167C92.5526 1.76167 92.5526 0.915 92.0302 0.395C91.5011 -0.131667 90.6573 -0.131667 90.1349 0.395Z" fill="#F1F2FD" />
                                        <path d="M103.529 0.395L94.1533 9.72833C93.6309 10.2483 93.6309 11.095 94.1533 11.615C94.6756 12.135 95.5262 12.135 96.0486 11.615L105.425 2.28167C105.947 1.76167 105.947 0.915 105.425 0.395C104.896 -0.131667 104.052 -0.131667 103.529 0.395Z" fill="#F1F2FD" />
                                        <path d="M116.924 0.395L107.548 9.72833C107.025 10.2483 107.025 11.095 107.548 11.615C108.07 12.135 108.921 12.135 109.443 11.615L118.819 2.28167C119.342 1.76167 119.342 0.915 118.819 0.395C118.29 -0.131667 117.446 -0.131667 116.924 0.395Z" fill="#F1F2FD" />
                                        <path d="M130.318 0.395L120.942 9.72833C120.42 10.2483 120.42 11.095 120.942 11.615C121.465 12.135 122.315 12.135 122.838 11.615L132.214 2.28167C132.736 1.76167 132.736 0.915 132.214 0.395C131.685 -0.131667 130.841 -0.131667 130.318 0.395Z" fill="#F1F2FD" />
                                    </svg>
                                </h5>
                                <h2 class="section-title__title">Platform Pembelajaran Daring BPKP</h2>
                            </div>
                            <p class="about-one__content__text">
                                MOOCs BPKP menghadirkan pelatihan daring terstruktur dengan modul materi, pelacakan progres, dan sertifikat kelulusan untuk mendukung peningkatan kompetensi ASN dan mitra BPKP.
                            </p>
                            <div class="about-one__box">
                                <div class="about-one__box__wrapper">
                                    <div class="about-one__box__icon"><span class="icon-Presentation"></span></div>
                                    <h4 class="about-one__box__title">{{ number_format($stats['courses'] ?? 0) }} Kursus Aktif</h4>
                                    <p class="about-one__box__text">Pelatihan dipublikasikan dan siap diikuti peserta terdaftar.</p>
                                </div>
                            </div>
                            <div class="about-one__box">
                                <div class="about-one__box__wrapper">
                                    <div class="about-one__box__icon"><span class="icon-Online-learning"></span></div>
                                    <h4 class="about-one__box__title">{{ number_format($stats['certificates'] ?? 0) }} Sertifikat Terbit</h4>
                                    <p class="about-one__box__text">Bukti penyelesaian pelatihan yang dapat diunduh peserta.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        
        @include('frontend.partials.landing-categories')

        @include('frontend.partials.landing-courses')

        @include('frontend.partials.landing-stats')

        <section class="copyright text-center">
            <div class="container wow fadeInUp" data-wow-delay="400ms">
                <div class="footer-main">
                    <a href="{{ route('landing-page.index') }}" class="footer-brand">
                        <img src="https://bpkp.go.id/_next/image?url=%2Fassets%2Fimages%2Fbpkp-putih-hd.png&w=1920&q=75" alt="BPKP">
                        <span class="footer-brand__text">MOOCs BPKP</span>
                    </a>
                    <ul class="footer-contact">
                        <li><a href="https://mooc.bpkp.go.id" target="_blank" rel="noopener noreferrer">mooc.bpkp.go.id</a></li>
                        <li><a href="tel:082260707030">082260707030</a></li>
                        <li><a href="mailto:elearning@bpkp.go.id">elearning@bpkp.go.id</a></li>
                    </ul>
                </div>
            </div>
        </section>

    </div>


    <div class="mobile-nav__wrapper">
        <div class="mobile-nav__overlay mobile-nav__toggler"></div>
        <div class="mobile-nav__content">
            <span class="mobile-nav__close mobile-nav__toggler"><i class="fa fa-times"></i></span>
            <div class="logo-box">
                <a href="index.html" aria-label="logo image" class="brand-logo"><img src="https://bpkp.go.id/_next/image?url=%2Fassets%2Fimages%2Fbpkp-putih-hd.png&w=1920&q=75" alt="BPKP" /><span class="brand-logo__text">MOOCs BPKP</span></a>
            </div>
            <div class="mobile-nav__container"></div>
            <div class="mobile-nav__login">
                <a href="/login" class="eduact-btn eduact-btn-second"><span class="eduact-btn__curve"></span>Login Aplikasi</a>
            </div>
        </div>
    </div>

    <div class="search-popup">
        <div class="search-popup__overlay search-toggler"></div>
        <div class="search-popup__content">
            <form role="search" method="get" class="search-popup__form" action="#">
                <input type="text" id="search" placeholder="Search Here..." />
                <button type="submit" class="eduact-btn"><span class="eduact-btn__curve"></span><i class="icon-Search"></i></button>
            </form>
        </div>
    </div>

    
    <a href="#" class="scroll-top">
        <svg class="scroll-top__circle" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </a>
    


    <script src="{{asset('landing-page')}}/assets/vendors/jquery/jquery-3.5.1.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/jquery-ui/jquery-ui.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/jarallax/jarallax.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/jquery-ajaxchimp/jquery.ajaxchimp.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/jquery-appear/jquery.appear.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/jquery-circle-progress/jquery.circle-progress.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/jquery-magnific-popup/jquery.magnific-popup.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/jquery-validate/jquery.validate.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/nouislider/nouislider.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/odometer/odometer.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/tiny-slider/tiny-slider.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/owl-carousel/owl.carousel.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/wnumb/wNumb.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/jquery-circleType/jquery.circleType.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/jquery-lettering/jquery.lettering.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/tilt/tilt.jquery.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/wow/wow.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/isotope/isotope.js"></script>
    <script src="{{asset('landing-page')}}/assets/vendors/countdown/countdown.min.js"></script>
    <script src="{{asset('landing-page')}}/assets/js/eduact.js"></script>
</body>
</html>
