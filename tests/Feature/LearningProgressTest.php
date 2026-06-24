<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\User;
use App\Services\LearningProgressService;
use App\Support\Roles;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LearningProgressTest extends TestCase
{
    use RefreshDatabase;

    private User $peserta;

    private Course $course;

    private CourseLesson $lesson1;

    private CourseLesson $lesson2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        Role::findByName(Roles::PESERTA)->givePermissionTo([
            'peserta dashboard view',
            'peserta kursus view',
            'peserta katalog view',
        ]);

        $this->peserta = User::factory()->create();
        $this->peserta->assignRole(Roles::PESERTA);

        $this->course = Course::query()->create([
            'kode' => 'TST-01',
            'judul' => 'Kursus Uji',
            'slug' => 'tst-01-kursus-uji',
            'kategori' => 'Uji',
            'instruktur' => 'Tester',
            'thumbnail' => 'https://example.com/t.jpg',
            'durasi_jam' => 1,
            'modul_total' => 1,
            'level' => 'Pemula',
            'rating' => 0,
            'deskripsi' => 'Test',
            'is_published' => true,
        ]);

        $module = CourseModule::query()->create([
            'course_id' => $this->course->id,
            'urutan' => 1,
            'judul' => 'Modul 1',
            'deskripsi' => null,
            'durasi_menit' => 10,
        ]);

        $this->lesson1 = CourseLesson::query()->create([
            'course_module_id' => $module->id,
            'urutan' => 1,
            'judul' => 'Materi 1',
            'tipe' => 'reading',
            'body' => '<p>Halo</p>',
            'is_preview' => false,
            'is_required' => true,
        ]);

        $this->lesson2 = CourseLesson::query()->create([
            'course_module_id' => $module->id,
            'urutan' => 2,
            'judul' => 'Materi 2',
            'tipe' => 'reading',
            'body' => '<p>Dua</p>',
            'is_preview' => false,
            'is_required' => true,
        ]);
    }

    public function test_peserta_can_self_enroll_from_katalog(): void
    {
        $response = $this->actingAs($this->peserta)->post(route('peserta.katalog.enroll', $this->course));

        $response->assertRedirect(route('peserta.kursus.show', $this->course));
        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $this->peserta->id,
            'course_id' => $this->course->id,
            'progress' => 0,
        ]);
    }

    public function test_self_enroll_is_idempotent_under_concurrent_requests(): void
    {
        $service = app(LearningProgressService::class);

        DB::transaction(fn () => $service->enroll($this->peserta, $this->course));

        try {
            $service->enroll($this->peserta, $this->course);
        } catch (\Throwable) {
            // duplicate should not throw
        }

        $this->assertSame(1, CourseEnrollment::query()
            ->where('user_id', $this->peserta->id)
            ->where('course_id', $this->course->id)
            ->count());
    }

    public function test_completing_lesson_updates_progress(): void
    {
        $service = app(LearningProgressService::class);
        $service->enroll($this->peserta, $this->course);

        $enrollment = $service->completeLesson($this->peserta, $this->course, $this->lesson1);

        $this->assertSame(50, $enrollment->progress);
        $this->assertDatabaseHas('lesson_completions', [
            'user_id' => $this->peserta->id,
            'course_lesson_id' => $this->lesson1->id,
        ]);

        $enrollment = $service->completeLesson($this->peserta, $this->course, $this->lesson2);
        $this->assertSame(100, $enrollment->progress);
        $this->assertSame('Selesai', $enrollment->status);
    }

    public function test_double_complete_is_idempotent(): void
    {
        $service = app(LearningProgressService::class);
        $service->enroll($this->peserta, $this->course);

        $service->completeLesson($this->peserta, $this->course, $this->lesson1);
        $service->completeLesson($this->peserta, $this->course, $this->lesson1);

        $this->assertSame(1, DB::table('lesson_completions')
            ->where('user_id', $this->peserta->id)
            ->where('course_lesson_id', $this->lesson1->id)
            ->count());
    }

    public function test_locked_lesson_cannot_be_completed(): void
    {
        $service = app(LearningProgressService::class);
        $service->enroll($this->peserta, $this->course);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $service->completeLesson($this->peserta, $this->course, $this->lesson2);
    }

    public function test_lesson_player_requires_enrollment(): void
    {
        $response = $this->actingAs($this->peserta)
            ->get(route('peserta.kursus.lessons.show', [$this->course, $this->lesson1]));

        $response->assertForbidden();
    }
}
