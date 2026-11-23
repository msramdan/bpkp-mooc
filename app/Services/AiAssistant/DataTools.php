<?php

namespace App\Services\AiAssistant;

use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\LessonCompletion;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\SurveyScoring;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Fungsi data yang di-whitelist untuk AI (read-only).
 *
 * - User SELALU berasal dari sesi login (parameter $user dari backend), tidak pernah
 *   dari input model / frontend. Tidak ada parameter user_id di skema tool.
 * - Tidak ada SQL bebas: setiap fungsi memakai query Eloquent tetap.
 * - Hanya field yang dibutuhkan untuk menjawab yang dikirim ke model.
 */
class DataTools
{
    public const DATA_CATEGORY = [
        'get_my_profile' => 'profil',
        'list_my_courses' => 'kursus',
        'get_my_course_detail' => 'progres_materi',
        'list_my_certificates' => 'sertifikat',
        'list_my_pending_tasks' => 'tugas',
        'list_my_quiz_results' => 'nilai',
        'search_course_catalog' => 'katalog_publik',
        'get_mooc_help' => 'panduan',
    ];

    private const HELP_TOPICS = [
        'beranda', 'kursus_saya', 'katalog', 'materi_dan_progres', 'kuis_dan_nilai',
        'penugasan', 'forum', 'sertifikat', 'ai_assistant',
    ];

    public function __construct(
        private readonly AiAssistantSettings $settings,
        private readonly QuotaManager $quota,
    ) {}

    /** Definisi tool untuk Anthropic Messages API (format SDK PHP, camelCase). */
    public function definitions(): array
    {
        // Tool "tanpa argumen" tetap punya 1 parameter tetap agar input tidak pernah objek kosong
        // (SDK PHP mengirim objek kosong sebagai [] yang ditolak API).
        $none = [
            'type' => 'object',
            'properties' => [
                'scope' => ['type' => 'string', 'enum' => ['me'], 'description' => 'Selalu "me" (data milik peserta yang sedang login).'],
            ],
            'required' => ['scope'],
            'additionalProperties' => false,
        ];

        return [
            [
                'name' => 'get_my_profile',
                'description' => 'Ambil profil akun peserta yang sedang login (nama, jabatan, unit organisasi). NIP ditampilkan tersamar.',
                'inputSchema' => $none,
                'strict' => true,
            ],
            [
                'name' => 'list_my_courses',
                'description' => 'Daftar kursus yang diikuti peserta yang sedang login beserta status, progres (%), jumlah topik selesai, dan tenggat.',
                'inputSchema' => $none,
                'strict' => true,
            ],
            [
                'name' => 'get_my_course_detail',
                'description' => 'Detail progres satu kursus yang diikuti peserta: daftar topik & materi, mana yang sudah/belum selesai, dan materi berikutnya. Parameter course berisi kode atau sebagian judul kursus.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'course' => ['type' => 'string', 'description' => 'Kode kursus atau kata kunci judul kursus yang diikuti peserta.'],
                    ],
                    'required' => ['course'],
                    'additionalProperties' => false,
                ],
                'strict' => true,
            ],
            [
                'name' => 'list_my_certificates',
                'description' => 'Daftar sertifikat milik peserta yang sedang login, serta kursus yang sudah 100% tetapi sertifikatnya belum diterbitkan.',
                'inputSchema' => $none,
                'strict' => true,
            ],
            [
                'name' => 'list_my_pending_tasks',
                'description' => 'Hal yang perlu diselesaikan peserta: materi wajib berikutnya, penugasan yang belum dikumpulkan, kuis/survei yang belum dikerjakan, dan tenggat kursus terdekat.',
                'inputSchema' => $none,
                'strict' => true,
            ],
            [
                'name' => 'list_my_quiz_results',
                'description' => 'Hasil kuis/pre test/post test yang sudah dikirim peserta yang sedang login (skor 0-100).',
                'inputSchema' => $none,
                'strict' => true,
            ],
            [
                'name' => 'search_course_catalog',
                'description' => 'Cari kursus publik yang tersedia di katalog MOOC. Parameter keyword boleh string kosong untuk menampilkan kursus terbaru.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'keyword' => ['type' => 'string', 'description' => 'Kata kunci judul/kategori/kode kursus, atau string kosong.'],
                    ],
                    'required' => ['keyword'],
                    'additionalProperties' => false,
                ],
                'strict' => true,
            ],
            [
                'name' => 'get_mooc_help',
                'description' => 'Panduan resmi cara memakai fitur MOOC BPKP.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'topic' => ['type' => 'string', 'enum' => self::HELP_TOPICS],
                    ],
                    'required' => ['topic'],
                    'additionalProperties' => false,
                ],
                'strict' => true,
            ],
        ];
    }

    public function names(): array
    {
        return array_keys(self::DATA_CATEGORY);
    }

    /**
     * Validasi & jalankan tool. Mengembalikan [isError, payload JSON string].
     *
     * @return array{0:bool, 1:string}
     */
    public function execute(string $name, mixed $input, User $user): array
    {
        if (! in_array($name, $this->names(), true)) {
            return [true, $this->json(['error' => 'Fungsi tidak dikenal.'])];
        }

        if (! is_array($input)) {
            return [true, $this->json(['error' => 'Input fungsi tidak valid.'])];
        }

        $allowedKeys = match ($name) {
            'get_my_course_detail' => ['course'],
            'search_course_catalog' => ['keyword'],
            'get_mooc_help' => ['topic'],
            default => ['scope'],
        };

        if (array_diff(array_keys($input), $allowedKeys) !== []) {
            return [true, $this->json(['error' => 'Parameter tidak diizinkan.'])];
        }

        if ($allowedKeys === ['scope'] && ($input['scope'] ?? 'me') !== 'me') {
            return [true, $this->json(['error' => 'Hanya data milik peserta yang sedang login yang dapat diakses.'])];
        }

        if ($allowedKeys === ['scope']) {
            $allowedKeys = [];
        }

        foreach ($allowedKeys as $key) {
            if (! isset($input[$key]) || ! is_string($input[$key]) || mb_strlen($input[$key]) > 120) {
                return [true, $this->json(['error' => "Parameter {$key} tidak valid."])];
            }
        }

        $data = match ($name) {
            'get_my_profile' => $this->profile($user),
            'list_my_courses' => $this->courses($user),
            'get_my_course_detail' => $this->courseDetail($user, trim($input['course'])),
            'list_my_certificates' => $this->certificates($user),
            'list_my_pending_tasks' => $this->pendingTasks($user),
            'list_my_quiz_results' => $this->quizResults($user),
            'search_course_catalog' => $this->catalog($user, trim($input['keyword'])),
            'get_mooc_help' => $this->help($input['topic']),
        };

        return [false, $this->json($data)];
    }

    private function profile(User $user): array
    {
        $nip = preg_replace('/\D/', '', (string) $user->nip);

        if ($nip !== '' && $this->settings->bool('sensitive_data_protection_enabled')) {
            $nip = str_repeat('*', max(0, strlen($nip) - 4)).substr($nip, -4);
        }

        return [
            'nama' => $user->name,
            'email' => $user->email,
            'nip' => $nip !== '' ? $nip : null,
            'jabatan' => $user->jabatan,
            'pangkat_golongan' => $user->pangkat_golongan,
            'unit_organisasi' => $user->unit_organisasi,
            'peran' => $user->getRoleNames()->first(),
            'terdaftar_sejak' => $this->date($user->getRawOriginal('created_at') ? $user->created_at : null),
        ];
    }

    private function courses(User $user): array
    {
        $enrollments = $this->enrollments($user);

        return [
            'jumlah_kursus' => $enrollments->count(),
            'berlangsung' => $enrollments->where('status', 'Berlangsung')->count(),
            'selesai' => $enrollments->where('status', 'Selesai')->count(),
            'kursus' => $enrollments->map(fn (CourseEnrollment $e) => [
                'kode' => $e->course->kode,
                'judul' => $e->course->judul,
                'kategori' => $e->course->kategori,
                'status' => $e->status,
                'progres_persen' => (int) $e->progress,
                'topik_selesai' => $e->modul_selesai.' / '.$e->course->modul_total,
                'tenggat' => $this->date($e->deadline),
                'kursus_berakhir' => $e->course->ends_at_enabled ? $this->date($e->course->ends_at) : null,
                'terdaftar' => $this->date($e->enrolled_at),
            ])->values()->all(),
        ];
    }

    private function courseDetail(User $user, string $keyword): array
    {
        if ($keyword === '') {
            return ['error' => 'Sebutkan kode atau judul kursus.'];
        }

        $enrollments = $this->enrollments($user);
        $needle = Str::lower($keyword);

        $matches = $enrollments->filter(function (CourseEnrollment $e) use ($needle): bool {
            return Str::lower((string) $e->course->kode) === $needle
                || Str::contains(Str::lower($e->course->judul), $needle);
        })->values();

        if ($matches->isEmpty()) {
            return [
                'ditemukan' => false,
                'pesan' => 'Kursus tersebut tidak ada di daftar kursus yang Anda ikuti.',
                'kursus_yang_diikuti' => $enrollments->map(fn ($e) => $e->course->judul)->values()->all(),
            ];
        }

        if ($matches->count() > 1) {
            return [
                'ditemukan' => false,
                'pesan' => 'Ada beberapa kursus yang cocok, minta peserta memilih salah satu.',
                'pilihan' => $matches->map(fn ($e) => ['kode' => $e->course->kode, 'judul' => $e->course->judul])->all(),
            ];
        }

        /** @var CourseEnrollment $enrollment */
        $enrollment = $matches->first();
        $course = $enrollment->course->load(['modules' => fn ($q) => $q->orderBy('urutan')->with(['lessons' => fn ($q) => $q->orderBy('urutan')])]);
        $lessonIds = $course->modules->flatMap->lessons->pluck('id');
        $completed = LessonCompletion::query()
            ->where('user_id', $user->id)
            ->whereIn('course_lesson_id', $lessonIds)
            ->pluck('course_lesson_id')
            ->flip();

        $next = null;
        $topik = [];
        $count = 0;

        foreach ($course->modules as $module) {
            $materi = [];
            foreach ($module->lessons as $lesson) {
                $done = $completed->has($lesson->id);
                if (! $done && $next === null) {
                    $next = ['topik' => $module->judul, 'materi' => $lesson->judul, 'jenis' => $lesson->typeLabel()];
                }
                if ($count++ < 80) {
                    $materi[] = [
                        'judul' => $lesson->judul,
                        'jenis' => $lesson->typeLabel(),
                        'wajib' => (bool) $lesson->is_required,
                        'selesai' => $done,
                    ];
                }
            }
            $topik[] = ['urutan' => $module->urutan, 'judul' => $module->judul, 'materi' => $materi];
        }

        return [
            'ditemukan' => true,
            'kode' => $course->kode,
            'judul' => $course->judul,
            'status' => $enrollment->status,
            'progres_persen' => (int) $enrollment->progress,
            'materi_selesai' => $completed->count().' / '.$lessonIds->count(),
            'materi_berikutnya' => $next,
            'tenggat' => $this->date($enrollment->deadline),
            'topik' => $topik,
        ];
    }

    private function certificates(User $user): array
    {
        $certs = Certificate::query()
            ->where('user_id', $user->id)
            ->with('course:id,judul,kode')
            ->orderByDesc('issued_at')
            ->get();

        $certCourseIds = $certs->whereNotNull('issued_at')->pluck('course_id');

        $pending = $this->enrollments($user)
            ->filter(fn (CourseEnrollment $e) => $e->progress >= 100 && ! $certCourseIds->contains($e->course_id))
            ->map(fn ($e) => $e->course->judul)
            ->values();

        return [
            'jumlah_sertifikat' => $certs->whereNotNull('issued_at')->count(),
            'sertifikat' => $certs->whereNotNull('issued_at')->map(fn (Certificate $c) => [
                'kursus' => $c->course?->judul,
                'nomor' => $c->nomor,
                'nilai_akhir' => $c->nilai_akhir,
                'terbit' => $this->date($c->issued_at),
            ])->values()->all(),
            'kursus_selesai_belum_terbit' => $pending->all(),
            'catatan' => 'Sertifikat dapat dilihat dan diunduh di menu Sertifikat.',
        ];
    }

    private function pendingTasks(User $user): array
    {
        $enrollments = $this->enrollments($user)->where('status', '!=', 'Selesai');
        $courseIds = $enrollments->pluck('course_id');

        $lessons = CourseLesson::query()
            ->whereHas('module', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->with('module:id,course_id,urutan,judul')
            ->get()
            ->sortBy(fn ($l) => sprintf('%05d-%05d', $l->module->urutan, $l->urutan))
            ->groupBy(fn ($l) => $l->module->course_id);

        $allIds = $lessons->flatten()->pluck('id');
        $completed = LessonCompletion::query()->where('user_id', $user->id)->whereIn('course_lesson_id', $allIds)->pluck('course_lesson_id')->flip();
        $submitted = AssignmentSubmission::query()->where('user_id', $user->id)->whereIn('course_lesson_id', $allIds)->pluck('course_lesson_id')->flip();
        $answered = SurveyResponse::query()->active()->where('user_id', $user->id)->whereIn('course_lesson_id', $allIds)->pluck('course_lesson_id')->flip();

        $today = $this->quota->now()->startOfDay();
        $items = [];

        foreach ($enrollments as $enrollment) {
            $courseLessons = $lessons->get($enrollment->course_id, collect());
            $next = $courseLessons->first(fn ($l) => ! $completed->has($l->id));
            $assignments = $courseLessons->filter(fn ($l) => $l->normalizedType() === 'penugasan' && ! $submitted->has($l->id));
            $quizzes = $courseLessons->filter(fn ($l) => in_array($l->normalizedType(), ['survey', 'pre_test', 'post_test'], true) && ! $answered->has($l->id));

            $deadline = $enrollment->deadline ?? ($enrollment->course->ends_at_enabled ? $enrollment->course->ends_at : null);

            $items[] = [
                'kursus' => $enrollment->course->judul,
                'progres_persen' => (int) $enrollment->progress,
                'materi_berikutnya' => $next ? ['topik' => $next->module->judul, 'materi' => $next->judul, 'jenis' => $next->typeLabel()] : null,
                'penugasan_belum_dikumpulkan' => $assignments->pluck('judul')->take(10)->values()->all(),
                'kuis_atau_survei_belum_dikerjakan' => $quizzes->pluck('judul')->take(10)->values()->all(),
                'tenggat' => $this->date($deadline),
                'sisa_hari_menuju_tenggat' => $deadline ? (int) $today->diffInDays($deadline->copy()->startOfDay(), false) : null,
            ];
        }

        return [
            'tanggal_hari_ini' => $today->translatedFormat('l, d F Y'),
            'kursus_berlangsung' => count($items),
            'rincian' => $items,
        ];
    }

    private function quizResults(User $user): array
    {
        $responses = SurveyResponse::query()
            ->active()
            ->where('user_id', $user->id)
            ->with(['lesson:id,judul,course_module_id,tipe', 'lesson.module:id,course_id', 'lesson.module.course:id,judul'])
            ->latest()
            ->limit(30)
            ->get();

        return [
            'jumlah' => $responses->count(),
            'hasil' => $responses->map(fn (SurveyResponse $r) => [
                'kursus' => $r->lesson?->module?->course?->judul,
                'aktivitas' => $r->lesson?->judul,
                'percobaan_ke' => $r->attempt_number ?? 1,
                'nilai_pg' => $r->max_possible_score > 0 ? SurveyScoring::percentage($r) : null,
                'nilai_esai' => match (SurveyScoring::essayStatus($r)) {
                    'graded' => (float) $r->essay_score,
                    'pending' => 'menunggu penilaian pengajar',
                    default => null,
                },
                'keterangan' => $r->max_possible_score > 0 || $r->essay_answered_count > 0 ? null : 'Survei tanpa nilai',
                'dikirim' => $this->date($r->created_at),
            ])->values()->all(),
        ];
    }

    private function catalog(User $user, string $keyword): array
    {
        $enrolled = $user->courseEnrollments()->pluck('course_id')->flip();

        $courses = Course::query()
            ->where('is_published', true)
            ->when($keyword !== '', function ($q) use ($keyword): void {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $keyword).'%';
                $q->where(fn ($w) => $w->where('judul', 'like', $like)->orWhere('kategori', 'like', $like)->orWhere('kode', 'like', $like));
            })
            ->latest()
            ->limit(15)
            ->get(['id', 'kode', 'judul', 'kategori', 'level', 'durasi_jam', 'instruktur']);

        return [
            'jumlah' => $courses->count(),
            'kursus' => $courses->map(fn (Course $c) => [
                'kode' => $c->kode,
                'judul' => $c->judul,
                'kategori' => $c->kategori,
                'level' => $c->level,
                'durasi_jam' => $c->durasi_jam,
                'sudah_diikuti' => $enrolled->has($c->id),
            ])->all(),
            'catatan' => 'Pendaftaran kursus dilakukan sendiri oleh peserta di menu Katalog Kursus.',
        ];
    }

    private function help(string $topic): array
    {
        $guide = [
            'beranda' => 'Menu Beranda menampilkan ringkasan: jumlah kursus aktif dan selesai, rata-rata progres, grafik progres per kursus, dan tombol "Lanjutkan" ke kursus terakhir.',
            'kursus_saya' => 'Menu Pembelajaran > Kursus Saya berisi semua kursus yang Anda ikuti. Klik kursus untuk melihat topik dan materi. Materi dibuka berurutan; materi wajib harus diselesaikan dulu sebelum materi berikutnya terbuka.',
            'katalog' => 'Menu Pembelajaran > Katalog Kursus menampilkan kursus yang dipublikasikan. Klik "Daftar" untuk mengikuti kursus; kursus langsung muncul di Kursus Saya.',
            'materi_dan_progres' => 'Buka materi dari halaman kursus. Progres naik saat materi ditandai selesai, tugas dikumpulkan, atau kuis dikirim. Lihat ringkasan di menu Nilai & Progres.',
            'kuis_dan_nilai' => 'Kuis, pre test, dan post test dikerjakan di halaman materi. Jawaban hanya bisa dikirim sekali. Skor dihitung dari soal pilihan ganda dan checklist (persentase soal benar). Hasil terlihat di halaman materi dan menu Nilai & Progres.',
            'penugasan' => 'Pada materi Penugasan, baca instruksi lalu kumpulkan jawaban sesuai format yang diizinkan (teks, file, atau link), kemudian klik Kumpulkan.',
            'forum' => 'Jika forum kursus dibuka, gunakan tombol Forum di halaman kursus untuk membuat diskusi, membalas, dan memberi suka.',
            'sertifikat' => 'Sertifikat terbit otomatis setelah progres kursus 100% (dan memenuhi syarat kursus). Lihat dan unduh di menu Sertifikat.',
            'ai_assistant' => 'AI Assistant menjawab pertanyaan seputar pembelajaran Anda sendiri dan cara memakai MOOC. Kuota pertanyaan dibatasi per hari dan direset otomatis. AI tidak dapat mengakses data peserta lain.',
        ];

        return ['topik' => $topic, 'panduan' => $guide[$topic] ?? 'Topik tidak tersedia.'];
    }

    /** @return Collection<int, CourseEnrollment> */
    private function enrollments(User $user): Collection
    {
        return CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->whereHas('course', fn ($q) => $q->where('is_published', true))
            ->with('course:id,kode,judul,kategori,modul_total,ends_at,ends_at_enabled')
            ->orderByDesc('enrolled_at')
            ->get();
    }

    private function date(?CarbonInterface $date): ?string
    {
        return $date?->copy()->setTimezone($this->settings->timezone())->translatedFormat('d F Y');
    }

    private function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
