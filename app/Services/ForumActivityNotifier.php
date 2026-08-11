<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseForumLike;
use App\Models\CourseForumReply;
use App\Models\CourseForumThread;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForumActivityNotifier
{
    /**
     * Extract mentioned user IDs from body against a list of candidates.
     *
     * @param  Collection<int, array{id:int,name:string}>|iterable<int, array{id:int,name:string}>  $candidates
     * @return list<int>
     */
    public function mentionedUserIds(string $body, iterable $candidates): array
    {
        $ids = [];
        $normalized = ' '.$body.' ';

        $sorted = collect($candidates)
            ->filter(fn ($row) => ! empty($row['name']) && ! empty($row['id']))
            ->sortByDesc(fn ($row) => mb_strlen((string) $row['name']))
            ->values();

        foreach ($sorted as $row) {
            $needle = '@'.$row['name'];
            if (mb_stripos($normalized, $needle) !== false) {
                $ids[] = (int) $row['id'];
                // Prevent shorter-name partial collisions after longer match.
                $normalized = str_ireplace($needle, ' ', $normalized);
            }
        }

        return array_values(array_unique($ids));
    }

    public function notifyThreadParticipantsOnReply(
        Course $course,
        CourseForumThread $thread,
        CourseForumReply $reply,
        User $actor
    ): void {
        $thread->loadMissing(['user:id,name', 'replies.user:id,name']);

        $participants = collect([$thread->user])
            ->merge($thread->replies->pluck('user'))
            ->filter()
            ->unique('id')
            ->reject(fn (User $user) => (int) $user->id === (int) $actor->id)
            ->values();

        $mentionables = $participants->map(fn (User $user) => [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
        ]);

        $mentionedIds = $this->mentionedUserIds((string) $reply->body, $mentionables);
        $snippet = $this->snippet((string) $reply->body);

        foreach ($participants as $recipient) {
            $recipientId = (int) $recipient->id;
            $isMentioned = in_array($recipientId, $mentionedIds, true);
            $url = $this->forumUrl($course, $thread, $recipient);

            $this->create([
                'user_id' => $recipientId,
                'actor_id' => $actor->id,
                'type' => $isMentioned
                    ? UserNotification::TYPE_FORUM_MENTION
                    : UserNotification::TYPE_FORUM_REPLY,
                'title' => $isMentioned
                    ? __(':name menandai Anda di forum', ['name' => $actor->name])
                    : __(':name membalas di forum kursus', ['name' => $actor->name]),
                'body' => $snippet,
                'data' => [
                    'course_id' => $course->id,
                    'course_title' => $course->title ?? $course->judul,
                    'thread_id' => $thread->id,
                    'thread_title' => $thread->title,
                    'reply_id' => $reply->id,
                    'url' => $url,
                    'icon' => $isMentioned ? 'mention' : 'reply',
                ],
            ]);
        }
    }

    /**
     * @param  'thread'|'reply'  $targetType
     */
    public function notifyLike(
        Course $course,
        CourseForumThread $thread,
        User $actor,
        User $owner,
        string $targetType,
        string $targetId
    ): void {
        if ((int) $owner->id === (int) $actor->id) {
            return;
        }

        $url = $this->forumUrl($course, $thread, $owner);

        $existing = UserNotification::query()
            ->where('user_id', $owner->id)
            ->where('type', UserNotification::TYPE_FORUM_LIKE)
            ->whereNull('read_at')
            ->where('data->target_type', $targetType)
            ->where('data->target_id', $targetId)
            ->latest('id')
            ->first();

        if ($existing) {
            $data = $existing->data ?? [];
            $actorIds = collect($data['actor_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->push((int) $actor->id)
                ->unique()
                ->values()
                ->all();

            $count = count($actorIds);
            $names = User::query()
                ->whereIn('id', array_slice($actorIds, -3))
                ->pluck('name')
                ->all();

            $existing->update([
                'actor_id' => $actor->id,
                'title' => $this->likeTitle($names, $count),
                'body' => $thread->title,
                'data' => array_merge($data, [
                    'actor_ids' => $actorIds,
                    'like_count' => $count,
                    'course_id' => $course->id,
                    'course_title' => $course->title ?? $course->judul,
                    'thread_id' => $thread->id,
                    'thread_title' => $thread->title,
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                    'url' => $url,
                    'icon' => 'like',
                ]),
                'updated_at' => now(),
            ]);

            return;
        }

        $this->create([
            'user_id' => $owner->id,
            'actor_id' => $actor->id,
            'type' => UserNotification::TYPE_FORUM_LIKE,
            'title' => $this->likeTitle([$actor->name], 1),
            'body' => $thread->title,
            'data' => [
                'actor_ids' => [(int) $actor->id],
                'like_count' => 1,
                'course_id' => $course->id,
                'course_title' => $course->title ?? $course->judul,
                'thread_id' => $thread->id,
                'thread_title' => $thread->title,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'url' => $url,
                'icon' => 'like',
            ],
        ]);
    }

    /**
     * @param  list<string>  $names
     */
    private function likeTitle(array $names, int $count): string
    {
        $names = array_values(array_filter($names));

        if ($count <= 1) {
            return __(':name menyukai postingan Anda', ['name' => $names[0] ?? __('Seseorang')]);
        }

        if ($count === 2) {
            return __(':name dan :other menyukai postingan Anda', [
                'name' => $names[0] ?? __('Seseorang'),
                'other' => $names[1] ?? __('orang lain'),
            ]);
        }

        $others = max(0, $count - 1);

        return __(':name dan :count orang lain menyukai postingan Anda', [
            'name' => $names[count($names) - 1] ?? $names[0] ?? __('Seseorang'),
            'count' => $others,
        ]);
    }

    /** @param  array<string, mixed>  $payload */
    private function create(array $payload): UserNotification
    {
        return UserNotification::query()->create($payload);
    }

    private function snippet(string $body, int $limit = 120): string
    {
        $clean = trim(preg_replace('/\s+/u', ' ', $body) ?? $body);

        return Str::limit($clean, $limit);
    }

    private function forumUrl(Course $course, CourseForumThread $thread, User $viewer): string
    {
        if (method_exists($viewer, 'hasRole') && $viewer->hasRole(\App\Support\Roles::SUPER_ADMIN)) {
            return route('courses.show', [$course, 'tab' => 'forum']).'#thread-'.$thread->id;
        }

        return route('peserta.kursus.forum.index', $course).'#thread-'.$thread->id;
    }

    /**
     * Toggle like and optionally notify post owner.
     *
     * @return array{liked: bool, likes_count: int}
     */
    public function toggleLike(
        User $actor,
        Course $course,
        CourseForumThread $thread,
        string $targetType,
        string $targetId
    ): array {
        return DB::transaction(function () use ($actor, $course, $thread, $targetType, $targetId) {
            $existing = CourseForumLike::query()
                ->where('user_id', $actor->id)
                ->where('likeable_type', $targetType)
                ->where('likeable_id', $targetId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->delete();
            } else {
                CourseForumLike::query()->create([
                    'user_id' => $actor->id,
                    'likeable_type' => $targetType,
                    'likeable_id' => $targetId,
                ]);

                $owner = null;
                if ($targetType === CourseForumLike::TYPE_THREAD) {
                    $owner = $thread->user;
                } else {
                    $reply = CourseForumReply::query()->find($targetId);
                    $owner = $reply?->user;
                }

                if ($owner) {
                    $this->notifyLike($course, $thread, $actor, $owner, $targetType, $targetId);
                }
            }

            $count = CourseForumLike::query()
                ->where('likeable_type', $targetType)
                ->where('likeable_id', $targetId)
                ->count();

            return [
                'liked' => $existing === null,
                'likes_count' => $count,
            ];
        });
    }

    /**
     * @param  list<string>  $threadIds
     * @param  list<string>  $replyIds
     * @return array{threads: array<string, array{count:int, liked:bool}>, replies: array<string, array{count:int, liked:bool}>}
     */
    public function likeSummaryFor(int $userId, array $threadIds, array $replyIds): array
    {
        $threadIds = array_values(array_unique(array_filter($threadIds)));
        $replyIds = array_values(array_unique(array_filter($replyIds)));

        $out = ['threads' => [], 'replies' => []];

        if ($threadIds === [] && $replyIds === []) {
            return $out;
        }

        $likes = CourseForumLike::query()
            ->select(['likeable_type', 'likeable_id', 'user_id'])
            ->where(function ($q) use ($threadIds, $replyIds) {
                if ($threadIds !== []) {
                    $q->orWhere(function ($inner) use ($threadIds) {
                        $inner->where('likeable_type', CourseForumLike::TYPE_THREAD)
                            ->whereIn('likeable_id', $threadIds);
                    });
                }
                if ($replyIds !== []) {
                    $q->orWhere(function ($inner) use ($replyIds) {
                        $inner->where('likeable_type', CourseForumLike::TYPE_REPLY)
                            ->whereIn('likeable_id', $replyIds);
                    });
                }
            })
            ->get();

        $threadCounts = [];
        $replyCounts = [];
        $threadLiked = [];
        $replyLiked = [];

        foreach ($likes as $like) {
            if ($like->likeable_type === CourseForumLike::TYPE_THREAD) {
                $threadCounts[$like->likeable_id] = ($threadCounts[$like->likeable_id] ?? 0) + 1;
                if ((int) $like->user_id === $userId) {
                    $threadLiked[$like->likeable_id] = true;
                }
            } else {
                $replyCounts[$like->likeable_id] = ($replyCounts[$like->likeable_id] ?? 0) + 1;
                if ((int) $like->user_id === $userId) {
                    $replyLiked[$like->likeable_id] = true;
                }
            }
        }

        foreach ($threadIds as $id) {
            $out['threads'][$id] = [
                'count' => (int) ($threadCounts[$id] ?? 0),
                'liked' => (bool) ($threadLiked[$id] ?? false),
            ];
        }
        foreach ($replyIds as $id) {
            $out['replies'][$id] = [
                'count' => (int) ($replyCounts[$id] ?? 0),
                'liked' => (bool) ($replyLiked[$id] ?? false),
            ];
        }

        return $out;
    }
}
