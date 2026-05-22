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

        $redirect = match (true) {
            $user?->hasRole('admin') => route('dashboard'),
            $user?->hasRole('peserta') => route('peserta.dashboard'),
            default => config('fortify.home'),
        };

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 200)
            : new RedirectResponse($redirect);
    }
}
