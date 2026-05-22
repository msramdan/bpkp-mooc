<?php

use Illuminate\Support\Facades\Auth;

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
            foreach ($routeOrPermissions as $permission) {
                if (is_string($permission) && str_contains($permission, '.')) {
                    if (request()->routeIs($permission) || request()->routeIs($permission.'.*')) {
                        return ' active';
                    }
                }
            }

            return '';
        }

        if (str_contains($routeOrPermissions, '.')) {
            return request()->routeIs($routeOrPermissions) || request()->routeIs($routeOrPermissions.'.*')
                ? ' active'
                : '';
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

        return route('dashboard');
    }
}
