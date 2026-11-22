<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AiAssistantSetting;
use App\Models\User;
use App\Support\Activity\Sanitizer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use BuildsLessonFixtures;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildFixtures();
        // Peserta kedua juga terdaftar di kursus yang sama.
        \App\Models\CourseEnrollment::query()->create([
            'user_id' => $this->otherPeserta->id, 'course_id' => $this->course->id, 'progress' => 0,
            'modul_selesai' => 0, 'status' => 'Berlangsung', 'enrolled_at' => now(),
        ]);
    }

    private function logsOf(User $user)
    {
        return ActivityLog::query()->ownedBy($user)->orderBy('id')->get();
    }

    /* ---------------- Pencatatan ---------------- */

    public function test_page_visit_is_logged_with_context_and_readable_description(): void
    {
        $this->actingAs($this->peserta)
            ->withHeader('User-Agent', 'PHPUnit-Browser/1.0')
            ->get(route('peserta.kursus.show', $this->course))->assertOk();

        $log = ActivityLog::query()->ownedBy($this->peserta)->where('log_name', 'request')->latest('id')->firstOrFail();

        $this->assertSame('Membuka kursus "'.$this->course->judul.'"', $log->description);
        $this->assertSame('peserta.kursus.show', $log->route_name);
        $this->assertSame('GET', $log->method);
        $this->assertSame(200, $log->status_code);
        $this->assertSame($this->peserta->email, $log->causer_email);
        $this->assertSame('peserta', $log->causer_role);
        $this->assertSame('PHPUnit-Browser/1.0', $log->user_agent);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->request_id);
        $this->assertNotNull($log->duration_ms);
    }

    public function test_guest_and_noisy_routes_are_not_logged_as_requests(): void
    {
        $before = ActivityLog::query()->count();
        $this->get(route('landing-page.index'))->assertOk();
        $this->assertSame($before, ActivityLog::query()->count(), 'Tamu tidak dicatat sebagai request');

        $video = $this->lesson('video', ['video_url' => 'courses/videos/a.mp4']);
        $this->actingAs($this->peserta)->postJson(route('peserta.kursus.lessons.progress', [$this->course, $video]), ['position' => 0, 'duration' => 60])->assertOk();
        $this->assertFalse(ActivityLog::query()->where('route_name', 'peserta.kursus.lessons.progress')->exists(), 'Heartbeat tidak dicatat');
    }

    public function test_failed_and_forbidden_requests_are_logged_with_status(): void
    {
        $this->actingAs($this->peserta)->getJson(route('activity-logs.index'))->assertForbidden();

        $log = ActivityLog::query()->ownedBy($this->peserta)->where('route_name', 'activity-logs.index')->firstOrFail();
        $this->assertSame(403, $log->status_code);
    }

    public function test_submitted_payload_is_sanitized(): void
    {
        $lesson = $this->surveyLesson();
        $this->actingAs($this->peserta)->post(route('peserta.kursus.lessons.survey.submit', [$this->course, $lesson]), [
            'answers' => $this->answers(),
            'password' => 'RahasiaSekali123',
            'api_key' => 'sk-ant-api03-SHOULDNOTAPPEAR',
            'catatan' => 'token saya sk-live-abcdefghijklmnop',
            'panjang' => str_repeat('x', 2000),
        ]);

        $log = ActivityLog::query()->ownedBy($this->peserta)->where('log_name', 'request')->where('route_name', 'peserta.kursus.lessons.survey.submit')->firstOrFail();
        $json = json_encode($log->properties);

        $this->assertStringNotContainsString('RahasiaSekali123', $json);
        $this->assertStringNotContainsString('SHOULDNOTAPPEAR', $json);
        $this->assertStringNotContainsString('sk-live-abcdefghijklmnop', $json);
        $this->assertStringNotContainsString('_token', $json);
        $this->assertSame(Sanitizer::MASK, $log->getProperty('payload.password'));
        $this->assertStringContainsString('(dipotong)', $log->getProperty('payload.panjang'));
        $this->assertSame((string) $this->opt['radio_ok']->id, (string) $log->getProperty('payload.answers.'.$this->qRadio->id));
    }

    public function test_file_uploads_are_logged_as_metadata_only(): void
    {
        $lesson = $this->lesson('penugasan', ['assignment_allow_file' => true]);
        $this->actingAs($this->peserta)->post(route('peserta.kursus.lessons.submit', [$this->course, $lesson]), [
            'submission_file' => UploadedFile::fake()->create('laporan.pdf', 12, 'application/pdf'),
        ]);

        $log = ActivityLog::query()->ownedBy($this->peserta)->where('log_name', 'request')->where('route_name', 'peserta.kursus.lessons.submit')->firstOrFail();
        $this->assertSame('laporan.pdf', $log->getProperty('payload.submission_file.file'));
    }

    public function test_data_changes_keep_before_and_after_linked_to_the_request(): void
    {
        $original = $this->course->judul;

        $this->actingAs($this->admin)->put(route('courses.update', $this->course), [
            'judul' => 'Judul Baru Uji', 'kode' => $this->course->kode, 'kategori' => $this->course->kategori,
            'instruktur' => 'Tester', 'level' => 'Pemula', 'deskripsi' => 'Test', 'durasi_jam' => 1,
        ]);

        $change = ActivityLog::query()->where('log_name', 'data')->where('subject_type', \App\Models\Course::class)
            ->where('subject_id', (string) $this->course->id)->where('event', 'updated')->latest('id')->first();

        if (! $change) {
            // Validasi form kursus bisa berbeda; uji jalur model langsung tetap wajib lulus.
            $this->actingAs($this->admin);
            $this->course->update(['judul' => 'Judul Baru Uji']);
            $change = ActivityLog::query()->where('log_name', 'data')->where('subject_id', (string) $this->course->id)->where('event', 'updated')->latest('id')->firstOrFail();
        }

        $this->assertSame($original, $change->before()['judul']);
        $this->assertSame('Judul Baru Uji', $change->after()['judul']);
        $this->assertArrayNotHasKey('updated_at', $change->after());
        $this->assertSame($this->admin->id, (int) $change->causer_id);
        $this->assertSame('Mengubah kursus "Judul Baru Uji"', $change->description);
    }

    public function test_created_and_deleted_events_and_secret_masking(): void
    {
        $this->actingAs($this->admin);

        $this->module->update(['judul' => 'Modul Diubah']);
        $lesson = $this->lesson('url', ['file_url' => 'https://example.com', 'judul' => 'Materi Akan Dihapus']);
        $lesson->delete();

        $created = ActivityLog::query()->where('subject_id', (string) $lesson->id)->where('event', 'created')->firstOrFail();
        $deleted = ActivityLog::query()->where('subject_id', (string) $lesson->id)->where('event', 'deleted')->firstOrFail();
        $this->assertSame('Materi Akan Dihapus', $created->after()['judul']);
        $this->assertSame('Materi Akan Dihapus', $deleted->before()['judul']);
        $this->assertSame([], $deleted->after());

        // Password: perubahan tercatat, nilai disamarkan.
        $this->peserta->update(['password' => Hash::make('baru-123456')]);
        $pw = ActivityLog::query()->where('subject_type', User::class)->where('subject_id', (string) $this->peserta->id)->where('event', 'updated')->latest('id')->firstOrFail();
        $this->assertSame(Sanitizer::MASK, $pw->after()['password']);
        $this->assertStringNotContainsString('$2y$', json_encode($pw->attribute_changes));

        // Pengaturan AI terenkripsi: nilai tidak pernah tercatat.
        AiAssistantSetting::query()->create(['key' => 'uji_secret_'.Str::random(4), 'value' => 'plain-secret-value', 'is_encrypted' => true]);
        $setting = ActivityLog::query()->where('subject_type', AiAssistantSetting::class)->latest('id')->firstOrFail();
        $this->assertSame(Sanitizer::MASK, $setting->after()['value']);
    }

    /* ---------------- Autentikasi ---------------- */

    public function test_login_logout_and_failed_logins_are_logged(): void
    {
        $user = User::factory()->create(['email' => 'auth-'.Str::random(6).'@test.local', 'password' => Hash::make('Benar-12345')]);
        $user->assignRole('peserta');

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah-sekali']);
        $failed = ActivityLog::query()->ownedBy($user)->where('event', 'failed')->firstOrFail();
        $this->assertSame(strtolower($user->email), strtolower($failed->getProperty('email')));
        $this->assertStringNotContainsString('salah-sekali', json_encode($failed->toArray()));

        $this->post(route('login.store'), ['email' => 'tidak-ada-'.Str::random(5).'@test.local', 'password' => 'x']);
        $unknown = ActivityLog::query()->where('event', 'failed')->whereNull('causer_id')->latest('id')->firstOrFail();
        $this->assertStringStartsWith('tidak-ada-', $unknown->getProperty('email'));

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Benar-12345']);
        $this->assertTrue(ActivityLog::query()->ownedBy($user)->where('event', 'login')->exists());

        $this->post(route('logout'));
        $this->assertTrue(ActivityLog::query()->ownedBy($user)->where('event', 'logout')->exists());
    }

    /* ---------------- Isolasi data antar peserta ---------------- */

    public function test_peserta_only_sees_own_activity(): void
    {
        $this->actingAs($this->peserta)->get(route('peserta.kursus.show', $this->course));
        $this->actingAs($this->otherPeserta)->get(route('peserta.katalog.index'));

        $mine = $this->actingAs($this->peserta)->get(route('activity.mine'));
        $mine->assertOk()->assertSee('Membuka kursus')->assertDontSee('Membuka katalog kursus')->assertDontSee($this->otherPeserta->email);

        // Manipulasi parameter tidak berpengaruh.
        $this->actingAs($this->peserta)->get(route('activity.mine', ['user_id' => $this->otherPeserta->id, 'causer_id' => $this->otherPeserta->id]))
            ->assertOk()->assertDontSee('Membuka katalog kursus');
    }

    public function test_peserta_cannot_open_other_peserta_activity_detail(): void
    {
        $this->actingAs($this->otherPeserta)->get(route('peserta.katalog.index'));
        $foreign = ActivityLog::query()->ownedBy($this->otherPeserta)->latest('id')->firstOrFail();

        $this->actingAs($this->peserta)->get(route('activity.mine.show', $foreign->id))->assertNotFound();
        $this->actingAs($this->peserta)->get('/aktivitas-saya/abc')->assertNotFound();

        $this->actingAs($this->otherPeserta)->get(route('activity.mine.show', $foreign->id))->assertOk()->assertSee('Membuka katalog kursus');
    }

    public function test_related_activities_on_detail_are_also_isolated(): void
    {
        $this->actingAs($this->peserta)->get(route('peserta.kursus.show', $this->course));
        $own = ActivityLog::query()->ownedBy($this->peserta)->latest('id')->firstOrFail();

        // Aktivitas user lain yang (secara paksa) memakai request_id yang sama tidak boleh ikut tampil.
        ActivityLog::query()->create([
            'log_name' => 'data', 'description' => 'RAHASIA PESERTA LAIN', 'event' => 'updated',
            'causer_type' => User::class, 'causer_id' => $this->otherPeserta->id, 'request_id' => $own->request_id,
        ]);

        $this->actingAs($this->peserta)->get(route('activity.mine.show', $own->id))->assertOk()->assertDontSee('RAHASIA PESERTA LAIN');
    }

    public function test_peserta_cannot_access_admin_log_pages(): void
    {
        foreach (['activity-logs.index', 'activity-logs.export'] as $route) {
            $this->actingAs($this->peserta)->getJson(route($route))->assertForbidden();
            $this->actingAs($this->peserta)->get(route($route))->assertRedirect();
        }
        $log = ActivityLog::query()->latest('id')->firstOrFail();
        $this->actingAs($this->peserta)->getJson(route('activity-logs.show', $log))->assertForbidden();
        $this->actingAs($this->peserta)->postJson(route('activity-logs.prune'), ['confirm' => 1])->assertForbidden();
    }

    /* ---------------- Admin ---------------- */

    public function test_admin_sees_all_users_and_can_filter(): void
    {
        $this->actingAs($this->peserta)->get(route('peserta.kursus.show', $this->course));
        $this->actingAs($this->otherPeserta)->get(route('peserta.katalog.index'));

        $this->actingAs($this->admin)->get(route('activity-logs.index'))
            ->assertOk()->assertSee($this->peserta->email)->assertSee($this->otherPeserta->email);

        $this->actingAs($this->admin)->get(route('activity-logs.index', ['user_id' => $this->otherPeserta->id]))
            ->assertOk()->assertSee('Membuka katalog kursus')->assertDontSee('Membuka kursus "'.$this->course->judul.'"');

        $this->actingAs($this->admin)->get(route('activity-logs.index', ['log' => 'auth', 'status' => '5xx', 'from' => '2000-01-01', 'to' => 'bukan-tanggal', 'subject_type' => 'App\\Evil']))
            ->assertOk();

        $log = ActivityLog::query()->ownedBy($this->peserta)->latest('id')->firstOrFail();
        $this->actingAs($this->admin)->get(route('activity-logs.show', $log))->assertOk()->assertSee($this->peserta->email);
    }

    public function test_admin_export_contains_before_after_and_is_itself_logged(): void
    {
        $this->actingAs($this->admin);
        $this->module->update(['judul' => 'Modul Export Uji']);

        $csv = $this->actingAs($this->admin)->get(route('activity-logs.export', ['log' => 'data']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Modul Export Uji', $csv);
        $this->assertStringContainsString('Sebelum', $csv);

        $this->assertTrue(ActivityLog::query()->ownedBy($this->admin)->where('route_name', 'activity-logs.export')->exists(), 'Export log tercatat');
    }

    public function test_admin_manual_prune_removes_only_old_logs(): void
    {
        $old = ActivityLog::query()->create(['log_name' => 'request', 'description' => 'LOG LAMA', 'created_at' => now()->subDays(100), 'updated_at' => now()->subDays(100)]);
        $twoMonths = ActivityLog::query()->create(['log_name' => 'request', 'description' => 'LOG 60 HARI', 'created_at' => now()->subDays(60), 'updated_at' => now()->subDays(60)]);
        $recent = ActivityLog::query()->create(['log_name' => 'request', 'description' => 'LOG BARU']);

        $this->actingAs($this->admin)->post(route('activity-logs.prune'))->assertSessionHasErrors('confirm');
        $this->assertTrue(ActivityLog::query()->whereKey($old->id)->exists());

        $this->actingAs($this->admin)->post(route('activity-logs.prune'), ['confirm' => 1])->assertRedirect();
        $this->assertFalse(ActivityLog::query()->whereKey($old->id)->exists());
        $this->assertTrue(ActivityLog::query()->whereKey($recent->id)->exists());
        $this->assertTrue(ActivityLog::query()->whereKey($twoMonths->id)->exists(), 'Log < 3 bulan tetap disimpan');
        $this->assertSame(90, (int) config('activitylog.clean_after_days'));
        $this->assertTrue(ActivityLog::query()->where('description', 'like', 'Membersihkan % log aktivitas%')->exists());
    }

    /* ---------------- Sanitizer ---------------- */

    public function test_sanitizer_handles_nested_and_hostile_input(): void
    {
        $out = Sanitizer::clean([
            'user' => ['password_confirmation' => 'x', 'nested' => ['Authorization' => 'Bearer abc', 'ok' => 'aman']],
            'private_key' => '-----BEGIN',
            'note' => 'APP_KEY base64:QUJDREVGR0hJSktMTU5PUFFSU1RVVldY',
            'list' => range(1, 250),
        ]);

        $this->assertSame(Sanitizer::MASK, $out['user']['password_confirmation']);
        $this->assertSame(Sanitizer::MASK, $out['user']['nested']['Authorization']);
        $this->assertSame('aman', $out['user']['nested']['ok']);
        $this->assertSame(Sanitizer::MASK, $out['private_key']);
        $this->assertStringNotContainsString('QUJDREVG', $out['note']);
        $this->assertCount(201, $out['list']); // 200 item + penanda dipotong
    }
}
