<?php

namespace App\Http\Controllers\AiAssistant;

use App\Http\Controllers\Controller;
use App\Models\AiAssistantConversation;
use App\Models\AiAssistantMessage;
use App\Models\User;
use App\Services\AiAssistant\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Endpoint AI Assistant untuk user. Identitas user selalu dari sesi login;
 * percakapan hanya bisa diakses pemiliknya (query di-scope ke user_id sesi).
 */
class ChatController extends Controller
{
    public const SUGGESTED_QUESTIONS = [
        'Berapa kursus yang sedang saya ikuti?',
        'Bagaimana progres belajar saya?',
        'Apakah sertifikat saya sudah tersedia?',
        'Apa yang harus saya selesaikan hari ini?',
        'Bagaimana cara menggunakan fitur MOOC?',
    ];

    public function __construct(private readonly AiAssistantService $assistant) {}

    public function index(Request $request): View
    {
        $user = $this->user($request);
        $access = $this->assistant->access($user);

        $conversations = AiAssistantConversation::query()
            ->where('user_id', $user->id)
            ->orderByDesc('last_message_at')
            ->limit(50)
            ->get(['id', 'title', 'last_message_at']);

        return view('ai-assistant.index', [
            'access' => $access,
            'quota' => $this->assistant->quotaPayload($user),
            'conversations' => $conversations,
            'suggestions' => self::SUGGESTED_QUESTIONS,
            'quotaExceededMessage' => $this->assistant->quotaExceededMessage(),
            'maxLength' => max(100, (int) app(\App\Services\AiAssistant\AiAssistantSettings::class)->get('max_question_length', 1000)),
        ]);
    }

    public function conversations(Request $request): JsonResponse
    {
        $user = $this->user($request);

        $items = AiAssistantConversation::query()
            ->where('user_id', $user->id)
            ->orderByDesc('last_message_at')
            ->limit(50)
            ->get(['id', 'title', 'last_message_at'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'last_message_at' => $c->last_message_at?->diffForHumans(),
            ]);

        return response()->json(['ok' => true, 'conversations' => $items]);
    }

    public function messages(Request $request, string $conversation): JsonResponse
    {
        $user = $this->user($request);
        $model = $this->ownConversation($user, $conversation);

        $messages = AiAssistantMessage::query()
            ->where('conversation_id', $model->id)
            ->where('user_id', $user->id)
            ->with('requestLog:id,status')
            ->orderBy('created_at')
            ->orderByDesc('role')
            ->get()
            ->map(fn (AiAssistantMessage $m) => $this->assistant->messagePayload($m, $m->requestLog?->status));

        return response()->json([
            'ok' => true,
            'conversation' => ['id' => $model->id, 'title' => $model->title],
            'messages' => $messages,
        ]);
    }

    public function ask(Request $request): JsonResponse
    {
        $user = $this->user($request);

        $access = $this->assistant->access($user);
        if (! $access['allowed']) {
            return response()->json(['ok' => false, 'type' => 'disabled', 'message' => $access['reason']], 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'conversation_id' => ['nullable', 'uuid'],
        ]);

        $conversation = isset($validated['conversation_id'])
            ? $this->ownConversation($user, $validated['conversation_id'])
            : null;

        $result = $this->assistant->ask($user, $validated['message'], $conversation, $request);
        $status = $result['status'];
        unset($result['status']);

        return response()->json($result, $status);
    }

    public function quota(Request $request): JsonResponse
    {
        $user = $this->user($request);

        return response()->json([
            'ok' => true,
            'access' => $this->assistant->access($user),
            'quota' => $this->assistant->quotaPayload($user),
        ]);
    }

    private function ownConversation(User $user, string $id): AiAssistantConversation
    {
        // Scope ke user sesi: ID milik user lain menghasilkan 404 (tidak bocor keberadaannya).
        return AiAssistantConversation::query()
            ->where('user_id', $user->id)
            ->whereKey($id)
            ->firstOrFail();
    }

    private function user(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
