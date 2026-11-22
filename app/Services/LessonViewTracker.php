<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LessonCompletion;
use App\Models\LessonViewProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Penyelesaian otomatis materi video & dokumen berdasarkan progres yang divalidasi server.
 *
 * Video (file upload, YouTube, Vimeo):
 *  - Peserta tidak bisa melompat ke bagian yang belum ditonton (dicegah di player).
 *  - Server hanya menerima kemajuan posisi sebesar waktu nyata yang berlalu (+ toleransi kecil),
 *    sehingga lompatan lewat request palsu ditolak.
 *  - Selesai bila posisi terjauh mencapai akhir video DAN waktu sejak mulai menonton >= 85% durasi.
 *
 * Dokumen (PDF di server sendiri & gambar):
 *  - Selesai bila seluruh halaman sudah ditampilkan, dengan waktu baca minimal per halaman.
 *
 * Materi lain (Word/PPT, tautan eksternal, H5P, dll.) tetap memakai tombol "Tandai selesai".
 */
class LessonViewTracker
{
    /** Toleransi kemajuan posisi per heartbeat: elapsed x RATE + SLACK detik. */
    private const ADVANCE_RATE = 1.25;

    private const ADVANCE_SLACK = 3.0;

    /** Toleransi akhir video (detik) untuk dianggap selesai. */
    private const END_TOLERANCE = 2.0;

    /** Waktu tonton (dikredit server) minimum dibanding durasi video. */
    private const MIN_WATCH_RATIO = 0.85;

    /** Kredit waktu maksimum per heartbeat (klien mengirim tiap ~5 detik). */
    private const MAX_CREDIT_PER_BEAT = 15.0;

    /** Heartbeat lebih rapat dari ini diabaikan (anti-banjir). */
    public const MIN_BEAT_INTERVAL = 2.0;

    /** Detik minimum per halaman dokumen (total). */
    private const SECONDS_PER_PAGE = 2;

    private const MAX_DOC_SECONDS = 300;

    public const MAX_VIDEO_SECONDS = 6 * 3600;

    public const MAX_PAGES = 2000;

    public function __construct(private readonly LearningProgressService $progress) {}

    /** 'video' | 'document' | null (tidak dilacak). */
    public function trackingKind(CourseLesson $lesson): ?string
    {
        return match ($lesson->normalizedType()) {
            'video' => $this->videoPlayer($lesson) !== null ? 'video' : null,
            'berkas' => $this->documentKind($lesson) !== null ? 'document' : null,
            default => null,
        };
    }

    /** 'html5' | 'youtube' | 'vimeo' | null */
    public function videoPlayer(CourseLesson $lesson): ?string
    {
        if ($lesson->normalizedType() !== 'video') {
            return null;
        }

        if ($lesson->isStreamableVideo()) {
            return 'html5';
        }

        $embed = (string) $lesson->embedVideoUrl();

        return match (true) {
            str_contains($embed, 'youtube.com/embed/') => 'youtube',
            str_contains($embed, 'player.vimeo.com/video/') => 'vimeo',
            default => null,
        };
    }

    /** 'pdf' | 'image' | null — hanya berkas yang disimpan di server sendiri. */
    public function documentKind(CourseLesson $lesson): ?string
    {
        if ($lesson->normalizedType() !== 'berkas') {
            return null;
        }

        $raw = trim((string) ($lesson->file_url ?: $lesson->video_url));
        if ($raw === '') {
            return null;
        }

        $isLocal = ! preg_match('#^https?://#i', $raw) || str_contains($raw, '/storage/');
        if (! $isLocal) {
            return null; // PDF di host lain tidak bisa dibaca pdf.js (CORS) -> tetap manual.
        }

        $ext = strtolower(pathinfo(parse_url($raw, PHP_URL_PATH) ?? $raw, PATHINFO_EXTENSION));

        return match (true) {
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true) => 'image',
            default => null,
        };
    }

    public function isCompleted(User $user, CourseLesson $lesson): bool
    {
        return LessonViewProgress::query()
            ->where('user_id', $user->id)
            ->where('course_lesson_id', $lesson->id)
            ->whereNotNull('completed_at')
            ->exists()
            || LessonCompletion::query()->where('user_id', $user->id)->where('course_lesson_id', $lesson->id)->exists();
    }

    /** State untuk dikirim ke player. */
    public function state(User $user, CourseLesson $lesson): array
    {
        $row = LessonViewProgress::query()
            ->where('user_id', $user->id)
            ->where('course_lesson_id', $lesson->id)
            ->first();

        $completedByLesson = LessonCompletion::query()->where('user_id', $user->id)->where('course_lesson_id', $lesson->id)->exists();

        return $this->payload($lesson, $row, $completedByLesson);
    }

    /**
     * Terima heartbeat dari player. Semua angka dari klien dianggap tidak tepercaya.
     *
     * @param  array{position?:mixed, duration?:mixed, pages?:mixed, total_pages?:mixed}  $input
     */
    public function heartbeat(User $user, Course $course, CourseLesson $lesson, array $input): array
    {
        $kind = $this->trackingKind($lesson);
        abort_if($kind === null, 404);

        // Baris dibuat di luar transaksi (hindari deadlock insert + FOR UPDATE paralel).
        $exists = LessonViewProgress::query()->where('user_id', $user->id)->where('course_lesson_id', $lesson->id)->exists();
        if (! $exists) {
            LessonViewProgress::query()->insertOrIgnore([
                'user_id' => $user->id,
                'course_lesson_id' => $lesson->id,
                'kind' => $kind,
                'max_position' => 0,
                'last_position' => 0,
                'pages_seen_count' => 0,
                'heartbeats' => 0,
                'rejected_jumps' => 0,
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $justCompleted = false;

        $row = DB::transaction(function () use ($user, $lesson, $kind, $input, &$justCompleted): LessonViewProgress {
            /** @var LessonViewProgress $row */
            $row = LessonViewProgress::query()
                ->where('user_id', $user->id)
                ->where('course_lesson_id', $lesson->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($row->completed_at !== null) {
                return $row;
            }

            $now = now();

            // Heartbeat beruntun (< 2 detik) diabaikan tanpa mengubah apa pun, kecuali sinyal "ended".
            if ($kind === 'video' && $row->last_heartbeat_at !== null && empty($input['ended'])
                && $row->last_heartbeat_at->diffInMilliseconds($now) < self::MIN_BEAT_INTERVAL * 1000) {
                return $row;
            }

            $row->heartbeats++;

            if ($kind === 'video') {
                $this->applyVideo($row, $input, $now);
            } else {
                $this->applyDocument($row, $lesson, $input);
            }

            $row->last_heartbeat_at = $now;

            if ($this->meetsCompletion($row, $now)) {
                $row->completed_at = $now;
                $justCompleted = true;
            }

            $row->save();

            return $row;
        }, 3);

        if ($justCompleted || $row->completed_at !== null) {
            // Idempoten (unique + lock enrollment di LearningProgressService).
            $this->progress->completeLesson($user, $course, $lesson);
        }

        return $this->payload($lesson, $row->fresh(), $row->completed_at !== null) + ['just_completed' => $justCompleted];
    }

    private function applyVideo(LessonViewProgress $row, array $input, $now): void
    {
        $position = $this->number($input['position'] ?? null, 0, self::MAX_VIDEO_SECONDS);
        $duration = $this->number($input['duration'] ?? null, 1, self::MAX_VIDEO_SECONDS);

        // Durasi dikunci pada laporan valid pertama; perubahan besar diabaikan.
        if ($row->duration_seconds === null && $duration !== null) {
            $row->duration_seconds = round($duration, 2);
        }

        if ($position === null) {
            return;
        }

        $cap = $row->duration_seconds !== null ? (float) $row->duration_seconds : self::MAX_VIDEO_SECONDS;
        $position = min($cap, $position);

        // Heartbeat pertama hanya menjadi titik awal (tanpa kredit waktu).
        if ($row->last_heartbeat_at === null) {
            $row->last_position = round($position <= self::ADVANCE_SLACK ? $position : 0, 2);
            $row->max_position = round(max((float) $row->max_position, (float) $row->last_position), 2);

            return;
        }

        // Kredit waktu nyata dibatasi agar diam lama / heartbeat beruntun tidak bisa dipakai melompat.
        $elapsed = min(self::MAX_CREDIT_PER_BEAT, max(0.0, (float) $row->last_heartbeat_at->diffInMilliseconds($now) / 1000));
        $allowedMax = (float) $row->max_position + ($elapsed * self::ADVANCE_RATE) + self::ADVANCE_SLACK;

        if ($position > $allowedMax) {
            // Lompatan / percepatan tidak wajar: tidak dihitung, hanya kemajuan wajar yang dikredit.
            $row->rejected_jumps++;
            $position = $allowedMax - self::ADVANCE_SLACK;
        }

        // Waktu tonton dikredit hanya bila video benar-benar berjalan (posisi berubah).
        if (abs($position - (float) $row->last_position) >= 0.5) {
            $row->watched_seconds = round((float) $row->watched_seconds + min($elapsed, abs($position - (float) $row->last_position) + 1), 2);
        }

        $row->max_position = round(min($cap, max((float) $row->max_position, $position)), 2);
        $row->last_position = round($position, 2);
    }

    private function applyDocument(LessonViewProgress $row, CourseLesson $lesson, array $input): void
    {
        $isImage = $this->documentKind($lesson) === 'image';
        $total = $isImage ? 1 : $this->integer($input['total_pages'] ?? null, 1, self::MAX_PAGES);

        if ($row->total_pages === null && $total !== null) {
            $row->total_pages = $total;
        }

        if ($row->total_pages === null) {
            return;
        }

        $pages = $isImage ? [1] : (is_array($input['pages'] ?? null) ? $input['pages'] : []);
        $seen = collect($row->pages_seen ?? []);

        foreach (array_slice($pages, 0, 200) as $page) {
            $p = $this->integer($page, 1, (int) $row->total_pages);
            if ($p !== null) {
                $seen->push($p);
            }
        }

        $seen = $seen->unique()->sort()->values();
        $row->pages_seen = $seen->all();
        $row->pages_seen_count = $seen->count();
    }

    private function meetsCompletion(LessonViewProgress $row, $now): bool
    {
        $wallclock = (float) (($row->started_at ?? $now)->diffInMilliseconds($now)) / 1000;

        if ($row->kind === 'video') {
            if ($row->duration_seconds === null || $row->duration_seconds <= 0) {
                return false;
            }

            return (float) $row->max_position >= (float) $row->duration_seconds - self::END_TOLERANCE
                && (float) $row->watched_seconds >= (float) $row->duration_seconds * self::MIN_WATCH_RATIO;
        }

        if (! $row->total_pages) {
            return false;
        }

        $minSeconds = min(self::MAX_DOC_SECONDS, (int) $row->total_pages * self::SECONDS_PER_PAGE);

        return $row->pages_seen_count >= $row->total_pages && $wallclock >= $minSeconds;
    }

    private function payload(CourseLesson $lesson, ?LessonViewProgress $row, bool $completed): array
    {
        $kind = $this->trackingKind($lesson);
        $percent = 0;

        if ($row) {
            if ($kind === 'video' && $row->duration_seconds) {
                $percent = (int) floor(min(100, ((float) $row->max_position / (float) $row->duration_seconds) * 100));
            } elseif ($kind === 'document' && $row->total_pages) {
                $percent = (int) floor(min(100, ($row->pages_seen_count / $row->total_pages) * 100));
            }
        }

        if ($completed) {
            $percent = 100;
        }

        return [
            'kind' => $kind,
            'player' => $kind === 'video' ? $this->videoPlayer($lesson) : $this->documentKind($lesson),
            'completed' => $completed,
            'percent' => $percent,
            'max_position' => (float) ($row->max_position ?? 0),
            'duration' => $row?->duration_seconds,
            'pages_seen' => $row?->pages_seen ?? [],
            'total_pages' => $row?->total_pages,
        ];
    }

    private function number(mixed $value, float $min, float $max): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $f = (float) $value;

        return is_finite($f) && $f >= $min && $f <= $max ? $f : null;
    }

    private function integer(mixed $value, int $min, int $max): ?int
    {
        $i = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);

        return $i === false ? null : $i;
    }
}
