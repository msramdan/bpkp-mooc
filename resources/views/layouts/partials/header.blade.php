        <header class="app-header sticky" id="header">
            <div class="main-header-container container-fluid">
                <div class="header-content-left">
                    <div class="header-element">
                        <div class="horizontal-logo">
                            <a href="{{ route('dashboard') }}" class="header-logo">
                                <img src="{{ asset('backend') }}/assets/images/brand-logos/desktop-logo.png"
                                    alt="logo" class="desktop-logo">
                                <img src="{{ asset('backend') }}/assets/images/brand-logos/toggle-logo.png"
                                    alt="logo" class="toggle-logo">
                                <img src="{{ asset('backend') }}/assets/images/brand-logos/desktop-dark.png"
                                    alt="logo" class="desktop-dark">
                                <img src="{{ asset('backend') }}/assets/images/brand-logos/toggle-dark.png"
                                    alt="logo" class="toggle-dark">
                            </a>
                        </div>
                    </div>



                    <div class="header-element">
                        <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link" data-bs-toggle="sidebar"
                            href="javascript:void(0);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon menu-btn" width="32"
                                height="32" fill="#000000" viewBox="0 0 256 256">
                                <path
                                    d="M224,128a8,8,0,0,1-8,8H40a8,8,0,0,1,0-16H216A8,8,0,0,1,224,128ZM40,72H216a8,8,0,0,0,0-16H40a8,8,0,0,0,0,16ZM216,184H40a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16Z">
                                </path>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon menu-btn-close"
                                width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                                <path
                                    d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z">
                                </path>
                            </svg>
                        </a>
                    </div>



                </div>



                <ul class="header-content-right">
                    <li class="header-element d-md-none d-block">
                        <a href="javascript:void(0);" class="header-link" data-bs-toggle="modal"
                            data-bs-target="#header-responsive-search">

                            <i class="bi bi-search header-link-icon"></i>

                        </a>
                    </li>

                    <li class="header-element search-dropdown dropdown d-md-block d-none">

                        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside"
                            data-bs-toggle="dropdown">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" width="32"
                                height="32" fill="#000000" viewBox="0 0 256 256">
                                <path
                                    d="M228.24,219.76l-51.38-51.38a86.15,86.15,0,1,0-8.48,8.48l51.38,51.38a6,6,0,0,0,8.48-8.48ZM38,112a74,74,0,1,1,74,74A74.09,74.09,0,0,1,38,112Z">
                                </path>
                            </svg>
                        </a>
                        <ul class="main-header-dropdown dropdown-menu dropdown-menu-end overflow-visible"
                            data-popper-placement="none">
                            <li class="px-3 py-2">
                                <div class="header-element header-search d-md-block d-none my-auto">

                                    <input type="text" class="header-search-bar form-control" id="header-search"
                                        placeholder="Cari Kursus" spellcheck=false autocomplete="off"
                                        autocapitalize="off">
                                    <a href="javascript:void(0);" class="header-search-icon border-0">
                                        <i class="bi bi-search"></i>
                                    </a>

                                </div>
                            </li>
                        </ul>
                    </li>



                    <li class="header-element country-selector dropdown">

                        <a href="javascript:void(0);" class="header-link dropdown-toggle"
                            data-bs-auto-close="outside" data-bs-toggle="dropdown">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" viewBox="0 0 256 256">
                                <rect width="256" height="256" fill="none" />
                                <polyline points="240 216 184 104 128 216" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                <line x1="144" y1="184" x2="224" y2="184" fill="none"
                                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="16" />
                                <line x1="96" y1="32" x2="96" y2="56" fill="none"
                                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="16" />
                                <line x1="32" y1="56" x2="160" y2="56" fill="none"
                                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="16" />
                                <path d="M128,56a96,96,0,0,1-96,96" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                <path d="M69.47,88A96,96,0,0,0,160,152" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            </svg>
                        </a>

                        <ul class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between"
                                    href="javascript:void(0);">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-rounded avatar-xs lh-1 me-2">
                                            <img src="https://flagcdn.com/w40/id.png" width="24" height="18"
                                                alt="Bahasa" class="rounded">
                                        </span>
                                        Bahasa
                                    </div>
                                    <span class="text-muted fs-12">(ID)</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between"
                                    href="javascript:void(0);">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-rounded avatar-xs lh-1 me-2">
                                            <img src="{{ asset('backend') }}/assets/images/flags/us_flag.jpg"
                                                alt="English">
                                        </span>
                                        English
                                    </div>
                                    <span class="text-muted fs-12">(EN)</span>
                                </a>
                            </li>
                        </ul>
                    </li>



                    <li class="header-element header-theme-mode">

                        <a href="javascript:void(0);" class="header-link layout-setting">
                            <span class="light-layout">

                                <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon"
                                    viewBox="0 0 256 256">
                                    <rect width="256" height="256" fill="none" />
                                    <path d="M108.11,28.11A96.09,96.09,0,0,0,227.89,147.89,96,96,0,1,1,108.11,28.11Z"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                </svg>

                            </span>
                            <span class="dark-layout">

                                <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon"
                                    viewBox="0 0 256 256">
                                    <rect width="256" height="256" fill="none" />
                                    <line x1="128" y1="40" x2="128" y2="32"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                    <circle cx="128" cy="128" r="56" fill="none"
                                        stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="16" />
                                    <line x1="64" y1="64" x2="56" y2="56"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                    <line x1="64" y1="192" x2="56" y2="200"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                    <line x1="192" y1="64" x2="200" y2="56"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                    <line x1="192" y1="192" x2="200" y2="200"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                    <line x1="40" y1="128" x2="32" y2="128"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                    <line x1="128" y1="216" x2="128" y2="224"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                    <line x1="216" y1="128" x2="224" y2="128"
                                        fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="16" />
                                </svg>

                            </span>
                        </a>

                    </li>



                    <li class="header-element notifications-dropdown d-xl-block d-none dropdown">

                        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown"
                            data-bs-auto-close="outside" id="messageDropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon animate-bell"
                                viewBox="0 0 256 256">
                                <rect width="256" height="256" fill="none" />
                                <path d="M96,192a32,32,0,0,0,64,0" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                <path d="M184,24a102.71,102.71,0,0,1,36.29,40" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                <path d="M35.71,64A102.71,102.71,0,0,1,72,24" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                <path
                                    d="M56,112a72,72,0,0,1,144,0c0,35.82,8.3,56.6,14.9,68A8,8,0,0,1,208,192H48a8,8,0,0,1-6.88-12C47.71,168.6,56,147.81,56,112Z"
                                    fill="none" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="16" />
                            </svg>
                            <span class="header-icon-pulse bg-secondary rounded pulse pulse-secondary"></span>
                        </a>


                        <div class="main-header-dropdown dropdown-menu dropdown-menu-end"
                            data-popper-placement="none">
                            <div class="p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="mb-0 fs-16">Notifications</p>
                                    <a href="javascript:void(0);" class="badge bg-secondary-transparent"
                                        id="notifiation-data">3 Unread</a>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="pb-0 px-3">
                                <ul class="nav nav-tabs mb-0 tab-style-8 scaleX" role="tablist">
                                    <li class="nav-item" role="presentation"> <button class="nav-link active"
                                            id="activity-tab" data-bs-toggle="tab"
                                            data-bs-target="#activity-tab-pane" type="button" role="tab"
                                            aria-controls="activity-tab-pane" aria-selected="true">Activity</button>
                                    </li>
                                    <li class="nav-item" role="presentation"> <button class="nav-link"
                                            id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes-tab-pane"
                                            type="button" role="tab" aria-controls="notes-tab-pane"
                                            aria-selected="false" tabindex="-1">Notes</button>
                                    </li>
                                    <li class="nav-item" role="presentation"> <button class="nav-link"
                                            id="alert-tab" data-bs-toggle="tab" data-bs-target="#alert-tab-pane"
                                            type="button" role="tab" aria-controls="alert-tab-pane"
                                            aria-selected="false" tabindex="-1">Alerts</button>
                                    </li>
                                </ul>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="tab-content">
                                <div class="tab-pane show active p-0 border-0" id="activity-tab-pane" role="tabpanel"
                                    aria-labelledby="activity-tab" tabindex="0">
                                    <ul class="list-unstyled mb-0" id="header-notification-scroll1">
                                        <li class="dropdown-item">
                                            <div class="d-flex align-items-start">
                                                <div class="pe-2 lh-1"> <span
                                                        class="avatar avatar-md avatar-rounded svg-white">
                                                        <img src="{{ asset('backend') }}/assets/images/faces/2.jpg"
                                                            alt="img"> </span>
                                                </div>
                                                <div
                                                    class="flex-grow-1 d-flex align-items-start justify-content-between">
                                                    <div>
                                                        <p class="mb-0 fw-semibold">Way to go Jack Miller ! &#127881;
                                                        </p>
                                                        <div
                                                            class="fw-normal fs-13 header-notification-text text-truncate">
                                                            Congratulations on snagging the Monthly Best Seller Gold
                                                            Badge !</div>
                                                        <span class="text-muted fs-12">2 Min
                                                            Ago</span>
                                                    </div>
                                                    <div> <a href="javascript:void(0);"
                                                            class="min-w-fit-content text-muted dropdown-item-close1"><i
                                                                class="ri-close-line fs-5"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-pane p-0 border-0" id="notes-tab-pane" role="tabpanel"
                                    aria-labelledby="notes-tab" tabindex="0">
                                    <ul class="list-unstyled mb-0" id="header-notification-scroll2">
                                        <li class="dropdown-item">
                                            <div class="d-flex align-items-start">
                                                <div class="pe-2 lh-1"> <span
                                                        class="avatar avatar-md avatar-rounded bg-primary">
                                                        <i class="ri-file-text-line fs-16"></i> </span>
                                                </div>
                                                <div
                                                    class="flex-grow-1 d-flex align-items-start justify-content-between">
                                                    <div>
                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);">This
                                                                Month Notes
                                                                Prepared</a></p>
                                                        <div
                                                            class="fw-normal fs-13 header-notification-text text-truncate">
                                                            Your notes for this month are ready and available. Please
                                                            review them at your convenience.</div>
                                                        <span class="fs-13 text-muted">2 min ago</span>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);"
                                                            class="min-w-fit-content text-muted dropdown-item-close1"><i
                                                                class="ri-close-line fs-5"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-pane p-0 border-0" id="alert-tab-pane" role="tabpanel"
                                    aria-labelledby="alert-tab" tabindex="0">
                                    <ul class="list-unstyled mb-0" id="header-notification-scroll3">
                                        <li class="dropdown-item">
                                            <div class="d-flex align-items-start">
                                                <div class="pe-2 lh-1"> <span
                                                        class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                                        <i class="ri-mail-line fs-16"></i> </span>
                                                </div>
                                                <div
                                                    class="flex-grow-1 d-flex align-items-start justify-content-between">
                                                    <div>
                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);">Mail
                                                                Login with
                                                                Different Device</a></p>
                                                        <div
                                                            class="fw-normal fs-13 header-notification-text text-truncate">
                                                            Your email account has been accessed from a new device. If
                                                            this was you, no action is needed. </div>
                                                        <span class="fs-13 text-muted">2 min ago</span>
                                                    </div>
                                                    <div> <a href="javascript:void(0);"
                                                            class="min-w-fit-content text-muted dropdown-item-close1"><i
                                                                class="ri-close-line fs-5"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="p-3 empty-header-item1 border-top">
                                <div class="d-grid text-center">
                                    <a href="{{ asset('backend') }}/pages/checkout.html"
                                        class="text-primary text-decoration-underline">View All<i
                                            class="ri-arrow-right-line"></i></a>
                                </div>
                            </div>
                            <div class="p-5 empty-item1 d-none">
                                <div class="text-center">
                                    <span class="avatar avatar-xl avatar-rounded bg-secondary-transparent">
                                        <i class="ri-notification-off-line fs-2"></i>
                                    </span>
                                    <h6 class="fw-medium mt-3">No New Notifications</h6>
                                </div>
                            </div>
                        </div>

                    </li>



                    <li class="header-element header-fullscreen">

                        <a onclick="openFullscreen();" href="javascript:void(0);" class="header-link">
                            <svg xmlns="http://www.w3.org/2000/svg" class="full-screen-open header-link-icon"
                                viewBox="0 0 256 256">
                                <rect width="256" height="256" fill="none" />
                                <polyline points="168 48 208 48 208 88" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                <polyline points="88 208 48 208 48 168" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                <polyline points="208 168 208 208 168 208" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                <polyline points="48 88 48 48 88 48" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="full-screen-close header-link-icon d-none"
                                viewBox="0 0 256 256">
                                <rect width="256" height="256" fill="none" />
                                <polyline points="160 48 208 48 208 96" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                <line x1="144" y1="112" x2="208" y2="48" fill="none"
                                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="16" />
                                <polyline points="96 208 48 208 48 160" fill="none" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="16" />
                                <line x1="112" y1="144" x2="48" y2="208" fill="none"
                                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="16" />
                            </svg>
                        </a>

                    </li>



                    @auth
                    <li class="header-element dropdown">

                        <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="me-xl-2 me-0">
                                    <img src="{{ asset('backend') }}/assets/images/faces/2.jpg" alt=""
                                        class="avatar avatar-sm avatar-rounded">
                                </div>
                                <div class="d-xl-block d-none lh-1">
                                    <span class="fw-medium lh-1">{{ str(auth()->user()->name)->limit(24) }}</span>
                                </div>
                            </div>
                        </a>

                        <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                            aria-labelledby="mainHeaderProfile">
                            <li>
                                <div class="py-2 px-3 text-center">
                                    <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                                    @if (auth()->user()->email)
                                        <span class="d-block fs-12 text-muted text-truncate">{{ auth()->user()->email }}</span>
                                    @endif
                                </div>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{ route('profile') }}">
                                    <i class="ti ti-user text-primary me-2 fs-16"></i>{{ __('Profile') }}
                                </a>
                            </li>
                            <li class="py-2 px-3">
                                <form method="POST" action="{{ route('logout') }}" class="d-grid">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('Log Out') }}</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endauth





                </ul>


            </div>


        </header>

        <div class="modal fade" id="header-responsive-search" tabindex="-1"
            aria-labelledby="header-responsive-search" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="input-group">
                            <input type="text" class="form-control border-end-0" placeholder="Cari Kursus"
                                aria-label="Cari Kursus" aria-describedby="button-addon2">
                            <button class="btn btn-primary" type="button" id="button-addon2"><i
                                    class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
