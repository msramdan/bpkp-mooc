<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    public function view(User $user, Certificate $certificate): bool
    {
        if ($user->can('course view')) {
            return true;
        }

        return $user->hasRole('peserta')
            && $user->can('peserta sertifikat view')
            && (int) $certificate->user_id === (int) $user->id;
    }
}
