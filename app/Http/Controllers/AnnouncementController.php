<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Kelola pengumuman (Super Admin).
 */
class AnnouncementController extends Controller
{
    public const DISK = 'local';

    public const DIR = 'announcements';

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));
        $now = now();

        $announcements = Announcement::query()
            ->with('creator:id,name')
            ->withCount([
                'reads as seen_count' => fn ($r) => $r->whereNotNull('seen_at'),
                'reads as dismissed_count' => fn ($r) => $r->whereNotNull('dismissed_at'),
            ])
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('title', 'like', '%'.addcslashes($q, '%_\\').'%')
                ->orWhere('body', 'like', '%'.addcslashes($q, '%_\\').'%')))
            ->when($status === 'published', fn ($query) => $query->published($now))
            ->when($status === 'scheduled', fn ($query) => $query->where('is_active', true)->where('starts_at', '>', $now))
            ->when($status === 'expired', fn ($query) => $query->where('is_active', true)->where('ends_at', '<=', $now))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = $this->stats();

        return view('announcements.admin.index', compact('announcements', 'stats', 'status', 'q'));
    }

    public function create(): View
    {
        return view('announcements.admin.form', ['announcement' => new Announcement([
            'category' => 'info', 'audience' => 'peserta', 'display_mode' => 'once', 'is_active' => true,
        ])]);
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $announcement = new Announcement;
        $this->fill($announcement, $request);
        $announcement->created_by = $request->user()->id;
        $announcement->updated_by = $request->user()->id;

        $stored = $this->storeAttachment($announcement, $request);

        try {
            $announcement->save();
        } catch (\Throwable $e) {
            $this->deleteFile($stored);
            throw $e;
        }

        return redirect()->route('announcements.index')
            ->with('success', __('Pengumuman ":t" berhasil dibuat.', ['t' => Str::limit($announcement->title, 60)]));
    }

    public function edit(Announcement $announcement): View
    {
        $announcement->loadCount([
            'reads as seen_count' => fn ($r) => $r->whereNotNull('seen_at'),
            'reads as dismissed_count' => fn ($r) => $r->whereNotNull('dismissed_at'),
        ]);

        return view('announcements.admin.form', compact('announcement'));
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $oldPath = $announcement->attachment_path;
        $this->fill($announcement, $request);
        $announcement->updated_by = $request->user()->id;

        $newPath = $this->storeAttachment($announcement, $request);
        if (! $newPath && $request->boolean('remove_attachment')) {
            $announcement->forceFill(['attachment_path' => null, 'attachment_name' => null, 'attachment_mime' => null, 'attachment_size' => null]);
        }

        try {
            $announcement->save();
        } catch (\Throwable $e) {
            $this->deleteFile($newPath);
            throw $e;
        }

        // File lama dihapus hanya setelah data tersimpan.
        if ($oldPath && $oldPath !== $announcement->attachment_path) {
            $this->deleteFile($oldPath);
        }

        return redirect()->route('announcements.index')->with('success', __('Pengumuman berhasil diperbarui.'));
    }

    public function toggle(Request $request, Announcement $announcement): JsonResponse|RedirectResponse
    {
        $request->validate(['is_active' => ['required', 'boolean']]);

        $announcement->forceFill([
            'is_active' => $request->boolean('is_active'),
            'updated_by' => $request->user()->id,
        ])->save();

        $message = $announcement->is_active ? __('Pengumuman dinyalakan.') : __('Pengumuman dimatikan.');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'is_active' => $announcement->is_active,
                'status' => $announcement->status(),
                'status_label' => self::statusLabel($announcement->status()),
                'message' => $message,
                'stats' => $this->stats(),
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $path = $announcement->attachment_path;

        DB::transaction(fn () => $announcement->delete());
        $this->deleteFile($path);

        return redirect()->route('announcements.index')->with('success', __('Pengumuman dihapus.'));
    }

    /** @return array<string, int> */
    private function stats(): array
    {
        $now = now();

        return [
            'published' => Announcement::query()->published($now)->count(),
            'scheduled' => Announcement::query()->where('is_active', true)->where('starts_at', '>', $now)->count(),
            'expired' => Announcement::query()->where('is_active', true)->where('ends_at', '<=', $now)->count(),
            'inactive' => Announcement::query()->where('is_active', false)->count(),
        ];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'published' => __('Sedang tayang'),
            'scheduled' => __('Terjadwal'),
            'expired' => __('Berakhir'),
            default => __('Nonaktif'),
        };
    }

    private function fill(Announcement $announcement, AnnouncementRequest $request): void
    {
        $data = $request->validated();

        $announcement->fill([
            'title' => $data['title'],
            'body' => filled($data['body'] ?? null) ? str_replace("\r\n", "\n", $data['body']) : null,
            'category' => $data['category'],
            'audience' => $data['audience'],
            'display_mode' => $data['display_mode'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'starts_at' => $request->wib('starts_at'),
            'ends_at' => $request->wib('ends_at'),
            'link_url' => filled($data['link_url'] ?? null) ? $data['link_url'] : null,
            'link_label' => filled($data['link_url'] ?? null) && filled($data['link_label'] ?? null) ? $data['link_label'] : null,
        ]);
    }

    /** Simpan lampiran ke disk privat (tidak bisa diakses langsung lewat URL publik). */
    private function storeAttachment(Announcement $announcement, AnnouncementRequest $request): ?string
    {
        $file = $request->file('attachment');
        if (! $file) {
            return null;
        }

        $path = $file->store(self::DIR, self::DISK);
        if (! $path) {
            return null;
        }

        $original = Str::of($file->getClientOriginalName())
            ->replaceMatches('/[^\pL\pN\.\-_ ()]+/u', '-')
            ->limit(180, '')
            ->trim()
            ->toString();

        $announcement->forceFill([
            'attachment_path' => $path,
            'attachment_name' => $original !== '' ? $original : 'lampiran.'.$file->extension(),
            'attachment_mime' => $file->getMimeType(),
            'attachment_size' => $file->getSize(),
        ]);

        return $path;
    }

    private function deleteFile(?string $path): void
    {
        if ($path && str_starts_with($path, self::DIR.'/')) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
