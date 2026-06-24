<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CertificateService
{
    public function issueForCompletedEnrollment(User $user, Course $course, CourseEnrollment $enrollment): ?Certificate
    {
        if ($enrollment->progress < 100) {
            return null;
        }

        return DB::transaction(function () use ($user, $course, $enrollment): Certificate {
            $existing = Certificate::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            try {
                return Certificate::query()->create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'nomor' => $this->generateNomor(),
                    'nilai_akhir' => min(100, max(0, (int) $enrollment->progress)),
                    'issued_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                $existing = Certificate::query()
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->first();

                if ($existing) {
                    return $existing;
                }

                return Certificate::query()->create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'nomor' => $this->generateNomor(),
                    'nilai_akhir' => min(100, max(0, (int) $enrollment->progress)),
                    'issued_at' => now(),
                ]);
            }
        });
    }

    public function issueWithNomor(
        User $user,
        Course $course,
        string $nomor,
        Carbon $issuedAt,
        int $nilaiAkhir = 100,
    ): Certificate {
        return DB::transaction(function () use ($user, $course, $nomor, $issuedAt, $nilaiAkhir): Certificate {
            $existing = Certificate::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $nomor = trim($nomor) !== '' ? trim($nomor) : $this->generateNomor();

            try {
                return Certificate::query()->create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'nomor' => $nomor,
                    'nilai_akhir' => min(100, max(0, $nilaiAkhir)),
                    'issued_at' => $issuedAt,
                ]);
            } catch (UniqueConstraintViolationException) {
                return Certificate::query()
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->firstOrFail();
            }
        });
    }

    private function generateNomor(): string
    {
        $year = now()->year;
        $month = now()->format('m');

        $last = Certificate::query()
            ->whereYear('issued_at', $year)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('nomor');

        $sequence = 1;

        if (is_string($last) && preg_match('/SERT-(\d+)\//', $last, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('SERT-%05d/MOOC/%s/%d', $sequence, $month, $year);
    }
}
