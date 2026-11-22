<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\LessonCompletion;
use App\Models\LessonViewProgress;
use App\Services\LessonViewTracker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class LessonViewTrackingTest extends TestCase
{
    use BuildsLessonFixtures;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildFixtures();
    }

    private function video(string $url = 'courses/videos/materi.mp4'): CourseLesson
    {
        return $this->lesson('video', ['video_url' => $url]);
    }

    private function doc(string $url): CourseLesson
    {
        return $this->lesson('berkas', ['file_url' => $url]);
    }

    private function beat(CourseLesson $lesson, array $payload, $as = null)
    {
        return $this->actingAs($as ?? $this->peserta)
            ->postJson(route('peserta.kursus.lessons.progress', [$this->course, $lesson]), $payload);
    }

    private function row(CourseLesson $lesson): ?LessonViewProgress
    {
        return LessonViewProgress::query()->where('user_id', $this->peserta->id)->where('course_lesson_id', $lesson->id)->first();
    }

    private function completed(CourseLesson $lesson): bool
    {
        return LessonCompletion::query()->where('user_id', $this->peserta->id)->where('course_lesson_id', $lesson->id)->exists();
    }

    /** Tonton normal: heartbeat tiap 5 detik sampai posisi $until. */
    private function watch(CourseLesson $lesson, float $from, float $until, float $duration, int $step = 5, float $speed = 1.0): void
    {
        for ($pos = $from; $pos < $until;) {
            $this->travel($step)->seconds();
            $pos = min($until, $pos + $step * $speed);
            $this->beat($lesson, ['position' => $pos, 'duration' => $duration])->assertOk();
        }
    }

    /* ---------------- Jenis tracking ---------------- */

    public function test_tracking_kind_detection(): void
    {
        $t = app(LessonViewTracker::class);

        $this->assertSame('video', $t->trackingKind($this->video()));
        $this->assertSame('youtube', $t->videoPlayer($this->video('https://www.youtube.com/watch?v=dQw4w9WgXcQ')));
        $this->assertSame('vimeo', $t->videoPlayer($this->video('https://vimeo.com/123456')));
        $this->assertNull($t->trackingKind($this->video('https://drive.google.com/file/d/abc/view')), 'Video tak dikenal: tidak dilacak (manual)');
        $this->assertSame('pdf', $t->documentKind($this->doc('courses/files/modul.pdf')));
        $this->assertSame('pdf', $t->documentKind($this->doc('http://localhost/storage/courses/files/modul.PDF')));
        $this->assertSame('image', $t->documentKind($this->doc('courses/files/infografis.png')));
        $this->assertNull($t->trackingKind($this->doc('https://other-host.example.com/modul.pdf')), 'PDF host lain: manual');
        $this->assertNull($t->trackingKind($this->doc('courses/files/slide.pptx')), 'Office: manual');
        $this->assertNull($t->trackingKind($this->lesson('url', ['file_url' => 'https://example.com'])));
    }

    /* ---------------- Video normal ---------------- */

    public function test_watching_video_to_the_end_completes_it_automatically(): void
    {
        $lesson = $this->video();

        // Tombol manual ditolak.
        $this->actingAs($this->peserta)->post(route('peserta.kursus.lessons.complete', [$this->course, $lesson]))
            ->assertSessionHas('error', 'Tonton video sampai selesai untuk menyelesaikan materi ini.');
        $this->assertFalse($this->completed($lesson));

        $this->beat($lesson, ['position' => 0, 'duration' => 60])->assertOk()->assertJsonPath('completed', false);
        $this->watch($lesson, 0, 55, 60);
        $this->assertFalse($this->completed($lesson));

        $this->travel(5)->seconds();
        $res = $this->beat($lesson, ['position' => 60, 'duration' => 60, 'ended' => true]);

        $res->assertOk()->assertJsonPath('completed', true)->assertJsonPath('just_completed', true)->assertJsonPath('percent', 100);
        $this->assertTrue($this->completed($lesson));
        $this->assertSame(0, $this->row($lesson)->rejected_jumps);

        // Heartbeat setelah selesai: tetap selesai, tidak dobel.
        $this->travel(5)->seconds();
        $this->beat($lesson, ['position' => 10, 'duration' => 60])->assertJsonPath('completed', true)->assertJsonPath('just_completed', false);
        $this->assertSame(1, LessonCompletion::query()->where('user_id', $this->peserta->id)->where('course_lesson_id', $lesson->id)->count());
    }

    public function test_lesson_page_shows_tracker_and_hides_manual_button(): void
    {
        $lesson = $this->video('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $lesson]))
            ->assertOk()
            ->assertSee('id="lessonTrack"', false)
            ->assertSee('enablejsapi=1', false)
            ->assertSee('Tonton video sampai selesai')
            ->assertDontSee('Tandai selesai');

        $pdf = $this->doc('courses/files/modul.pdf');
        $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $pdf]))
            ->assertOk()->assertSee('id="lessonPdf"', false)->assertSee('pdf.min.js', false)->assertDontSee('Tandai selesai');
    }

    public function test_h5p_lesson_loads_its_stylesheet(): void
    {
        // Regresi: CSS H5P dulu di-push ke stack yang tidak dirender layout.
        $lesson = $this->lesson('h5p', ['file_url' => 'courses/h5p/x/package.h5p']);

        $this->actingAs($this->peserta)->get(route('peserta.kursus.lessons.show', [$this->course, $lesson]))
            ->assertOk()->assertSee('h5p-standalone/styles/h5p.css', false);
    }

    /* ---------------- Video: kecurangan ---------------- */

    public function test_skipping_ahead_is_rejected(): void
    {
        $lesson = $this->video();
        $this->beat($lesson, ['position' => 0, 'duration' => 600]);

        $this->travel(5)->seconds();
        $this->beat($lesson, ['position' => 590, 'duration' => 600, 'ended' => true])->assertJsonPath('completed', false);

        $row = $this->row($lesson);
        $this->assertSame(1, $row->rejected_jumps);
        $this->assertLessThanOrEqual(10, $row->max_position, 'Hanya kemajuan wajar yang diakui');
        $this->assertFalse($this->completed($lesson));
    }

    public function test_double_speed_cannot_finish_in_half_the_time(): void
    {
        $lesson = $this->video();
        $this->beat($lesson, ['position' => 0, 'duration' => 120]);

        // 2x lebih cepat selama 60 detik nyata (posisi diklaim 120).
        $this->watch($lesson, 0, 120, 120, 5, 2.0);
        $this->travel(5)->seconds();
        $this->beat($lesson, ['position' => 120, 'duration' => 120, 'ended' => true]);

        $this->assertFalse($this->completed($lesson));
        $this->assertLessThan(120 * 0.85, $this->row($lesson)->watched_seconds, 'Waktu tonton yang dikredit tetap waktu nyata');

        // 4x lebih cepat: lompatannya ditolak.
        $fast = $this->video();
        $this->beat($fast, ['position' => 0, 'duration' => 120]);
        $this->watch($fast, 0, 120, 120, 5, 4.0);
        $this->assertGreaterThan(0, $this->row($fast)->rejected_jumps);
        $this->assertFalse($this->completed($fast));
    }

    public function test_heartbeat_flood_is_ignored(): void
    {
        $lesson = $this->video();
        $this->beat($lesson, ['position' => 0, 'duration' => 300]);
        $before = $this->row($lesson)->heartbeats;

        // 20 heartbeat dalam 1 detik dengan posisi naik 3 detik tiap kali.
        for ($i = 1; $i <= 20; $i++) {
            $this->beat($lesson, ['position' => $i * 3, 'duration' => 300]);
        }

        $row = $this->row($lesson);
        $this->assertSame($before, $row->heartbeats, 'Heartbeat < 2 detik diabaikan');
        $this->assertLessThan(5, $row->max_position);
    }

    public function test_idling_then_jumping_gets_no_extra_credit(): void
    {
        $lesson = $this->video();
        $this->beat($lesson, ['position' => 0, 'duration' => 60]);

        $this->travel(2)->hours();
        $this->beat($lesson, ['position' => 60, 'duration' => 60, 'ended' => true])->assertJsonPath('completed', false);

        $this->assertLessThanOrEqual(25, $this->row($lesson)->max_position, 'Kredit per heartbeat dibatasi');
        $this->assertFalse($this->completed($lesson));
    }

    public function test_duration_is_locked_and_cannot_be_shrunk(): void
    {
        $lesson = $this->video();
        $this->beat($lesson, ['position' => 0, 'duration' => 900]);

        $this->travel(5)->seconds();
        $this->beat($lesson, ['position' => 5, 'duration' => 5, 'ended' => true])->assertJsonPath('completed', false);

        $this->assertSame(900.0, $this->row($lesson)->duration_seconds);
    }

    public function test_garbage_input_is_rejected_or_ignored(): void
    {
        $lesson = $this->video();

        $this->beat($lesson, ['position' => 'abc', 'duration' => 60])->assertStatus(422);
        $this->beat($lesson, ['position' => 0, 'duration' => 'NaN'])->assertStatus(422);
        $this->beat($lesson, ['position' => 0, 'duration' => 60, 'ended' => 'maybe'])->assertStatus(422);

        $this->beat($lesson, ['position' => 0, 'duration' => 60])->assertOk();
        $this->travel(5)->seconds();
        $this->beat($lesson, ['position' => -50, 'duration' => 60])->assertOk();
        $this->travel(5)->seconds();
        $this->beat($lesson, ['position' => 1e12, 'duration' => 1e12])->assertOk();

        $row = $this->row($lesson);
        $this->assertSame(60.0, $row->duration_seconds);
        $this->assertSame(0.0, $row->max_position);
        $this->assertFalse($this->completed($lesson));
    }

    /* ---------------- Dokumen ---------------- */

    public function test_pdf_completes_after_all_pages_and_minimum_time(): void
    {
        $lesson = $this->doc('courses/files/modul.pdf');

        $this->beat($lesson, ['total_pages' => 4, 'pages' => []])->assertOk();
        $this->travel(2)->seconds();
        $this->beat($lesson, ['total_pages' => 4, 'pages' => [1, 2]])->assertJsonPath('percent', 50);

        // Klaim total halaman berubah & halaman di luar rentang diabaikan.
        $this->beat($lesson, ['total_pages' => 1, 'pages' => [0, 9, -1]])->assertOk();
        $this->assertSame(4, $this->row($lesson)->total_pages);
        $this->assertSame([1, 2], $this->row($lesson)->pages_seen);

        // Semua halaman terlihat tapi waktu baca belum cukup (4 hlm x 2 dtk).
        $this->beat($lesson, ['total_pages' => 4, 'pages' => [3, 4]])->assertJsonPath('completed', false);
        $this->assertFalse($this->completed($lesson));

        $this->travel(7)->seconds();
        $this->beat($lesson, ['total_pages' => 4, 'pages' => []])->assertJsonPath('completed', true);
        $this->assertTrue($this->completed($lesson));
    }

    public function test_pdf_manual_complete_is_blocked_until_read(): void
    {
        $lesson = $this->doc('courses/files/modul.pdf');
        $this->actingAs($this->peserta)->post(route('peserta.kursus.lessons.complete', [$this->course, $lesson]))
            ->assertSessionHas('error', 'Baca dokumen sampai halaman terakhir untuk menyelesaikan materi ini.');
        $this->assertFalse($this->completed($lesson));
    }

    public function test_image_completes_after_short_view(): void
    {
        $lesson = $this->doc('courses/files/infografis.jpg');

        $this->beat($lesson, ['total_pages' => 99, 'pages' => [5]])->assertJsonPath('completed', false);
        $this->assertSame(1, $this->row($lesson)->total_pages, 'Gambar selalu 1 halaman');
        $this->travel(3)->seconds();
        $this->beat($lesson, [])->assertJsonPath('completed', true);
    }

    public function test_too_many_pages_in_one_request_is_rejected(): void
    {
        $lesson = $this->doc('courses/files/modul.pdf');
        $this->beat($lesson, ['total_pages' => 500, 'pages' => range(1, 201)])->assertStatus(422);
        $this->beat($lesson, ['total_pages' => 500, 'pages' => ['x']])->assertStatus(422);
        $this->assertNull($this->row($lesson));
    }

    /* ---------------- Fallback manual & akses ---------------- */

    public function test_untracked_materials_keep_manual_completion(): void
    {
        foreach ([$this->doc('https://other-host.example.com/modul.pdf'), $this->doc('courses/files/slide.pptx'), $this->video('https://drive.google.com/x')] as $lesson) {
            $this->beat($lesson, ['position' => 1, 'duration' => 2])->assertNotFound();
            $this->actingAs($this->peserta)->post(route('peserta.kursus.lessons.complete', [$this->course, $lesson]))->assertSessionHas('success');
            $this->assertTrue($this->completed($lesson));
        }
    }

    public function test_access_rules_for_progress_endpoint(): void
    {
        $lesson = $this->video();

        // Peserta tidak terdaftar.
        $this->beat($lesson, ['position' => 0, 'duration' => 60], $this->otherPeserta)->assertForbidden();
        $this->assertSame(0, LessonViewProgress::query()->where('user_id', $this->otherPeserta->id)->count());

        // Materi terkunci.
        $this->lesson('url', ['is_required' => true, 'file_url' => 'https://example.com']);
        $locked = $this->video();
        $this->beat($locked, ['position' => 0, 'duration' => 60])->assertForbidden();

        // Materi dari kursus lain via URL kursus ini.
        $otherCourse = Course::query()->create([
            'kode' => 'OC-'.Str::random(4), 'judul' => 'Lain', 'slug' => 'lain-'.Str::random(6), 'kategori' => 'Uji',
            'instruktur' => 'X', 'durasi_jam' => 1, 'modul_total' => 1, 'level' => 'Pemula', 'rating' => 0, 'deskripsi' => 'x', 'is_published' => true,
        ]);
        $otherModule = CourseModule::query()->create(['course_id' => $otherCourse->id, 'urutan' => 1, 'judul' => 'M', 'durasi_menit' => 1]);
        $foreign = CourseLesson::query()->create(['course_module_id' => $otherModule->id, 'urutan' => 1, 'judul' => 'V', 'tipe' => 'video', 'video_url' => 'x.mp4', 'is_required' => false]);
        $this->beat($foreign, ['position' => 0, 'duration' => 60])->assertNotFound();

        // Tamu.
        auth()->logout();
        $this->postJson(route('peserta.kursus.lessons.progress', [$this->course, $lesson]), ['position' => 0])->assertUnauthorized();
    }
}
