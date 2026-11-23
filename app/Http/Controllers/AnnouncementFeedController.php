<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pengumuman dari sisi pengguna (peserta / admin): daftar, tutup pop up, unduh lampiran.
 */
class AnnouncementFeedController extends Controller
{
    public function __construct(private readonly AnnouncementService $service) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $announcements = $this->service->listFor($user);

        $seenIds = $user->id
            ? \App\Models\AnnouncementRead::query()->where('user_id', $user->id)->whereNotNull('seen_at')
                ->whereIn('announcement_id', $announcements->pluck('id'))->pluck('announcement_id')->all()
            : [];

        // Membuka halaman ini = semua pengumuman dianggap sudah dilihat.
        $this->service->markSeen($user, $announcements->pluck('id'));

        return view('announcements.feed', compact('announcements', 'seenIds'));
    }

    public function dismiss(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:50'],
            'ids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->service->dismiss($request->user(), $data['ids']);

        return response()->json(['ok' => true, 'dismissed' => $count]);
    }

    public function attachment(Request $request, Announcement $announcement): StreamedResponse
    {
        $user = $request->user();
        $isManager = $user->can('announcement manage');

        // Peserta hanya boleh mengakses lampiran pengumuman yang sedang tayang untuknya.
        abort_unless($announcement->hasAttachment() && ($isManager || $announcement->isVisibleTo($user)), 404);
        abort_unless(Storage::disk(AnnouncementController::DISK)->exists($announcement->attachment_path), 404);

        $inline = $announcement->attachmentIsImage() && $request->boolean('inline');
        $headers = [
            'Content-Type' => $announcement->attachmentIsImage() ? $announcement->attachment_mime : 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=300',
        ];

        $disk = Storage::disk(AnnouncementController::DISK);

        return $inline
            ? $disk->response($announcement->attachment_path, $announcement->attachment_name, $headers, 'inline')
            : $disk->download($announcement->attachment_path, $announcement->attachment_name, $headers);
    }
}
