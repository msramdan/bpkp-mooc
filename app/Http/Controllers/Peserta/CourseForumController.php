<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseForumLike;
use App\Models\CourseForumReply;
use App\Models\CourseForumThread;
use App\Services\ForumActivityNotifier;
use App\Support\PesertaAccess;
use App\Support\Roles;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $likeSummary = app(ForumActivityNotifier::class)->likeSummaryFor(
            (int) Auth::id(),
            $threads->pluck('id')->all(),
            $threads->flatMap(fn ($t) => $t->replies->pluck('id'))->all()
        );

        return view('peserta.kursus.forum', [
            'course' => $course,
            'threads' => $threads,
            'likeSummary' => $likeSummary,
        ]);
    }

    public function storeThread(Request $request, Course $course): RedirectResponse|JsonResponse
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

        $thread = DB::transaction(function () use ($course, $user, $data) {
            return CourseForumThread::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'title' => $data['title'],
                'body' => $data['thread_body'],
                'last_activity_at' => now(),
            ]);
        });

        $thread->load(['user:id,name,email,avatar', 'replies.user:id,name,email,avatar']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => __('Diskusi berhasil dibuat.'),
                'thread' => $this->threadPayload($thread, $course, [
                    'count' => 0,
                    'liked' => false,
                ]),
            ]);
        }

        return to_route('peserta.kursus.forum.index', $course)
            ->with('success', __('Diskusi berhasil dibuat.'));
    }

    public function storeReply(Request $request, Course $course, CourseForumThread $thread, ForumActivityNotifier $notifier): RedirectResponse|JsonResponse
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

        $reply = DB::transaction(function () use ($thread, $user, $data) {
            $reply = $thread->replies()->create([
                'user_id' => $user->id,
                'body' => $data['reply_body'],
            ]);

            $thread->update([
                'last_activity_at' => now(),
            ]);

            return $reply;
        });

        $reply->load('user:id,name,email,avatar');
        $thread->load(['user:id,name', 'replies.user:id,name']);
        $notifier->notifyThreadParticipantsOnReply($course, $thread, $reply, $user);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => __('Balasan berhasil dikirim.'),
                'reply' => $this->replyPayload($reply, $thread, $course, [
                    'count' => 0,
                    'liked' => false,
                ]),
                'replies_count' => $thread->replies()->count(),
                'mentionables' => $thread->mentionableParticipants()->values(),
            ]);
        }

        return to_route('peserta.kursus.forum.index', $course)
            ->with('success', __('Balasan berhasil dikirim.'));
    }

    public function updateThread(Request $request, Course $course, CourseForumThread $thread): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $course);
        $user = PesertaAccess::user();
        PesertaAccess::enrollmentForCourse($course);

        abort_unless($course->is_forum_open && $thread->course_id === $course->id, 404);
        abort_unless((string) $thread->user_id === (string) $user->id, 403);

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
        $like = app(ForumActivityNotifier::class)->likeSummaryFor(
            (int) Auth::id(),
            [$thread->id],
            []
        )['threads'][$thread->id] ?? ['count' => 0, 'liked' => false];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => __('Diskusi berhasil diperbarui.'),
                'thread' => $this->threadPayload($thread, $course, $like),
            ]);
        }

        return to_route('peserta.kursus.forum.index', $course)
            ->with('success', __('Diskusi berhasil diperbarui.'));
    }

    public function updateReply(Request $request, Course $course, CourseForumThread $thread, CourseForumReply $reply): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $course);
        $user = PesertaAccess::user();
        PesertaAccess::enrollmentForCourse($course);

        abort_unless($course->is_forum_open && $thread->course_id === $course->id && $reply->thread_id === $thread->id, 404);
        abort_unless((string) $reply->user_id === (string) $user->id, 403);

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
        $like = app(ForumActivityNotifier::class)->likeSummaryFor(
            (int) Auth::id(),
            [],
            [$reply->id]
        )['replies'][$reply->id] ?? ['count' => 0, 'liked' => false];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => __('Balasan berhasil diperbarui.'),
                'reply' => $this->replyPayload($reply, $thread, $course, $like),
            ]);
        }

        return to_route('peserta.kursus.forum.index', $course)
            ->with('success', __('Balasan berhasil diperbarui.'));
    }

    public function toggleLike(Request $request, Course $course, CourseForumThread $thread, ForumActivityNotifier $notifier): JsonResponse
    {
        $this->authorize('view', $course);
        $user = PesertaAccess::user();
        PesertaAccess::enrollmentForCourse($course);

        abort_unless($course->is_forum_open && $thread->course_id === $course->id, 404);

        $data = $request->validate([
            'target_type' => ['required', 'in:thread,reply'],
            'target_id' => ['required', 'uuid'],
        ]);

        if ($data['target_type'] === CourseForumLike::TYPE_THREAD) {
            abort_unless($data['target_id'] === $thread->id, 404);
        } else {
            $exists = CourseForumReply::query()
                ->whereKey($data['target_id'])
                ->where('thread_id', $thread->id)
                ->exists();
            abort_unless($exists, 404);
        }

        $result = $notifier->toggleLike(
            $user,
            $course,
            $thread,
            $data['target_type'],
            $data['target_id']
        );

        return response()->json([
            'message' => $result['liked'] ? __('Disukai') : __('Batal suka'),
            'liked' => $result['liked'],
            'likes_count' => $result['likes_count'],
            'target_type' => $data['target_type'],
            'target_id' => $data['target_id'],
        ]);
    }

    /** @param  array{count?:int, liked?:bool}|null  $like */
    private function replyPayload(CourseForumReply $reply, CourseForumThread $thread, Course $course, ?array $like = null): array
    {
        $mentionables = $thread->mentionableParticipants();
        $like ??= ['count' => 0, 'liked' => false];

        return [
            'id' => $reply->id,
            'body' => $reply->body,
            'body_html' => CourseForumThread::renderBodyWithMentions($reply->body, $mentionables),
            'created_at' => $reply->created_at?->translatedFormat('d M Y H:i'),
            'is_admin' => (bool) $reply->user?->hasRole(Roles::SUPER_ADMIN),
            'can_edit' => (bool) Auth::id() && (int) $reply->user_id === (int) Auth::id(),
            'update_url' => route('peserta.kursus.forum.reply.update', [$course, $thread, $reply]),
            'like_url' => route('peserta.kursus.forum.like', [$course, $thread]),
            'likes_count' => (int) ($like['count'] ?? 0),
            'liked' => (bool) ($like['liked'] ?? false),
            'user' => [
                'id' => $reply->user?->id,
                'name' => $reply->user?->name,
                'avatar' => $reply->user?->avatar,
            ],
        ];
    }

    /** @param  array{count?:int, liked?:bool}|null  $like */
    private function threadPayload(CourseForumThread $thread, Course $course, ?array $like = null): array
    {
        $mentionables = $thread->mentionableParticipants();
        $like ??= ['count' => 0, 'liked' => false];

        return [
            'id' => $thread->id,
            'title' => $thread->title,
            'body' => $thread->body,
            'body_html' => CourseForumThread::renderBodyWithMentions($thread->body, $mentionables),
            'created_at' => $thread->created_at?->translatedFormat('d M Y · H:i'),
            'is_admin' => (bool) $thread->user?->hasRole(Roles::SUPER_ADMIN),
            'can_edit' => (bool) Auth::id() && (int) $thread->user_id === (int) Auth::id(),
            'replies_count' => $thread->replies->count(),
            'reply_url' => route('peserta.kursus.forum.reply', [$course, $thread]),
            'update_url' => route('peserta.kursus.forum.update', [$course, $thread]),
            'like_url' => route('peserta.kursus.forum.like', [$course, $thread]),
            'likes_count' => (int) ($like['count'] ?? 0),
            'liked' => (bool) ($like['liked'] ?? false),
            'mentionables' => $mentionables->values(),
            'user' => [
                'id' => $thread->user?->id,
                'name' => $thread->user?->name,
                'avatar' => $thread->user?->avatar,
            ],
        ];
    }
}
