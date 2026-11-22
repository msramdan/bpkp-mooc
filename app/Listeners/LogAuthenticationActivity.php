<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;
use Throwable;

/**
 * Catat login, logout, login gagal, lockout, dan reset password.
 * Password tidak pernah dicatat; login gagal hanya menyimpan email yang dicoba.
 */
class LogAuthenticationActivity
{
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'onLogin',
            Logout::class => 'onLogout',
            Failed::class => 'onFailed',
            Lockout::class => 'onLockout',
            PasswordReset::class => 'onPasswordReset',
        ];
    }

    public function onLogin(Login $event): void
    {
        $this->log($event->user, 'login', 'Login ke aplikasi', ['remember' => $event->remember]);
    }

    public function onLogout(Logout $event): void
    {
        $this->log($event->user, 'logout', 'Logout dari aplikasi');
    }

    public function onFailed(Failed $event): void
    {
        $email = mb_substr((string) ($event->credentials['email'] ?? ''), 0, 120);

        // Jika email terdaftar, catat atas nama user tsb agar terlihat di riwayatnya (percobaan login ke akunnya).
        $this->log($event->user, 'failed', 'Login gagal', ['email' => $email]);
    }

    public function onLockout(Lockout $event): void
    {
        $email = mb_substr((string) $event->request->input('email', ''), 0, 120);
        $user = $email !== '' ? User::query()->where('email', $email)->first() : null;

        $this->log($user, 'lockout', 'Login diblokir sementara (terlalu banyak percobaan)', ['email' => $email]);
    }

    public function onPasswordReset(PasswordReset $event): void
    {
        $this->log($event->user, 'password_reset', 'Reset password');
    }

    private function log(mixed $user, string $event, string $description, array $properties = []): void
    {
        try {
            $logger = activity('auth')->event($event)->withProperties($properties);
            $user instanceof User ? $logger->causedBy($user) : $logger->causedByAnonymous();
            $logger->log($description);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
