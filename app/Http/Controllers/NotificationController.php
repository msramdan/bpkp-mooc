<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $filter = $request->query('filter', 'all'); // all|unread
        $since = $request->query('since');
        $limit = min(40, max(5, (int) $request->query('limit', 20)));

        $query = UserNotification::query()
            ->where('user_id', $user->id)
            ->with(['actor:id,name,avatar'])
            ->latest('created_at')
            ->latest('id');

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        if (is_string($since) && $since !== '') {
            try {
                $sinceAt = Carbon::parse($since);
                $query->where('created_at', '>', $sinceAt);
            } catch (\Throwable) {
                // ignore invalid since
            }
        }

        $items = $query->limit($limit)->get();

        $unreadCount = UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'server_time' => now()->toIso8601String(),
            'items' => $items->map(fn (UserNotification $n) => $this->payload($n))->values(),
        ]);
    }

    public function markRead(Request $request, UserNotification $notification): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && (int) $notification->user_id === (int) $user->id, 403);

        $notification->markAsRead();

        return response()->json([
            'ok' => true,
            'unread_count' => UserNotification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->count(),
            'item' => $this->payload($notification->fresh(['actor:id,name,avatar'])),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'ok' => true,
            'unread_count' => 0,
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(UserNotification $n): array
    {
        $data = $n->data ?? [];

        return [
            'id' => $n->id,
            'type' => $n->type,
            'title' => $n->title,
            'body' => $n->body,
            'url' => $data['url'] ?? null,
            'icon' => $data['icon'] ?? 'default',
            'is_unread' => $n->isUnread(),
            'created_at' => $n->created_at?->toIso8601String(),
            'created_human' => $n->created_at?->diffForHumans(),
            'actor' => $n->actor ? [
                'id' => $n->actor->id,
                'name' => $n->actor->name,
                'avatar' => $n->actor->avatar,
            ] : null,
            'meta' => [
                'course_title' => $data['course_title'] ?? null,
                'thread_title' => $data['thread_title'] ?? null,
                'like_count' => $data['like_count'] ?? null,
            ],
        ];
    }
}
