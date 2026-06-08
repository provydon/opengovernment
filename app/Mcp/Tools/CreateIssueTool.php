<?php

namespace App\Mcp\Tools;

use App\Services\Civic\IssueRegistrarService;
use App\Services\Civic\LgaResolverService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use RuntimeException;

#[Description('File a new issue on behalf of the authenticated citizen. The MCP session must be authenticated as a verified user.')]
class CreateIssueTool extends Tool
{
    public function handle(Request $request): Response
    {
        $user = $request->user();
        if (! $user) {
            return Response::error('Authenticate the MCP session as a verified citizen before calling create_issue.');
        }

        $lga = app(LgaResolverService::class)->resolve(
            $user,
            id: $request->get('local_government_id'),
            name: $request->get('local_government_name'),
        );

        if (! $lga) {
            return Response::error('Could not resolve a local government for this user.');
        }

        try {
            $issue = app(IssueRegistrarService::class)->create(
                user: $user,
                lga: $lga,
                title: (string) $request->get('title'),
                body: (string) $request->get('body'),
                category: $request->get('category'),
            );
        } catch (RuntimeException $e) {
            return Response::error($e->getMessage());
        }

        return Response::json([
            'created' => true,
            'issue' => [
                'id' => $issue->id,
                'slug' => $issue->slug,
                'title' => $issue->title,
                'url' => url('/issues/'.$issue->slug),
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'body' => $schema->string()->required(),
            'category' => $schema->string(),
            'local_government_id' => $schema->integer(),
            'local_government_name' => $schema->string(),
        ];
    }
}
