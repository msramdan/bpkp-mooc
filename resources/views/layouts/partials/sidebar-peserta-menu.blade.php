                        <li class="slide__category"><span class="category-name">{{ __('Portal Peserta') }}</span></li>

                        @foreach (config('peserta.sidebars', []) as $sidebar)
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
                                                    <a href="{{ route($menu['route']) }}"
                                                        class="side-menu__item{{ is_active_menu($menu['route']) }}">
                                                        <span class="side-menu__icon">{!! $menu['icon'] !!}</span>
                                                        <span class="side-menu__label">{{ __($menu['title']) }}</span>
                                                    </a>
                                                </li>
                                            @endcan
                                        @else
                                            <li
                                                class="slide has-sub{{ $subOpen ? ' open active' : '' }}{{ is_active_menu(collect($menu['submenus'])->pluck('route')->filter()->all()) }}">
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
                                                                    <a href="{{ route($submenu['route']) }}"
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
                        @endforeach
