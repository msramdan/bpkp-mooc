<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CourseForumThread extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_id',
        'user_id',
        'title',
        'body',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CourseForumReply::class, 'thread_id')->oldest();
    }

    /**
     * Users who already participated in this thread (author + previous commenters).
     *
     * @return Collection<int, array{id:int,name:string}>
     */
    public function mentionableParticipants(): Collection
    {
        $users = collect();

        if ($this->relationLoaded('user') && $this->user) {
            $users->push($this->user);
        } elseif ($this->user_id) {
            $users->push($this->user()->first());
        }

        $replies = $this->relationLoaded('replies')
            ? $this->replies
            : $this->replies()->with('user:id,name')->get();

        foreach ($replies as $reply) {
            if ($reply->user) {
                $users->push($reply->user);
            }
        }

        return $users
            ->filter()
            ->unique('id')
            ->reject(fn ($user) => (int) $user->id === (int) auth()->id())
            ->values()
            ->map(fn ($user) => [
                'id' => (int) $user->id,
                'name' => (string) $user->name,
            ]);
    }

    /**
     * @param  Collection<int, array{id?:int,name:string}>|array<int, array{id?:int,name:string}>  $mentionables
     */
    public static function renderBodyWithMentions(?string $body, Collection|array $mentionables = []): string
    {
        $safe = e((string) $body);
        $names = collect($mentionables)
            ->pluck('name')
            ->filter()
            ->unique()
            ->sortByDesc(fn ($name) => mb_strlen((string) $name))
            ->values();

        foreach ($names as $name) {
            $needle = '@'.$name;
            $safe = str_replace(
                e($needle),
                '<span class="pf-mention">'.e($needle).'</span>',
                $safe
            );
        }

        return $safe;
    }
}
