<?php

namespace App\Http\Controllers;

use App\Ai\Agents\CivicAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\MessageRole;
use Throwable;

class ChatController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Chat/Index');
    }

    /**
     * Run one turn of the civic agent. The frontend keeps the full
     * history on its side and POSTs the whole thing back each turn —
     * keeps the server stateless and makes the streaming UX easier.
     */
    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'history' => ['nullable', 'array'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string'],
        ]);

        $history = collect($data['history'] ?? [])
            ->map(fn ($m) => new Message(
                $m['role'] === 'user' ? MessageRole::User : MessageRole::Assistant,
                $m['content'],
            ))
            ->all();

        $agent = new CivicAgent(actor: $request->user(), history: $history);

        try {
            $response = $agent->prompt(
                $data['message'],
                provider: config('opengovernment.ai.driver'),
                model: config('opengovernment.ai.model'),
            );

            return response()->json([
                'reply' => $response->text,
                'usage' => [
                    'input_tokens' => $response->usage->inputTokens ?? null,
                    'output_tokens' => $response->usage->outputTokens ?? null,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'reply' => "I couldn't reach the AI provider. The site admin needs to set the API key in `.env` (".config('opengovernment.ai.driver').").",
                'error' => $e->getMessage(),
            ], 200); // 200 so the chat UI still renders the message
        }
    }
}
