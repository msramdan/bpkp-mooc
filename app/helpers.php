<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('menu_route_is_active')) {
    function menu_route_is_active(string $routeName): bool
    {
        if (request()->routeIs($routeName)) {
            return true;
        }

        if (preg_match('/^(.+)\.(index|show|create|edit|update|destroy)$/', $routeName, $matches)) {
            return request()->routeIs($matches[1].'.*');
        }

        return request()->routeIs($routeName.'.*');
    }
}

if (! function_exists('is_active_menu')) {
    /**
     * Return CSS class suffix for active sidebar item.
     *
     * @param  string|array<int, string>|null  $routeOrPermissions
     */
    function is_active_menu(string|array|null $routeOrPermissions): string
    {
        if ($routeOrPermissions === null || $routeOrPermissions === '') {
            return '';
        }

        if (is_array($routeOrPermissions)) {
            foreach ($routeOrPermissions as $item) {
                if (is_string($item) && str_contains($item, '.') && menu_route_is_active($item)) {
                    return ' active';
                }
            }

            return '';
        }

        if (str_contains($routeOrPermissions, '.')) {
            return menu_route_is_active($routeOrPermissions) ? ' active' : '';
        }

        $routeBase = str($routeOrPermissions)->remove('/')->singular()->plural()->toString();

        return request()->routeIs($routeBase) || request()->routeIs($routeBase.'.*')
            ? ' active'
            : '';
    }
}

if (! function_exists('user_home_route')) {
    function user_home_route(): string
    {
        $user = Auth::user();

        if ($user?->hasRole('admin')) {
            return route('dashboard');
        }

        if ($user?->hasRole('peserta')) {
            return route('peserta.dashboard');
        }

        return route('profile');
    }
}
