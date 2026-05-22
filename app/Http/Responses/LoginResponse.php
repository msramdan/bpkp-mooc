<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        $hasAdmin = $user?->hasRole('admin');
        $hasPeserta = $user?->hasRole('peserta');

        $redirect = match (true) {
            $hasAdmin => route('dashboard'),
            $hasPeserta => route('peserta.dashboard'),
            default => route('profile'),
        };

        $response = $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 200)
            : new RedirectResponse($redirect);

        if (! $hasAdmin && ! $hasPeserta && ! $request->wantsJson()) {
            session()->flash('error', __('Akun Anda belum memiliki peran. Hubungi administrator.'));
        }

        return $response;
    }
}
