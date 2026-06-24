<?php

namespace App\Http\Controllers;

use App\Support\Roles;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Entry point /dashboard — arahkan ke beranda sesuai peran (admin / peserta).
 */
class HomeController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user?->hasRole(Roles::SUPER_ADMIN)) {
            return app(DashboardController::class)->index();
        }

        if ($user?->hasRole(Roles::PESERTA)) {
            return redirect()->route('peserta.dashboard');
        }

        return redirect()->route('profile');
    }
}
