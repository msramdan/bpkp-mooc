                        <li class="slide__category"><span class="category-name">{{ __('Menu Utama') }}</span></li>

                        @auth
                            <li class="slide{{ request()->routeIs('dashboard') ? ' active' : '' }}">
                                <a href="{{ route('dashboard') }}"
                                    class="side-menu__item{{ request()->routeIs('dashboard') ? ' active' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                                        <rect width="256" height="256" fill="none" />
                                        <path
                                            d="M104,216V152h48v64h64V120a8,8,0,0,0-2.34-5.66l-80-80a8,8,0,0,0-11.32,0l-80,80A8,8,0,0,0,40,120v96Z"
                                            fill="none" stroke="currentColor" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="16" />
                                    </svg>
                                    <span class="side-menu__label">{{ __('Dashboard') }}</span>
                                </a>
                            </li>
                        @endauth

                        @foreach (config('generator.sidebars', []) as $sidebar)
                            @if (isset($sidebar['permissions']))
                                @canany($sidebar['permissions'])
                                    @foreach ($sidebar['menus'] as $menu)
                                        @php
                                            $permissions = empty($menu['permission'])
                                                ? $menu['permissions']
                                                : [$menu['permission']];
                                            $subOpen = false;
                                            if (! empty($menu['submenus'])) {
                                                foreach ($menu['submenus'] as $sub) {
                                                    if (trim(is_active_menu($sub['route'] ?? '')) !== '') {
                                                        $subOpen = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp

                                        @canany($permissions)
                                            @if (empty($menu['submenus']))
                                                @can($menu['permission'])
                                                    <li class="slide{{ is_active_menu($menu['route']) }}">
                                                        <a href="{{ route(str($menu['route'])->remove('/')->singular()->plural() . '.index') }}"
                                                            class="side-menu__item{{ is_active_menu($menu['route']) }}">
                                                            <span class="side-menu__icon">{!! $menu['icon'] !!}</span>
                                                            <span class="side-menu__label">{{ __($menu['title']) }}</span>
                                                        </a>
                                                    </li>
                                                @endcan
                                            @else
                                                <li
                                                    class="slide has-sub{{ $subOpen ? ' open' : '' }}{{ is_active_menu($menu['permissions']) }}">
                                                    <a href="javascript:void(0);"
                                                        class="side-menu__item{{ $subOpen ? ' active' : '' }}">
                                                        <span class="side-menu__icon">{!! $menu['icon'] !!}</span>
                                                        <span class="side-menu__label">{{ __($menu['title']) }}</span>
                                                        <i class="ri-arrow-right-s-line side-menu__angle"></i>
                                                    </a>
                                                    <ul class="slide-menu child1">
                                                        <li class="slide side-menu__label1">
                                                            <a href="javascript:void(0)">{{ __($menu['title']) }}</a>
                                                        </li>
                                                        @canany($menu['permissions'])
                                                            @foreach ($menu['submenus'] as $submenu)
                                                                @can($submenu['permission'])
                                                                    <li class="slide{{ is_active_menu($submenu['route']) }}">
                                                                        <a href="{{ route(str($submenu['route'])->remove('/')->singular()->plural() . '.index') }}"
                                                                            class="side-menu__item{{ is_active_menu($submenu['route']) }}">
                                                                            {{ __($submenu['title']) }}
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                            @endforeach
                                                        @endcanany
                                                    </ul>
                                                </li>
                                            @endif
                                        @endcanany
                                    @endforeach
                                @endcanany
                            @endif
                        @endforeach
