<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseForumReply;
use App\Models\CourseForumThread;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
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

    public function storeThread(Request $request, Course $course): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $course);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'thread_body' => ['required', 'string', 'max:5000'],
        ], [], [
            'title' => __('Judul diskusi'),
            'thread_body' => __('Isi diskusi'),
        ]);

        $thread = DB::transaction(function () use ($request, $course, $data) {
            return CourseForumThread::create([
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
                'title' => $data['title'],
                'body' => $data['thread_body'],
                'last_activity_at' => now(),
            ]);
        });

        $thread->load(['user:id,name,email,avatar', 'replies.user:id,name,email,avatar']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => __('Diskusi berhasil dibuat.'),
                'thread' => $this->threadPayload($thread, $course),
            ]);
        }

        return to_route('courses.show', [$course, 'tab' => 'forum'])
            ->with('success', __('Diskusi berhasil dibuat.'));
    }

    public function storeReply(Request $request, Course $course, CourseForumThread $thread): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $course);
        abort_unless($thread->course_id === $course->id, 404);

        $data = $request->validate([
            'reply_body' => ['required', 'string', 'max:5000'],
        ], [], [
            'reply_body' => __('Balasan'),
        ]);

        $reply = DB::transaction(function () use ($request, $thread, $data) {
            $reply = $thread->replies()->create([
                'user_id' => $request->user()->id,
                'body' => $data['reply_body'],
            ]);

            $thread->update([
                'last_activity_at' => now(),
            ]);

            return $reply;
        });

        $reply->load('user:id,name,email,avatar');
        $thread->load(['user:id,name', 'replies.user:id,name']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => __('Balasan berhasil dikirim.'),
                'reply' => $this->replyPayload($reply, $thread),
                'replies_count' => $thread->replies()->count(),
                'mentionables' => $thread->mentionableParticipants()->values(),
            ]);
        }

        return to_route('courses.show', [$course, 'tab' => 'forum'])
            ->with('success', __('Balasan berhasil dikirim.'));
    }

    public function updateThread(Request $request, Course $course, CourseForumThread $thread): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $course);
        abort_unless($thread->course_id === $course->id, 404);
        abort_unless((string) $thread->user_id === (string) $request->user()->id, 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'thread_body' => ['required', 'string', 'max:5000'],
        ], [], [
            'title' => __('Judul diskusi'),
            'thread_body' => __('Isi diskusi'),
        ]);

        $thread->update([
            'title' => $data['title'],
            'body' => $data['thread_body'],
            'last_activity_at' => now(),
        ]);

        $thread->load(['user:id,name,email,avatar', 'replies.user:id,name,email,avatar']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => __('Diskusi berhasil diperbarui.'),
                'thread' => $this->threadPayload($thread, $course),
            ]);
        }

        return to_route('courses.show', [$course, 'tab' => 'forum'])
            ->with('success', __('Diskusi berhasil diperbarui.'));
    }

    public function updateReply(Request $request, Course $course, CourseForumThread $thread, CourseForumReply $reply): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $course);
        abort_unless($thread->course_id === $course->id && $reply->thread_id === $thread->id, 404);
        abort_unless((string) $reply->user_id === (string) $request->user()->id, 403);

        $data = $request->validate([
            'reply_body' => ['required', 'string', 'max:5000'],
        ], [], [
            'reply_body' => __('Balasan'),
        ]);

        $reply->update([
            'body' => $data['reply_body'],
        ]);

        $reply->load('user:id,name,email,avatar');
        $thread->load(['user:id,name', 'replies.user:id,name']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => __('Balasan berhasil diperbarui.'),
                'reply' => $this->replyPayload($reply, $thread),
            ]);
        }

        return to_route('courses.show', [$course, 'tab' => 'forum'])
            ->with('success', __('Balasan berhasil diperbarui.'));
    }

    /** @return array<string, mixed> */
    private function replyPayload(CourseForumReply $reply, CourseForumThread $thread): array
    {
        $mentionables = $thread->mentionableParticipants();

        return [
            'id' => $reply->id,
            'body' => $reply->body,
            'body_html' => CourseForumThread::renderBodyWithMentions($reply->body, $mentionables),
            'created_at' => $reply->created_at?->translatedFormat('d M Y H:i'),
            'is_admin' => (bool) $reply->user?->hasRole(Roles::SUPER_ADMIN),
            'can_edit' => (bool) Auth::id() && (int) $reply->user_id === (int) Auth::id(),
            'update_url' => route('courses.forum.reply.update', [$thread->course_id, $thread->id, $reply->id]),
            'user' => [
                'id' => $reply->user?->id,
                'name' => $reply->user?->name,
                'avatar' => $reply->user?->avatar,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function threadPayload(CourseForumThread $thread, Course $course): array
    {
        $mentionables = $thread->mentionableParticipants();

        return [
            'id' => $thread->id,
            'title' => $thread->title,
            'body' => $thread->body,
            'body_html' => CourseForumThread::renderBodyWithMentions($thread->body, $mentionables),
            'created_at' => $thread->created_at?->translatedFormat('d M Y · H:i'),
            'is_admin' => (bool) $thread->user?->hasRole(Roles::SUPER_ADMIN),
            'can_edit' => (bool) Auth::id() && (int) $thread->user_id === (int) Auth::id(),
            'replies_count' => $thread->replies->count(),
            'reply_url' => route('courses.forum.reply', [$course, $thread]),
            'update_url' => route('courses.forum.update', [$course, $thread]),
            'mentionables' => $mentionables->values(),
            'user' => [
                'id' => $thread->user?->id,
                'name' => $thread->user?->name,
                'avatar' => $thread->user?->avatar,
            ],
        ];
    }
}
