<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseForumThread;
use App\Support\PesertaAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseForumController extends Controller
{
    public function index(Course $course): View
    {
        $this->authorize('view', $course);
        PesertaAccess::enrollmentForCourse($course);

        abort_unless($course->is_forum_open, 404);

        $threads = $course->forumThreads()
            ->with([
                'user:id,name,email,avatar',
                'replies' => fn ($query) => $query->with('user:id,name,email,avatar')->oldest(),
            ])
            ->get();

        return view('peserta.kursus.forum', [
            'course' => $course,
            'threads' => $threads,
        ]);
    }

    public function storeThread(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('view', $course);
        $user = PesertaAccess::user();
        PesertaAccess::enrollmentForCourse($course);

        abort_unless($course->is_forum_open, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'thread_body' => ['required', 'string', 'max:5000'],
        ], [], [
            'title' => __('Judul diskusi'),
            'thread_body' => __('Isi diskusi'),
        ]);

        DB::transaction(function () use ($course, $user, $data): void {
            CourseForumThread::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'title' => $data['title'],
                'body' => $data['thread_body'],
                'last_activity_at' => now(),
            ]);
        });

        return to_route('peserta.kursus.forum.index', $course)
            ->with('success', __('Diskusi berhasil dibuat.'));
    }

    public function storeReply(Request $request, Course $course, CourseForumThread $thread): RedirectResponse
    {
        $this->authorize('view', $course);
        $user = PesertaAccess::user();
        PesertaAccess::enrollmentForCourse($course);

        abort_unless($course->is_forum_open && $thread->course_id === $course->id, 404);

        $data = $request->validate([
            'reply_body' => ['required', 'string', 'max:5000'],
        ], [], [
            'reply_body' => __('Balasan'),
        ]);

        DB::transaction(function () use ($thread, $user, $data): void {
            $thread->replies()->create([
                'user_id' => $user->id,
                'body' => $data['reply_body'],
            ]);

            $thread->update([
                'last_activity_at' => now(),
            ]);
        });

        return to_route('peserta.kursus.forum.index', $course)
            ->with('success', __('Balasan berhasil dikirim.'));
    }
}
