<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseForumThread;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class CourseForumController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
            new Middleware('permission:course view'),
        ];
    }

    public function storeThread(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('view', $course);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'thread_body' => ['required', 'string', 'max:5000'],
        ], [], [
            'title' => __('Judul diskusi'),
            'thread_body' => __('Isi diskusi'),
        ]);

        DB::transaction(function () use ($request, $course, $data): void {
            CourseForumThread::create([
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
                'title' => $data['title'],
                'body' => $data['thread_body'],
                'last_activity_at' => now(),
            ]);
        });

        return to_route('courses.show', [$course, 'tab' => 'forum'])
            ->with('success', __('Diskusi berhasil dibuat.'));
    }

    public function storeReply(Request $request, Course $course, CourseForumThread $thread): RedirectResponse
    {
        $this->authorize('view', $course);
        abort_unless($thread->course_id === $course->id, 404);

        $data = $request->validate([
            'reply_body' => ['required', 'string', 'max:5000'],
        ], [], [
            'reply_body' => __('Balasan'),
        ]);

        DB::transaction(function () use ($request, $thread, $data): void {
            $thread->replies()->create([
                'user_id' => $request->user()->id,
                'body' => $data['reply_body'],
            ]);

            $thread->update([
                'last_activity_at' => now(),
            ]);
        });

        return to_route('courses.show', [$course, 'tab' => 'forum'])
            ->with('success', __('Balasan berhasil dikirim.'));
    }
}
