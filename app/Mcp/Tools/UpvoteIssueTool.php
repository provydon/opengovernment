<?php

namespace App\Mcp\Tools;

use App\Models\Issue;
use App\Services\Civic\IssueRegistrarService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use RuntimeException;

#[Description('Add the authenticated citizen\'s upvote to an existing issue.')]
class UpvoteIssueTool extends Tool
{
    public function handle(Request $request): Response
    {
        $user = $request->user();
        if (! $user) {
            return Response::error('Authenticate as a verified citizen first.');
        }

        $issue = Issue::find($request->get('issue_id'));
        if (! $issue) {
            return Response::error('Issue not found.');
        }

        try {
            $updated = app(IssueRegistrarService::class)->vote($user, $issue, 1);
        } catch (RuntimeException $e) {
            return Response::error($e->getMessage());
        }

        return Response::json([
            'voted' => true,
            'issue' => [
                'id' => $updated->id,
                'slug' => $updated->slug,
                'upvotes' => $updated->upvotes,
                'score' => $updated->score,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'issue_id' => $schema->integer()->required(),
        ];
    }
}
