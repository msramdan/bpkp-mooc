<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home One || Eduact || HTML Template For Educaton & LMS</title>
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
                            <li>
                                <a href="{{ route('landing-page.index') }}">Home</a>
                                <ul style="display:none;">
                                    <li>
                                        <section class="home-showcase">
                                            <div class="container">
                                                <div class="home-showcase__inner">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <div class="home-showcase__item">
                                                                <div class="home-showcase__image">
                                                                    <img src="{{asset('landing-page')}}/assets/images/home-showcase/home-showcase-1-1.jpg" alt="eduact">
                                                                    <div class="home-showcase__buttons">
                                                                        <a href="index.html" class="eduact-btn home-showcase__buttons__item">
                                                                            <span class="eduact-btn__curve"></span>
                                                                            Multi Page
                                                                        </a>
                                                                        <a href="index-one-page.html" class="eduact-btn home-showcase__buttons__item">
                                                                            <span class="eduact-btn__curve"></span>
                                                                            One Page
                                                                        </a>
                                                                    </div>
                                                                    <!-- /.home-showcase__buttons -->
                                                                </div><!-- /.home-showcase__image -->
                                                                <h3 class="home-showcase__title">Home page 01</h3><!-- /.home-showcase__title -->
                                                            </div><!-- /.home-showcase__item -->
                                                        </div><!-- /.col-lg-3 -->
                                                        <div class="col-lg-3">
                                                            <div class="home-showcase__item">
                                                                <div class="home-showcase__image">
                                                                    <img src="{{asset('landing-page')}}/assets/images/home-showcase/home-showcase-1-2.jpg" alt="eduact">
                                                                    <div class="home-showcase__buttons">
                                                                        <a href="index-2.html" class="eduact-btn home-showcase__buttons__item">
                                                                            <span class="eduact-btn__curve"></span>
                                                                            Multi Page
                                                                        </a>
                                                                        <a href="index-2-one-page.html" class="eduact-btn home-showcase__buttons__item">
                                                                            <span class="eduact-btn__curve"></span>
                                                                            One Page
                                                                        </a>
                                                                    </div>
                                                                    <!-- /.home-showcase__buttons -->
                                                                </div><!-- /.home-showcase__image -->
                                                                <h3 class="home-showcase__title">Home page 02</h3><!-- /.home-showcase__title -->
                                                            </div><!-- /.home-showcase__item -->
                                                        </div><!-- /.col-lg-3 -->
                                                        <div class="col-lg-3">
                                                            <div class="home-showcase__item">
                                                                <div class="home-showcase__image">
                                                                    <img src="{{asset('landing-page')}}/assets/images/home-showcase/home-showcase-1-3.jpg" alt="eduact">
                                                                    <div class="home-showcase__buttons">
                                                                        <a href="index-3.html" class="eduact-btn home-showcase__buttons__item">
                                                                            <span class="eduact-btn__curve"></span>
                                                                            Multi Page
                                                                        </a>
                                                                        <a href="index-3-one-page.html" class="eduact-btn home-showcase__buttons__item">
                                                                            <span class="eduact-btn__curve"></span>
                                                                            One Page
                                                                        </a>
                                                                    </div>
                                                                    <!-- /.home-showcase__buttons -->
                                                                </div><!-- /.home-showcase__image -->
                                                                <h3 class="home-showcase__title">Home page 03</h3><!-- /.home-showcase__title -->
                                                            </div><!-- /.home-showcase__item -->
                                                        </div><!-- /.col-lg-3 -->
                                                        <div class="col-lg-3">
                                                            <div class="home-showcase__item">
                                                                <div class="home-showcase__image">
                                                                    <img src="{{asset('landing-page')}}/assets/images/home-showcase/home-showcase-1-4.jpg" alt="eduact">
                                                                    <div class="home-showcase__buttons">
                                                                        <a href="index-dark.html" class="eduact-btn home-showcase__buttons__item">
                                                                            <span class="eduact-btn__curve"></span>
                                                                            View Page
                                                                        </a>
                                                                    </div>
                                                                    <!-- /.home-showcase__buttons -->
                                                                </div><!-- /.home-showcase__image -->
                                                                <h3 class="home-showcase__title">Home page 04</h3><!-- /.home-showcase__title -->
                                                            </div><!-- /.home-showcase__item -->
                                                        </div><!-- /.col-lg-3 -->
                                                    </div><!-- /.row -->
                                                </div><!-- /.home-showcase__inner -->
                                            </div><!-- /.container -->
                                        </section>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown" style="display:none;">
                                <a href="#">Pages</a>
                                <ul>
                                    <li><a href="about.html">About</a></li>
                                    <li><a href="team.html">Our Teacher</a></li>
                                    <li><a href="team-carousel.html">Teacher Carousel</a></li>
                                    <li><a href="team-become.html">Become Teacher</a></li>
                                    <li><a href="team-details.html">Teacher Details</a></li>
                                    <li><a href="gallery.html">Gallery</a></li>
                                    <li><a href="gallery-carousel.html">Gallery Carousel</a></li>
                                    <li><a href="pricing.html">Pricing</a></li>
                                    <li><a href="faq.html">FAQs</a></li>
                                    <li><a href="/login">Login Aplikasi</a></li>
                                    <li><a href="404.html">404 Error</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="#pelatihan">Daftar Pelatihan</a>
                                <ul style="display:none;">
                                    <li><a href="course.html">Course Page</a></li>
                                    <li><a href="course-carousel.html">Course Carousel</a></li>
                                    <li><a href="management-consulting.html">Management Consulting</a></li>
                                    <li><a href="web-development.html">Web Development</a></li>
                                    <li><a href="frontend-development.html">Frontend Development</a></li>
                                    <li><a href="uiux-design.html">UI/UX Design</a></li>
                                    <li><a href="digital-photography.html">Digital Photography</a></li>
                                    <li><a href="online-business.html">Online Business</a></li>
                                </ul>
                            </li>
                            <li class="dropdown" style="display:none;">
                                <a href="#">Shop</a>
                                <ul class="sub-menu">
                                    <li class="dropdown">
                                        <a href="#">Products</a>
                                        <ul class="sub-menu">
                                            <li><a href="products.html">No Sidebar</a></li>
                                            <li><a href="products-left.html">Left Sidebar</a></li>
                                            <li><a href="products-right.html">Right Sidebar</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="products-carousel.html">Products Carousel</a></li>
                                    <li><a href="product-details.html">Product Details</a></li>
                                    <li><a href="cart.html">Cart</a></li>
                                    <li><a href="checkout.html">Checkout</a></li>
                                </ul>
                            </li>
                            <li class="dropdown" style="display:none;">
                                <a href="#">Blog</a>
                                <ul class="sub-menu">
                                    <li class="dropdown">
                                        <a href="#">Blog Grid</a>
                                        <ul class="sub-menu">
                                            <li><a href="blog-grid.html">No Sidebar</a></li>
                                            <li><a href="blog-grid-left.html">Left Sidebar</a></li>
                                            <li><a href="blog-grid-right.html">Right Sidebar</a></li>
                                        </ul>
                                    </li>
                                    <li class="dropdown">
                                        <a href="#">Blog List</a>
                                        <ul class="sub-menu">
                                            <li><a href="blog-list.html">No Sidebar</a></li>
                                            <li><a href="blog-list-left.html">Left Sidebar</a></li>
                                            <li><a href="blog-list-right.html">Right Sidebar</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="blog-carousel.html">Blog Carousel</a></li>
                                    <li class="dropdown">
                                        <a href="#">Blog Details</a>
                                        <ul class="sub-menu">
                                            <li><a href="blog-details.html">No Sidebar</a></li>
                                            <li><a href="blog-details-left.html">Left Sidebar</a></li>
                                            <li><a href="blog-details-right.html">Right Sidebar</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li style="display:none;"><a href="contact.html">Contact</a></li>
                        </ul>
                    </div><!-- /.main-menu__nav -->
                    <div class="main-menu__right">
                        <a href="#" class="main-menu__toggler mobile-nav__toggler">
                            <i class="fa fa-bars"></i>
                        </a><!-- /.mobile menu btn -->
                        <a href="#" class="main-menu__search search-toggler">
                            <i class="icon-Search"></i>
                        </a><!-- /.search btn -->
                        <a href="/login" class="main-menu__login" style="display:none;">
                            <i class="icon-account-1"></i>
                        </a><!-- /.login btn -->
                        <a href="/login" class="eduact-btn"><span class="eduact-btn__curve"></span>Login Aplikasi</a><!-- /.contact btn -->
                    </div><!-- /.main-menu__right -->
                </div><!-- /.container -->
            </nav>
            <!-- /.main-menu -->
        </header><!-- /.main-header -->

        <div class="stricky-header stricked-menu main-menu">
            <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
        </div><!-- /.stricky-header -->
        <!--Hero Banner Start-->
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
                                <a href="/login" class="eduact-btn eduact-btn-second"><span class="eduact-btn__curve"></span>Mulai Sekarang<i class="icon-arrow"></i></a>
                                <a href="#pelatihan" class="eduact-btn"><span class="eduact-btn__curve"></span>Daftar Pelatihan<i class="icon-arrow"></i></a>
                            </div><!-- banner-btn -->
                        </div><!-- banner-content -->
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-banner__thumb wow fadeInUp" data-wow-delay="700ms">
                            <img src="{{asset('landing-page')}}/assets/images/resources/banner-1-1.png" alt="eduact">
                            <div class="hero-banner__cap wow slideInDown" data-wow-delay="800ms"><img src="{{asset('landing-page')}}/assets/images/shapes/banner-cap.png" alt="eduact"></div><!-- banner-cap -->
                            <div class="hero-banner__star wow slideInDown" data-wow-delay="850ms"><img src="{{asset('landing-page')}}/assets/images/shapes/banner-star.png" alt="eduact"></div><!-- banner-star -->
                            <div class="hero-banner__map wow slideInDown" data-wow-delay="900ms"><img src="{{asset('landing-page')}}/assets/images/shapes/banner-map.png" alt="eduact"></div><!-- banner-map -->
                            <div class="hero-banner__video wow zoomIn" data-wow-delay="950ms" style="background-image: url({{asset('landing-page')}}/assets/images/resources/banner-video.png);">
                                <a href="https://www.youtube.com/watch?v=FTOS6cwJNCs" class="video-popup"><span class="icon-play"></span></a>
                            </div><!-- banner-video -->
                            <div class="hero-banner__book wow slideInUp" data-wow-delay="1000ms"><img src="{{asset('landing-page')}}/assets/images/shapes/banner-book.png" alt="eduact"></div><!-- banner-book -->
                            <div class="hero-banner__star2 wow slideInUp" data-wow-delay="1050ms"><img src="{{asset('landing-page')}}/assets/images/shapes/banner-star2.png" alt="eduact"></div><!-- banner-star -->
                            <div class="hero-banner__cart wow slideInUp" data-wow-delay="1100ms">
                                <div class="hero-banner__cart__thumb"><img src="{{asset('landing-page')}}/assets/images/resources/banner-author.png" alt="eduact"></div>
                                <div class="hero-banner__cart__content">
                                    <div class="hero-banner__cart__content-inner">
                                        <h4 class="hero-banner__cart__title">Pelatihan Terbaru</h4>
                                        <p class="hero-banner__cart__text">MOOCs Pilihan</p>
                                        <a href="#pelatihan" class="eduact-btn"><span class="eduact-btn__curve"></span>Mulai<i class="icon-arrow"></i></a>
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
            <div class="hero-banner__border wow fadeInUp" data-wow-delay="1100ms"></div><!-- banner-border -->
        </section>
        <!--Hero Banner End-->
        <!-- Service Start -->
        <section class="service-one">
            <div class="service-one__bg" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/service-bg-1.png);"></div>
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="100ms">
                        <div class="service-one__item">
                            <div class="service-one__wrapper">
                                <div class="service-one__icon">
                                    <span class="icon-education"></span>
                                </div><!-- /.service-icon -->
                                <h3 class="service-one__title">
                                    <a href="team.html">Exclusive Coach</a>
                                </h3><!-- /.service-title -->
                                <p class="service-one__text">Lorem ipsum dolor sit amet consectetur. Convallis ornare semper id hendrerit diam. Mauris cursus suscipit</p><!-- /.service-content -->
                                <a class="service-one__rm" href="team.html">Read More<span class="icon-caret-right"></span></a>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 118 129" fill="none">
                                    <path d="M0.582052 143.759C135.395 113.682 145.584 0.974413 145.584 0.974413L173.881 89.6286C173.881 89.6286 0.582054 322.604 0.582052 143.759Z" fill="#F1F2FD" />
                                </svg>
                            </div>
                        </div><!-- /.service-card-one -->
                    </div>
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="200ms">
                        <div class="service-one__item">
                            <div class="service-one__wrapper">
                                <div class="service-one__icon">
                                    <span class="icon-business"></span>
                                </div><!-- /.service-icon -->
                                <h3 class="service-one__title">
                                    <a href="team-become.html">Creative Minds</a>
                                </h3><!-- /.service-title -->
                                <p class="service-one__text">Lorem ipsum dolor sit amet consectetur. Convallis ornare semper id hendrerit diam. Mauris cursus suscipit</p><!-- /.service-content -->
                                <a class="service-one__rm" href="team-become.html">Read More<span class="icon-caret-right"></span></a>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 118 129" fill="none">
                                    <path d="M0.582052 143.759C135.395 113.682 145.584 0.974413 145.584 0.974413L173.881 89.6286C173.881 89.6286 0.582054 322.604 0.582052 143.759Z" fill="#F1F2FD" />
                                </svg>
                            </div>
                        </div><!-- /.service-card-one -->
                    </div>
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="300ms">
                        <div class="service-one__item">
                            <div class="service-one__wrapper">
                                <div class="service-one__icon">
                                    <span class="icon-webinar"></span>
                                </div><!-- /.service-icon -->
                                <h3 class="service-one__title">
                                    <a href="course.html">Video Tutorials</a>
                                </h3><!-- /.service-title -->
                                <p class="service-one__text">Lorem ipsum dolor sit amet consectetur. Convallis ornare semper id hendrerit diam. Mauris cursus suscipit</p><!-- /.service-content -->
                                <a class="service-one__rm" href="course.html">Read More<span class="icon-caret-right"></span></a>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 118 129" fill="none">
                                    <path d="M0.582052 143.759C135.395 113.682 145.584 0.974413 145.584 0.974413L173.881 89.6286C173.881 89.6286 0.582054 322.604 0.582052 143.759Z" fill="#F1F2FD" />
                                </svg>
                            </div>
                        </div><!-- /.service-card-one -->
                    </div>
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="400ms">
                        <div class="service-one__item">
                            <div class="service-one__wrapper">
                                <div class="service-one__icon">
                                    <span class="icon-neural"></span>
                                </div><!-- /.service-icon -->
                                <h3 class="service-one__title">
                                    <a href="about.html">Worlds Record</a>
                                </h3><!-- /.service-title -->
                                <p class="service-one__text">Lorem ipsum dolor sit amet consectetur. Convallis ornare semper id hendrerit diam. Mauris cursus suscipit</p><!-- /.service-content -->
                                <a class="service-one__rm" href="about.html">Read More<span class="icon-caret-right"></span></a>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 118 129" fill="none">
                                    <path d="M0.582052 143.759C135.395 113.682 145.584 0.974413 145.584 0.974413L173.881 89.6286C173.881 89.6286 0.582054 322.604 0.582052 143.759Z" fill="#F1F2FD" />
                                </svg>
                            </div>
                        </div><!-- /.service-card-one -->
                    </div>
                </div>
            </div>
        </section>
        <!-- Service End -->
        <!-- About Start -->
        <section class="about-one">
            <div class="container">
                <div class="row">
                    <div class="col-xl-6">
                        <div class="about-one__thumb wow fadeInLeft" data-wow-delay="100ms"><!-- about thumb start -->
                            <div class="about-one__thumb__one eduact-tilt" data-tilt-options='{ "glare": false, "maxGlare": 0, "maxTilt": 2, "speed": 700, "scale": 1 }'>
                                <img src="{{asset('landing-page')}}/assets/images/resources/about-1-1.png" alt="eduact">
                            </div>
                            <div class="about-one__thumb__shape1 wow zoomIn" data-wow-delay="300ms">
                                <img src="{{asset('landing-page')}}/assets/images/shapes/about-shape-1-1.png" alt="eduact">
                            </div>
                            <div class="about-one__thumb__shape2 wow zoomIn" data-wow-delay="400ms">
                                <img src="{{asset('landing-page')}}/assets/images/shapes/about-shape-1-2.png" alt="eduact">
                            </div>
                            <div class="about-one__thumb__box wow fadeInLeft" data-wow-delay="600ms">
                                <div class="about-one__thumb__box__icon"><span class="icon-Headphone-Women"></span></div>
                                <h4 class="about-one__thumb__box__title">Need to Know More Details?</h4>
                                <p class="about-one__thumb__box__text"><a href="tel:6845550102">+(684) 555-0102</a></p>
                            </div>
                        </div><!-- about thumb end -->
                    </div>
                    <div class="col-xl-6">
                        <div class="about-one__content"><!-- about content start-->
                            <div class="section-title">
                                <h5 class="section-title__tagline">
                                    About Us
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
                                <h2 class="section-title__title">Creating a Lifelong Learning Best Community</h2>
                            </div><!-- section-title -->
                            <p class="about-one__content__text">
                                It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks
                            </p>
                            <div class="about-one__box">
                                <div class="about-one__box__wrapper">
                                    <div class="about-one__box__icon"><span class="icon-Presentation"></span></div>
                                    <h4 class="about-one__box__title">Flexible Classes</h4>
                                    <p class="about-one__box__text">The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>
                                </div>
                            </div><!-- /.icon-box -->
                            <div class="about-one__box">
                                <div class="about-one__box__wrapper">
                                    <div class="about-one__box__icon"><span class="icon-Online-learning"></span></div>
                                    <h4 class="about-one__box__title">Live Class From anywhere</h4>
                                    <p class="about-one__box__text">The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>
                                </div>
                            </div><!-- /.icon-box -->
                            <a href="about.html" class="eduact-btn"><span class="eduact-btn__curve"></span>Discover More<i class="icon-arrow"></i></a><!-- /.btn -->
                        </div><!-- about content end-->
                    </div>
                </div>
            </div>
        </section>
        <!-- About End -->
        <!-- Category Start -->
        <section class="category-one" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-bg-1.jpg);">
            <div class="container">
                <div class="section-title text-center">
                    <h5 class="section-title__tagline">
                        Category
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
                    <h2 class="section-title__title">Favorite Topics To Learn</h2>
                </div><!-- section-title -->
                <div class="category-one__slider eduact-owl__carousel owl-with-shadow owl-theme owl-carousel" data-owl-options='{
            "items": 4,
            "margin": 30,
            "smartSpeed": 700,
            "loop":true,
            "autoplay": true,
            "nav":false,
            "dots":true,
            "navText": ["<span class=\"icon-arrow-left\"></span>","<span class=\"icon-arrow\"></span>"],
            "responsive":{
                "0":{
                    "items":1,
                    "nav":true,
                    "dots":false,
                    "margin": 0
                },
                "670":{
                    "nav":true,
                    "dots":false,
                    "items": 2
                },
                "992":{
                    "items": 3
                },
                "1200":{
                    "items": 3
                },
                "1400":{
                    "items": 4,
                    "margin": 36
                }
            }
            }'>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-1.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-education"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Business Analysis</h3><!-- /.category-title -->
                                    <p class="category-one__text">8 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-1.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-education"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Business Analysis</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">8 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-2.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Technology"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Data Science</h3><!-- /.category-title -->
                                    <p class="category-one__text">6 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-2.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Technology"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Data Science</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">6 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-3.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Digital-marketing"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Digital Marketing</h3><!-- /.category-title -->
                                    <p class="category-one__text">5 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-3.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Digital-marketing"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Digital Marketing</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">5 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-4.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Start-up"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Business Ideas</h3><!-- /.category-title -->
                                    <p class="category-one__text">9 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-4.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Start-up"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Business Ideas</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">9 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-1.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-education"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Business Analysis</h3><!-- /.category-title -->
                                    <p class="category-one__text">8 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-1.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-education"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Business Analysis</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">8 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-2.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Technology"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Data Science</h3><!-- /.category-title -->
                                    <p class="category-one__text">6 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-2.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Technology"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Data Science</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">6 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-3.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Digital-marketing"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Digital Marketing</h3><!-- /.category-title -->
                                    <p class="category-one__text">5 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-3.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Digital-marketing"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Digital Marketing</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">5 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-4.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Start-up"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Business Ideas</h3><!-- /.category-title -->
                                    <p class="category-one__text">9 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-4.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Start-up"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Business Ideas</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">9 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-1.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-education"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Business Analysis</h3><!-- /.category-title -->
                                    <p class="category-one__text">8 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-1.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-education"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Business Analysis</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">8 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-2.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Technology"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Data Science</h3><!-- /.category-title -->
                                    <p class="category-one__text">6 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-2.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Technology"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Data Science</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">6 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-3.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Digital-marketing"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Digital Marketing</h3><!-- /.category-title -->
                                    <p class="category-one__text">5 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-3.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Digital-marketing"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Digital Marketing</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">5 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-4.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Start-up"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Business Ideas</h3><!-- /.category-title -->
                                    <p class="category-one__text">9 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-4.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Start-up"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Business Ideas</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">9 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-1.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-education"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Business Analysis</h3><!-- /.category-title -->
                                    <p class="category-one__text">8 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-1.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-education"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Business Analysis</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">8 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-2.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Technology"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Data Science</h3><!-- /.category-title -->
                                    <p class="category-one__text">6 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-2.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Technology"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Data Science</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">6 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-3.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Digital-marketing"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Digital Marketing</h3><!-- /.category-title -->
                                    <p class="category-one__text">5 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-3.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Digital-marketing"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Digital Marketing</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">5 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-4.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Start-up"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Business Ideas</h3><!-- /.category-title -->
                                    <p class="category-one__text">9 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-4.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Start-up"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Business Ideas</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">9 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-1.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-education"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Business Analysis</h3><!-- /.category-title -->
                                    <p class="category-one__text">8 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-1.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-education"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Business Analysis</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">8 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-2.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Technology"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Data Science</h3><!-- /.category-title -->
                                    <p class="category-one__text">6 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-2.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Technology"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Data Science</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">6 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-3.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Digital-marketing"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Digital Marketing</h3><!-- /.category-title -->
                                    <p class="category-one__text">5 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-3.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Digital-marketing"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Digital Marketing</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">5 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                    <div class="item">
                        <div class="category-one__item">
                            <div class="category-one__wrapper" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/category-shape.png);">
                                <div class="category-one__thumb"><img src="{{asset('landing-page')}}/assets/images/category/category-normal-4.png" alt="eduact" /></div><!-- /.category-thumb -->
                                <div class="category-one__content">
                                    <div class="category-one__icon">
                                        <span class="icon-Start-up"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__title">Business Ideas</h3><!-- /.category-title -->
                                    <p class="category-one__text">9 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                            <div class="category-one__hover">
                                <div class="category-one__hover__thumb">
                                    <img src="{{asset('landing-page')}}/assets/images/category/category-hover-4.png" alt="eduact" />
                                </div><!-- /.category-thumb -->
                                <div class="category-one__hover__content">
                                    <div class="category-one__hover__icon">
                                        <span class="icon-Start-up"></span>
                                    </div><!-- /.category-icon -->
                                    <h3 class="category-one__hover__title"><a href="about.html">Business Ideas</a></h3><!-- /.category-title -->
                                    <p class="category-one__hover__text">9 Courses</p><!-- /.category-content -->
                                </div>
                            </div>
                        </div><!-- /.category-card-one -->
                    </div>
                </div>
            </div>
        </section>
        <!-- Category End -->
        <!-- Course Start -->
        <section id="pelatihan" class="course-one" style="background-image: url({{asset('landing-page')}}/assets/images/shapes/course-bg-1.png);">
            <div class="container">
                <div class="section-title text-center">
                    <h5 class="section-title__tagline">
                        Pelatihan Yang Sedang Berlangsung
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
                    <h2 class="section-title__title">Pelatihan Unggulan Bulan Ini</h2>
                </div><!-- section-title -->
                <div class="row">
                    <div class="col-xl-4 col-lg-6 wow fadeInUp" data-wow-delay="100ms">
                        <div class="course-one__item">
                            <div class="course-one__thumb">
                                <img src="{{asset('landing-page')}}/assets/images/course/course-1-1.png" alt="eduact">
                                <a class="course-one__like" href="javascript:void(0);"><span class="icon-like"></span></a>
                            </div><!-- /.course-thumb -->
                            <div class="course-one__content">
                                <div class="course-one__time">20 Hours</div>
                                <div class="course-one__ratings">
                                    <span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span>
                                    <div class="course-one__ratings__reviews">(25 Reviews)</div>
                                </div>
                                <h3 class="course-one__title">
                                    <a href="management-consulting.html">Management Consultants in Competitive Markets</a>
                                </h3>
                                <div class="course-one__bottom">
                                    <div class="course-one__author">
                                        <img src="{{asset('landing-page')}}/assets/images/course/author-1.png" alt="eduact">
                                        <h5 class="course-one__author__name">Guy Hawkins</h5>
                                        <p class="course-one__author__designation">Project Manager</p>
                                    </div>
                                    <div class="course-one__meta">
                                        <h4 class="course-one__meta__price">$473.00</h4>
                                        <p class="course-one__meta__class">15 Lessons</p>
                                    </div>
                                </div>
                            </div><!-- /.course-content -->
                        </div><!-- /.course-card-one -->
                    </div>
                    <div class="col-xl-4 col-lg-6 wow fadeInUp" data-wow-delay="200ms">
                        <div class="course-one__item">
                            <div class="course-one__thumb">
                                <img src="{{asset('landing-page')}}/assets/images/course/course-1-2.png" alt="eduact">
                                <a class="course-one__like" href="javascript:void(0);"><span class="icon-like"></span></a>
                            </div><!-- /.course-thumb -->
                            <div class="course-one__content">
                                <div class="course-one__time">40 Hours</div>
                                <div class="course-one__ratings">
                                    <span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span>
                                    <div class="course-one__ratings__reviews">(25 Reviews)</div>
                                </div>
                                <h3 class="course-one__title">
                                    <a href="web-development.html">The Ultimate Developer Course For Future Learner</a>
                                </h3>
                                <div class="course-one__bottom">
                                    <div class="course-one__author">
                                        <img src="{{asset('landing-page')}}/assets/images/course/author-2.png" alt="eduact">
                                        <h5 class="course-one__author__name">Devon Lane</h5>
                                        <p class="course-one__author__designation">ROR Developer</p>
                                    </div>
                                    <div class="course-one__meta">
                                        <h4 class="course-one__meta__price">$943.00</h4>
                                        <p class="course-one__meta__class">10 Lessons</p>
                                    </div>
                                </div>
                            </div><!-- /.course-content -->
                        </div><!-- /.course-card-one -->
                    </div>
                    <div class="col-xl-4 col-lg-6 wow fadeInUp" data-wow-delay="300ms">
                        <div class="course-one__item">
                            <div class="course-one__thumb">
                                <img src="{{asset('landing-page')}}/assets/images/course/course-1-3.png" alt="eduact">
                                <a class="course-one__like" href="javascript:void(0);"><span class="icon-like"></span></a>
                            </div><!-- /.course-thumb -->
                            <div class="course-one__content">
                                <div class="course-one__time">13 Hours</div>
                                <div class="course-one__ratings">
                                    <span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span>
                                    <div class="course-one__ratings__reviews">(25 Reviews)</div>
                                </div>
                                <h3 class="course-one__title">
                                    <a href="frontend-development.html">The Special HTML & CSS Bootcamp Edition</a>
                                </h3>
                                <div class="course-one__bottom">
                                    <div class="course-one__author">
                                        <img src="{{asset('landing-page')}}/assets/images/course/author-3.png" alt="eduact">
                                        <h5 class="course-one__author__name">Darrell Steward</h5>
                                        <p class="course-one__author__designation">Fronted Developer</p>
                                    </div>
                                    <div class="course-one__meta">
                                        <h4 class="course-one__meta__price">$767.00</h4>
                                        <p class="course-one__meta__class">13 Lessons</p>
                                    </div>
                                </div>
                            </div><!-- /.course-content -->
                        </div><!-- /.course-card-one -->
                    </div>
                    <div class="col-xl-4 col-lg-6 wow fadeInUp" data-wow-delay="400ms">
                        <div class="course-one__item">
                            <div class="course-one__thumb">
                                <img src="{{asset('landing-page')}}/assets/images/course/course-1-4.png" alt="eduact">
                                <a class="course-one__like" href="javascript:void(0);"><span class="icon-like"></span></a>
                            </div><!-- /.course-thumb -->
                            <div class="course-one__content">
                                <div class="course-one__time">25 Hours</div>
                                <div class="course-one__ratings">
                                    <span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span>
                                    <div class="course-one__ratings__reviews">(25 Reviews)</div>
                                </div>
                                <h3 class="course-one__title">
                                    <a href="uiux-design.html">Building Responsive User Interfaces to Implementing</a>
                                </h3>
                                <div class="course-one__bottom">
                                    <div class="course-one__author">
                                        <img src="{{asset('landing-page')}}/assets/images/course/author-4.png" alt="eduact">
                                        <h5 class="course-one__author__name">Jane Cooper</h5>
                                        <p class="course-one__author__designation">React JS Developer</p>
                                    </div>
                                    <div class="course-one__meta">
                                        <h4 class="course-one__meta__price">$739.65</h4>
                                        <p class="course-one__meta__class">15 Lessons</p>
                                    </div>
                                </div>
                            </div><!-- /.course-content -->
                        </div><!-- /.course-card-one -->
                    </div>
                    <div class="col-xl-4 col-lg-6 wow fadeInUp" data-wow-delay="500ms">
                        <div class="course-one__item">
                            <div class="course-one__thumb">
                                <img src="{{asset('landing-page')}}/assets/images/course/course-1-5.png" alt="eduact">
                                <a class="course-one__like" href="javascript:void(0);"><span class="icon-like"></span></a>
                            </div><!-- /.course-thumb -->
                            <div class="course-one__content">
                                <div class="course-one__time">12 Hours</div>
                                <div class="course-one__ratings">
                                    <span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span>
                                    <div class="course-one__ratings__reviews">(25 Reviews)</div>
                                </div>
                                <h3 class="course-one__title">
                                    <a href="digital-photography.html">Photography Crash Course for Beginners</a>
                                </h3>
                                <div class="course-one__bottom">
                                    <div class="course-one__author">
                                        <img src="{{asset('landing-page')}}/assets/images/course/author-5.png" alt="eduact">
                                        <h5 class="course-one__author__name">Wade Warren</h5>
                                        <p class="course-one__author__designation">Finance</p>
                                    </div>
                                    <div class="course-one__meta">
                                        <h4 class="course-one__meta__price">$351.02</h4>
                                        <p class="course-one__meta__class">13 Lessons</p>
                                    </div>
                                </div>
                            </div><!-- /.course-content -->
                        </div><!-- /.course-card-one -->
                    </div>
                    <div class="col-xl-4 col-lg-6 wow fadeInUp" data-wow-delay="600ms">
                        <div class="course-one__item">
                            <div class="course-one__thumb">
                                <img src="{{asset('landing-page')}}/assets/images/course/course-1-6.png" alt="eduact">
                                <a class="course-one__like" href="javascript:void(0);"><span class="icon-like"></span></a>
                            </div><!-- /.course-thumb -->
                            <div class="course-one__content">
                                <div class="course-one__time">33 Hours</div>
                                <div class="course-one__ratings">
                                    <span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span>
                                    <div class="course-one__ratings__reviews">(25 Reviews)</div>
                                </div>
                                <h3 class="course-one__title">
                                    <a href="online-business.html">Sales and Marketing For Online Businesses</a>
                                </h3>
                                <div class="course-one__bottom">
                                    <div class="course-one__author">
                                        <img src="{{asset('landing-page')}}/assets/images/course/author-6.png" alt="eduact">
                                        <h5 class="course-one__author__name">Guy Hawkins</h5>
                                        <p class="course-one__author__designation">Chief Executive Officer</p>
                                    </div>
                                    <div class="course-one__meta">
                                        <h4 class="course-one__meta__price">$475.22</h4>
                                        <p class="course-one__meta__class">13 Lessons</p>
                                    </div>
                                </div>
                            </div><!-- /.course-content -->
                        </div><!-- /.course-card-one -->
                    </div>
                </div>
            </div>
        </section>
        <!-- Course End -->

        <!-- Counter Start -->
        <section class="counter-one" style="background-image: linear-gradient(rgba(33, 75, 154, 0.88), rgba(33, 75, 154, 0.88)), url({{asset('landing-page')}}/assets/images/shapes/counter-bg-1.jpg);">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 wow fadeInLeft" data-wow-delay="200ms">
                        <div class="counter-one__left">
                            <h4 class="counter-one__left__title">Create Your Free Account</h4>
                            <div class="counter-one__left__content">
                                The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic.
                            </div>
                            <a href="contact.html" class="eduact-btn eduact-btn-second"><span class="eduact-btn__curve"></span>Join Now<i class="icon-arrow"></i></a>
                            <img src="{{asset('landing-page')}}/assets/images/shapes/counter-dot.png" alt="eduact">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="counter-one__shapes wow fadeInUp" data-wow-delay="200ms">
                            <svg viewBox="0 0 581 596" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M161.37 12.301C221.003 -53.0048 563.794 156.411 579.671 299.209C595.548 442.007 237.88 668.171 135.305 571.868C46.2938 488.252 -0.524429 189.658 161.37 12.301Z" fill="url(#paint0_linear_227_946)" />
                                <path d="M289.511 579.243C203.626 594.241 -34.778 302.771 4.28926 182.908C43.3565 63.0458 313.639 12.301 483.973 114.853C666.745 224.904 435.092 553.933 289.511 579.243Z" fill="url(#paint1_linear_227_946)" />
                                <defs>
                                    <linearGradient id="paint0_linear_227_946" x1="172.303" y1="27.9012" x2="521.418" y2="508.929" gradientUnits="userSpaceOnUse">
                                        <stop offset="0" stop-color="#214B9A" stop-opacity="0" />
                                        <stop offset="0.269374" stop-color="#214B9A" stop-opacity="0.550859" />
                                        <stop offset="1" stop-color="white" stop-opacity="0" />
                                    </linearGradient>
                                    <linearGradient id="paint1_linear_227_946" x1="123.876" y1="84.092" x2="408.261" y2="553.853" gradientUnits="userSpaceOnUse">
                                        <stop offset="0" stop-color="#E24A55" />
                                        <stop offset="1" stop-color="white" stop-opacity="0" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                        <div class="counter-one__area wow zoomIn" data-wow-delay="400ms">
                            <h5 class="counter-one__title">Register Now and<br> Get a <span>50% Discount</span></h5>
                            <ul class="counter-one__list list-unstyled" data-leading-zeros="true" data-enable-days="true" data-deadline-date="dynamicDate">
                                <!-- loading via js -->
                            </ul><!-- /.countdown-one__list list-unstyled -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Counter End -->
        <!-- Testimonial Start -->
        <section class="testimonial-one">
            <div class="container">
                <div class="section-title text-center">
                    <h5 class="section-title__tagline">
                        Testimonial
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
                    <h2 class="section-title__title">What Our Student Feedback</h2>
                </div><!-- section-title -->
                <div class="testimonial-one__area">
                    <div class="testimonial-one__carousel eduact-owl__carousel owl-with-shadow owl-theme owl-carousel" data-owl-options='{
                "items": 1,
                "margin": 0,
                "smartSpeed": 700,
                "loop":true,
                "autoplay": true,
                "nav":true,
                "dots":false,
                "navText": ["<span class=\"icon-arrow-left\"></span>","<span class=\"icon-arrow\"></span>"]
                }'>
                        <!-- Testimonial Item -->
                        <div class="item">
                            <div class="testimonial-one__item">
                                <div class="testimonial-one__author">
                                    <img src="{{asset('landing-page')}}/assets/images/resources/testimonial-1-author-1.png" alt="eduact">
                                </div><!-- testimonial-author -->
                                <div class="testimonial-one__content">
                                    <div class="testimonial-one__quote">
                                        Flexible Classes refers to the process of acquiring knowledge or skills through the use of digital technologies and the internet.
                                        Flexible Classes refers to the process flexible Classes refers to the process
                                    </div><!-- testimonial-quote -->
                                    <div class="testimonial-one__meta">
                                        <h5 class="testimonial-one__title">Savannah Nguyen</h5>
                                        <span class="testimonial-one__designation">UI/UX Designer</span>
                                    </div><!-- testimonial-meta -->
                                </div>
                            </div>
                        </div>
                        <!-- Testimonial Item -->
                        <!-- Testimonial Item -->
                        <div class="item">
                            <div class="testimonial-one__item">
                                <div class="testimonial-one__author">
                                    <img src="{{asset('landing-page')}}/assets/images/resources/testimonial-1-author-2.png" alt="eduact">
                                </div><!-- testimonial-author -->
                                <div class="testimonial-one__content">
                                    <div class="testimonial-one__quote">
                                        Flexible Classes refers to the process of acquiring knowledge or skills through the use of digital technologies and the internet.
                                        Flexible Classes refers to the process flexible Classes refers to the process
                                    </div><!-- testimonial-quote -->
                                    <div class="testimonial-one__meta">
                                        <h5 class="testimonial-one__title">Christine eve</h5>
                                        <span class="testimonial-one__designation">Web Developer</span>
                                    </div><!-- testimonial-meta -->
                                </div>
                            </div>
                        </div>
                        <!-- Testimonial Item -->
                        <!-- Testimonial Item -->
                        <div class="item">
                            <div class="testimonial-one__item">
                                <div class="testimonial-one__author">
                                    <img src="{{asset('landing-page')}}/assets/images/resources/testimonial-1-author-3.png" alt="eduact">
                                </div><!-- testimonial-author -->
                                <div class="testimonial-one__content">
                                    <div class="testimonial-one__quote">
                                        Flexible Classes refers to the process of acquiring knowledge or skills through the use of digital technologies and the internet.
                                        Flexible Classes refers to the process flexible Classes refers to the process
                                    </div><!-- testimonial-quote -->
                                    <div class="testimonial-one__meta">
                                        <h5 class="testimonial-one__title">Sarah Taylor</h5>
                                        <span class="testimonial-one__designation">Seo Expert</span>
                                    </div><!-- testimonial-meta -->
                                </div>
                            </div>
                        </div>
                        <!-- Testimonial Item -->
                    </div>
                    <div class="testimonial-one__thumb wow fadeInUp" data-wow-delay="200ms">
                        <img src="{{asset('landing-page')}}/assets/images/resources/testimonial-1.png" alt="eduact">
                        <svg viewBox="0 0 612 563" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M595.211 330.916C584.04 315.536 581.185 295.533 588.393 277.948C591.708 269.857 593.359 260.893 592.899 251.505C591.377 220.18 566.027 194.704 534.708 192.994C534.495 192.982 534.283 192.971 534.071 192.959C520.328 192.346 507.517 185.776 499.106 174.89C498.458 174.053 497.809 173.215 497.148 172.39C485.069 157.234 481.695 137.196 487.463 118.703C489.268 112.9 490.07 106.661 489.634 100.174C487.923 74.7337 467.02 54.3769 441.54 53.2801C426.665 52.6432 413.147 58.4459 403.521 68.129C386.44 85.3367 363.449 95.4207 339.207 95.1495C338.429 95.1377 337.638 95.1377 336.86 95.1377C332.79 95.1377 328.803 95.2674 321.631 94.4418C300.244 91.9768 280.473 82.2348 264.76 67.5039C244.483 48.4916 216.997 37.063 186.846 37.7471C129.15 39.0563 80.9264 88.6391 79.2159 146.325C77.9301 189.774 109.827 226.101 118.757 239.239C145.441 278.431 123.193 329.536 93.867 364.199C75.677 385.7 64.8598 413.652 65.3434 444.14C66.3697 509.161 119.548 562.153 184.581 562.99C207.784 563.285 229.501 557.046 248.021 545.995C285.428 523.668 327.033 509.161 370.031 502.167C393.635 498.323 415.883 490.456 436.043 479.275C458.739 466.69 485.128 461.914 510.702 466.337C516.117 467.268 521.708 467.705 527.429 467.575C573.659 466.537 611.16 428.513 611.584 382.279C611.773 363.067 605.663 345.316 595.211 330.916Z" fill="#214B9A" />
                            <path d="M103.524 314.265C122.402 295.39 122.402 264.788 103.524 245.913C84.6458 227.038 54.038 227.038 35.1597 245.913C16.2814 264.788 16.2814 295.39 35.1597 314.265C54.038 333.14 84.6458 333.14 103.524 314.265Z" fill="#214B9A" />
                            <path d="M519.408 173.899C529.715 173.899 538.07 165.546 538.07 155.241C538.07 144.936 529.715 136.582 519.408 136.582C509.101 136.582 500.746 144.936 500.746 155.241C500.746 165.546 509.101 173.899 519.408 173.899Z" fill="#214B9A" />
                            <path d="M404.941 42.6715C408.221 39.3921 408.221 34.0752 404.941 30.7958C401.661 27.5164 396.343 27.5164 393.063 30.7958C389.783 34.0752 389.783 39.3921 393.063 42.6715C396.343 45.9509 401.661 45.9509 404.941 42.6715Z" fill="#214B9A" />
                            <path d="M450.459 39.6567C457.747 32.3702 457.747 20.5565 450.459 13.27C443.171 5.98349 431.355 5.9835 424.067 13.27C416.78 20.5565 416.78 32.3703 424.067 39.6568C431.355 46.9433 443.171 46.9432 450.459 39.6567Z" fill="#214B9A" />
                            <path d="M469.475 508.914C476.947 508.914 483.005 502.857 483.005 495.386C483.005 487.914 476.947 481.858 469.475 481.858C462.002 481.858 455.944 487.914 455.944 495.386C455.944 502.857 462.002 508.914 469.475 508.914Z" fill="#214B9A" />
                            <path d="M341.696 525.638C343.481 525.638 344.929 524.191 344.929 522.406C344.929 520.621 343.481 519.175 341.696 519.175C339.911 519.175 338.464 520.621 338.464 522.406C338.464 524.191 339.911 525.638 341.696 525.638Z" fill="url(#paint0_linear_187_13357)" />
                        </svg>
                        <div class="testimonial-one__thumb-pen wow fadeInUp" data-wow-delay="400ms">
                            <img src="{{asset('landing-page')}}/assets/images/shapes/testimonial-shape-1.png" alt="eduact">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Testimonial End -->
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

    </div><!-- /.page-wrapper -->


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

    <!-- back-to-top-start -->
    <a href="#" class="scroll-top">
        <svg class="scroll-top__circle" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </a>
    <!-- back-to-top-end -->


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
