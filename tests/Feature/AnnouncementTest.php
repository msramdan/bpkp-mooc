<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Services\AnnouncementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use BuildsLessonFixtures;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildFixtures();
        $this->withoutMiddleware(ThrottleRequests::class);
        Storage::fake('local');
        // Tes tidak boleh bergantung pada data pengumuman yang sudah ada di DB lokal (di-rollback setelah tes).
        Announcement::query()->delete();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function make(array $attrs = []): Announcement
    {
        return Announcement::query()->create(array_merge([
            'title' => 'Pengumuman '.uniqid(),
            'body' => 'Isi pengumuman',
            'category' => 'info',
            'audience' => 'peserta',
            'display_mode' => 'once',
            'is_active' => true,
        ], $attrs));
    }

    private function valid(array $over = []): array
    {
        return array_merge([
            'title' => 'Jadwal Ujian',
            'body' => "Baris 1\nBaris 2",
            'category' => 'penting',
            'audience' => 'peserta',
            'display_mode' => 'once',
            'is_active' => '1',
            'starts_at' => '',
            'ends_at' => '',
        ], $over);
    }

    private function dashboardAfterLogin($user = null)
    {
        return $this->actingAs($user ?? $this->peserta)
            ->withSession([AnnouncementService::SESSION_FLAG => true])
            ->get(route('peserta.dashboard'));
    }

    /* ---------------- Admin: CRUD ---------------- */

    public function test_admin_creates_scheduled_announcement_with_link_and_attachment(): void
    {
        $file = UploadedFile::fake()->create('Jadwal Ujian.pdf', 120, 'application/pdf');

        $this->actingAs($this->admin)->post(route('announcements.store'), $this->valid([
            'starts_at' => '2030-01-10 08:00',
            'ends_at' => '2030-01-20 17:00',
            'link_url' => 'https://zoom.us/j/123',
            'link_label' => 'Gabung Zoom',
            'attachment' => $file,
        ]))->assertRedirect(route('announcements.index'))->assertSessionHas('success');

        $a = Announcement::query()->where('title', 'Jadwal Ujian')->firstOrFail();
        // Input WIB (UTC+7) disimpan sebagai UTC.
        $this->assertSame('2030-01-10 01:00:00', $a->starts_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2030-01-20 10:00:00', $a->ends_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('scheduled', $a->status());
        $this->assertSame('Gabung Zoom', $a->link_label);
        $this->assertSame('Jadwal Ujian.pdf', $a->attachment_name);
        $this->assertSame((int) $this->admin->id, (int) $a->created_by);
        Storage::disk('local')->assertExists($a->attachment_path);
        $this->assertStringStartsWith('announcements/', $a->attachment_path);
    }

    public function test_update_replaces_and_removes_attachment_and_cleans_files(): void
    {
        $this->actingAs($this->admin)->post(route('announcements.store'), $this->valid(['attachment' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]));
        $a = Announcement::query()->latest()->firstOrFail();
        $old = $a->attachment_path;

        $this->actingAs($this->admin)->put(route('announcements.update', $a), $this->valid([
            'title' => 'Baru', 'attachment' => UploadedFile::fake()->image('poster.png', 400, 300),
        ]))->assertRedirect();
        $a->refresh();
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($a->attachment_path);
        $this->assertTrue($a->attachmentIsImage());

        $current = $a->attachment_path;
        $this->actingAs($this->admin)->put(route('announcements.update', $a), $this->valid(['remove_attachment' => '1']))->assertRedirect();
        $this->assertNull($a->fresh()->attachment_path);
        Storage::disk('local')->assertMissing($current);

        $this->actingAs($this->admin)->post(route('announcements.store'), $this->valid(['attachment' => UploadedFile::fake()->create('b.pdf', 10, 'application/pdf')]));
        $b = Announcement::query()->latest()->firstOrFail();
        $path = $b->attachment_path;
        $this->actingAs($this->admin)->delete(route('announcements.destroy', $b))->assertRedirect();
        $this->assertNull(Announcement::query()->find($b->id));
        Storage::disk('local')->assertMissing($path);
    }

    public function test_abnormal_input_is_rejected(): void
    {
        $cases = [
            'judul kosong' => ['title' => '   '],
            'judul terlalu panjang' => ['title' => str_repeat('a', 151)],
            'isi terlalu panjang' => ['body' => str_repeat('a', 5001)],
            'javascript url' => ['link_url' => 'javascript:alert(1)'],
            'data url' => ['link_url' => 'data:text/html,<script>alert(1)</script>'],
            'ftp url' => ['link_url' => 'ftp://example.com/a'],
            'kategori palsu' => ['category' => 'hacker'],
            'audiens palsu' => ['audience' => 'superuser'],
            'mode palsu' => ['display_mode' => 'always'],
            'format tanggal salah' => ['starts_at' => '10/01/2030'],
            'tanggal mustahil' => ['starts_at' => '2030-02-31 10:00'],
            'berakhir sebelum mulai' => ['starts_at' => '2030-01-10 08:00', 'ends_at' => '2030-01-09 08:00'],
            'berakhir sama dengan mulai' => ['starts_at' => '2030-01-10 08:00', 'ends_at' => '2030-01-10 08:00'],
            'berakhir sudah lewat' => ['ends_at' => '2020-01-01 00:00'],
            'svg (xss)' => ['attachment' => UploadedFile::fake()->createWithContent('x.svg', '<svg onload="alert(1)"/>')],
            'html' => ['attachment' => UploadedFile::fake()->createWithContent('x.html', '<script>alert(1)</script>')],
            'php disamarkan' => ['attachment' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;')],
            'terlalu besar' => ['attachment' => UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf')],
            'judul array' => ['title' => ['x']],
        ];

        foreach ($cases as $label => $over) {
            $res = $this->actingAs($this->admin)->post(route('announcements.store'), $this->valid($over));
            $this->assertNotEmpty(session('errors')?->all() ?? [], "Kasus '{$label}' seharusnya ditolak");
            $res->assertRedirect();
        }
        $this->assertSame(0, Announcement::query()->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_toggle_on_off_via_json(): void
    {
        $a = $this->make();

        $this->actingAs($this->admin)->patchJson(route('announcements.toggle', $a), ['is_active' => false])
            ->assertOk()->assertJson(['ok' => true, 'is_active' => false, 'status' => 'inactive']);
        $this->assertFalse($a->fresh()->is_active);

        $this->actingAs($this->admin)->patchJson(route('announcements.toggle', $a), ['is_active' => 'bukan'])->assertStatus(422);
        $this->actingAs($this->admin)->patchJson(route('announcements.toggle', $a), ['is_active' => true])->assertJson(['status' => 'published']);
    }

    public function test_index_filters_and_search(): void
    {
        $this->make(['title' => 'Tayang Sekarang']);
        $this->make(['title' => 'Nanti', 'starts_at' => now()->addDay()]);
        $this->make(['title' => 'Lewat', 'ends_at' => now()->subDay()]);
        $this->make(['title' => 'Dimatikan', 'is_active' => false]);

        $this->actingAs($this->admin)->get(route('announcements.index', ['status' => 'scheduled']))
            ->assertOk()->assertSee('Nanti')->assertDontSee('Tayang Sekarang');
        $this->actingAs($this->admin)->get(route('announcements.index', ['status' => 'expired']))
            ->assertOk()->assertSee('Lewat')->assertDontSee('Nanti');
        $this->actingAs($this->admin)->get(route('announcements.index', ['q' => '100%_\\']))->assertOk();
        $this->actingAs($this->admin)->get(route('announcements.index', ['q' => 'Dimatikan']))
            ->assertOk()->assertSee('Dimatikan')->assertDontSee('Tayang Sekarang');
        $this->actingAs($this->admin)->get(route('announcements.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('announcements.edit', $this->make()))->assertOk();
    }

    /* ---------------- Akses ---------------- */

    public function test_peserta_and_guest_cannot_manage(): void
    {
        $a = $this->make();

        $this->actingAs($this->peserta)->getJson(route('announcements.index'))->assertForbidden();
        $this->actingAs($this->peserta)->postJson(route('announcements.store'), $this->valid())->assertForbidden();
        $this->actingAs($this->peserta)->patchJson(route('announcements.toggle', $a), ['is_active' => false])->assertForbidden();
        $this->actingAs($this->peserta)->deleteJson(route('announcements.destroy', $a))->assertForbidden();
        $this->assertTrue($a->fresh()->is_active);
        $this->assertSame(1, Announcement::query()->count());

        $this->app['auth']->forgetGuards();
        $this->get(route('announcements.feed'))->assertRedirect(route('login'));
        $this->postJson(route('announcements.dismiss'), ['ids' => [$a->id]])->assertUnauthorized();
        $this->get(route('announcements.attachment', $a))->assertRedirect(route('login'));
    }

    /* ---------------- Pop up ---------------- */

    public function test_popup_shows_after_login_respecting_schedule_status_and_audience(): void
    {
        $this->make(['title' => 'Tayang Peserta']);
        $this->make(['title' => 'Untuk Semua', 'audience' => 'all']);
        $this->make(['title' => 'Khusus Admin', 'audience' => 'admin']);
        $this->make(['title' => 'Belum Mulai', 'starts_at' => now()->addHour()]);
        $this->make(['title' => 'Sudah Lewat', 'ends_at' => now()->subMinute()]);
        $this->make(['title' => 'Mati', 'is_active' => false]);

        $this->dashboardAfterLogin()->assertOk()
            ->assertSee('id="annPopup"', false)
            ->assertSee('Tayang Peserta')->assertSee('Untuk Semua')
            ->assertDontSee('Khusus Admin')->assertDontSee('Belum Mulai')->assertDontSee('Sudah Lewat')->assertDontSee('>Mati<', false);

        // Admin melihat "all" + "admin", tidak melihat yang khusus peserta.
        $this->actingAs($this->admin)->withSession([AnnouncementService::SESSION_FLAG => true])->get(route('dashboard'))
            ->assertOk()->assertSee('Khusus Admin')->assertSee('Untuk Semua')->assertDontSee('Tayang Peserta');
    }

    public function test_scheduled_announcement_appears_automatically_when_time_comes_and_disappears_after_end(): void
    {
        Carbon::setTestNow('2030-01-10 00:00:00');
        $this->make(['title' => 'Ujian Batch 3', 'starts_at' => '2030-01-10 01:00:00', 'ends_at' => '2030-01-10 05:00:00']);

        $this->dashboardAfterLogin()->assertDontSee('Ujian Batch 3');
        Carbon::setTestNow('2030-01-10 01:00:00');
        $this->dashboardAfterLogin()->assertSee('Ujian Batch 3');
        Carbon::setTestNow('2030-01-10 05:00:00');
        $this->dashboardAfterLogin(); // flag dipakai
        $this->assertSame(0, app(AnnouncementService::class)->popupFor($this->peserta, true)->count());
    }

    public function test_once_mode_stops_after_dismiss_but_every_login_mode_returns(): void
    {
        $once = $this->make(['title' => 'Sekali Saja']);
        $every = $this->make(['title' => 'Tiap Login', 'display_mode' => 'every_login']);

        $this->dashboardAfterLogin()->assertSee('Sekali Saja')->assertSee('Tiap Login');
        $this->actingAs($this->peserta)->postJson(route('announcements.dismiss'), ['ids' => [$once->id, $every->id]])
            ->assertOk()->assertJson(['dismissed' => 2]);

        $this->dashboardAfterLogin()->assertDontSee('Sekali Saja')->assertSee('Tiap Login');

        // Halaman biasa (bukan setelah login): tidak ada pop up lagi.
        $this->actingAs($this->peserta)->get(route('peserta.dashboard'))->assertDontSee('id="annPopup"', false);
    }

    public function test_new_once_announcement_reaches_already_logged_in_user_only_once(): void
    {
        $this->actingAs($this->peserta)->get(route('peserta.dashboard'))->assertDontSee('id="annPopup"', false);
        $this->make(['title' => 'Info Mendadak', 'category' => 'mendesak']);

        $this->actingAs($this->peserta)->get(route('peserta.dashboard'))->assertSee('Info Mendadak');
        $this->actingAs($this->peserta)->get(route('peserta.dashboard'))->assertDontSee('id="annPopup"', false);
    }

    public function test_real_login_sets_popup_flag(): void
    {
        $this->make(['title' => 'Selamat Datang Batch 3']);
        $this->post(route('login'), ['email' => $this->peserta->email, 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($this->peserta);
        $this->get(route('peserta.dashboard'))->assertOk()->assertSee('Selamat Datang Batch 3');
    }

    public function test_dismiss_abnormal_and_idor_inputs(): void
    {
        $mine = $this->make();
        $adminOnly = $this->make(['audience' => 'admin']);
        $off = $this->make(['is_active' => false]);

        $this->actingAs($this->peserta)->postJson(route('announcements.dismiss'), [])->assertStatus(422);
        $this->actingAs($this->peserta)->postJson(route('announcements.dismiss'), ['ids' => 'x'])->assertStatus(422);
        $this->actingAs($this->peserta)->postJson(route('announcements.dismiss'), ['ids' => ['bukan-uuid']])->assertStatus(422);
        $this->actingAs($this->peserta)->postJson(route('announcements.dismiss'), ['ids' => array_fill(0, 51, $mine->id)])->assertStatus(422);

        // ID pengumuman untuk audiens lain / nonaktif diabaikan; klik ganda aman.
        $this->actingAs($this->peserta)->postJson(route('announcements.dismiss'), ['ids' => [$adminOnly->id, $off->id, $mine->id, $mine->id]])
            ->assertOk()->assertJson(['dismissed' => 1]);
        $this->actingAs($this->peserta)->postJson(route('announcements.dismiss'), ['ids' => [$mine->id]])->assertOk();

        $this->assertSame(1, AnnouncementRead::query()->where('user_id', $this->peserta->id)->count());
        $this->assertSame(0, AnnouncementRead::query()->whereIn('announcement_id', [$adminOnly->id, $off->id])->count());
        $read = AnnouncementRead::query()->where('user_id', $this->peserta->id)->first();
        $this->assertNotNull($read->seen_at);
        $this->assertNotNull($read->dismissed_at);
    }

    /* ---------------- Lampiran ---------------- */

    public function test_attachment_access_rules(): void
    {
        Storage::disk('local')->put('announcements/doc.pdf', '%PDF-1.4 test');
        Storage::disk('local')->put('announcements/pic.png', 'png-bytes');
        $doc = $this->make(['attachment_path' => 'announcements/doc.pdf', 'attachment_name' => 'Jadwal.pdf', 'attachment_mime' => 'application/pdf', 'attachment_size' => 13]);
        $pic = $this->make(['attachment_path' => 'announcements/pic.png', 'attachment_name' => 'poster.png', 'attachment_mime' => 'image/png', 'attachment_size' => 9]);
        $hidden = $this->make(['is_active' => false, 'attachment_path' => 'announcements/doc.pdf', 'attachment_name' => 'x.pdf', 'attachment_mime' => 'application/pdf']);
        $adminOnly = $this->make(['audience' => 'admin', 'attachment_path' => 'announcements/doc.pdf', 'attachment_name' => 'x.pdf', 'attachment_mime' => 'application/pdf']);
        $noFile = $this->make();

        $res = $this->actingAs($this->peserta)->get(route('announcements.attachment', $doc))->assertOk();
        $this->assertStringContainsString('attachment', (string) $res->headers->get('content-disposition'));
        $this->assertSame('nosniff', $res->headers->get('x-content-type-options'));
        $this->assertSame('application/octet-stream', $res->headers->get('content-type'));

        $img = $this->actingAs($this->peserta)->get(route('announcements.attachment', $pic).'?inline=1')->assertOk();
        $this->assertSame('image/png', $img->headers->get('content-type'));
        $this->assertStringContainsString('inline', (string) $img->headers->get('content-disposition'));

        $this->actingAs($this->peserta)->get(route('announcements.attachment', $hidden))->assertNotFound();
        $this->actingAs($this->peserta)->get(route('announcements.attachment', $adminOnly))->assertNotFound();
        $this->actingAs($this->peserta)->get(route('announcements.attachment', $noFile))->assertNotFound();
        $this->actingAs($this->peserta)->get('/pengumuman/../../.env/lampiran')->assertNotFound();
        // Admin tetap bisa membuka lampiran pengumuman nonaktif (untuk pengecekan).
        $this->actingAs($this->admin)->get(route('announcements.attachment', $hidden))->assertOk();
    }

    /* ---------------- XSS & tampilan ---------------- */

    public function test_xss_in_title_body_and_link_is_neutralised(): void
    {
        $this->make([
            'title' => '<script>alert("t")</script>',
            'body' => "<img src=x onerror=alert(1)>\njavascript:alert(2)\nLihat https://bpkp.go.id/info?a=1&b=2.",
            'link_url' => 'https://example.com/"onmouseover="alert(3)',
        ]);

        foreach ([route('peserta.dashboard'), route('announcements.feed')] as $url) {
            $html = $this->actingAs($this->peserta)->withSession([AnnouncementService::SESSION_FLAG => true])->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('<script>alert("t")', $html);
            $this->assertStringNotContainsString('<img src=x', $html);
            $this->assertStringNotContainsString('href="javascript:alert', $html);
            $this->assertStringNotContainsString('"onmouseover="', $html);
            $this->assertStringContainsString('href="https://bpkp.go.id/info?a=1&amp;b=2"', $html);
        }
    }

    public function test_render_body_unit(): void
    {
        $html = (string) AnnouncementService::renderBody("Halo <b>x</b>\nhttps://a.com/x).\nwww.b.com");
        $this->assertSame('Halo &lt;b&gt;x&lt;/b&gt;<br>'."\n".'<a href="https://a.com/x" target="_blank" rel="noopener noreferrer nofollow">https://a.com/x</a>).<br>'."\n".'www.b.com', $html);
        $this->assertSame('', (string) AnnouncementService::renderBody(null));
    }

    public function test_feed_lists_and_marks_seen_and_header_badge_counts_unseen(): void
    {
        $this->make(['title' => 'Satu']);
        $this->make(['title' => 'Dua', 'category' => 'mendesak']);

        $this->actingAs($this->peserta)->get(route('announcements.feed'))
            ->assertOk()->assertSee('Satu')->assertSee('Dua')->assertSee('BARU')
            ->assertDontSee('id="annPopup"', false);
        $this->assertSame(0, app(AnnouncementService::class)->unseenCount($this->peserta));
        $this->actingAs($this->peserta)->get(route('announcements.feed'))->assertDontSee('BARU');

        $this->make(['title' => 'Tiga', 'display_mode' => 'every_login']);
        $this->assertSame(1, app(AnnouncementService::class)->unseenCount($this->peserta));
    }

    public function test_activity_log_records_changes(): void
    {
        $this->actingAs($this->admin)->post(route('announcements.store'), $this->valid(['title' => 'Tercatat']));
        $a = Announcement::query()->where('title', 'Tercatat')->firstOrFail();

        $this->assertDatabaseHas('activity_log', ['subject_type' => Announcement::class, 'subject_id' => $a->id, 'event' => 'created']);
    }
}
