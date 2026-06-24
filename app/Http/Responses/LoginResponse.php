<?php

namespace App\Http\Responses;

use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        $hasSuperAdmin = $user?->hasRole(Roles::SUPER_ADMIN);
        $hasPeserta = $user?->hasRole(Roles::PESERTA);

        if (! $hasSuperAdmin && ! $hasPeserta) {
            $redirect = route('profile');
            $response = $request->wantsJson()
                ? new JsonResponse(['two_factor' => false, 'redirect' => $redirect], 200)
                : new RedirectResponse($redirect);

            if (! $request->wantsJson()) {
                session()->flash('error', __('Akun Anda belum memiliki peran. Hubungi administrator.'));
            }

            return $response;
        }

        $redirect = route('dashboard');

        $response = $request->wantsJson()
            ? new JsonResponse(['two_factor' => false, 'redirect' => $redirect], 200)
            : new RedirectResponse($redirect);

        return $response;
    }
}
