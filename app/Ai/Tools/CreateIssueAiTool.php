<?php

namespace App\Ai\Tools;

use App\Models\User;
use App\Services\Civic\IssueRegistrarService;
use App\Services\Civic\LgaResolverService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use RuntimeException;
use Stringable;

class CreateIssueAiTool implements Tool
{
    public function __construct(
        private IssueRegistrarService $registrar,
        private LgaResolverService $lgas,
        private ?User $actor = null,
    ) {}

    public function description(): Stringable|string
    {
        return 'File a brand new issue for a local government. Only call this AFTER calling search_similar_issues and confirming with the user that none of the matches describe the same problem. Requires a signed-in, identity-verified citizen.';
    }

    public function handle(Request $request): Stringable|string
    {
        if (! $this->actor) {
            return json_encode(['error' => 'not_authenticated', 'hint' => 'Tell the user they need to sign in and verify their identity to file an issue.']);
        }

        $lga = $this->lgas->resolve(
            $this->actor,
            id: $request->get('local_government_id'),
            name: $request->get('local_government_name'),
        );

        if (! $lga) {
            return json_encode(['error' => 'no_lga_resolved']);
        }

        try {
            $issue = $this->registrar->create(
                user: $this->actor,
                lga: $lga,
                title: (string) $request->get('title'),
                body: (string) $request->get('body'),
                category: $request->get('category'),
            );
        } catch (RuntimeException $e) {
            return json_encode(['error' => 'cannot_post', 'reason' => $e->getMessage()]);
        }

        return json_encode([
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
            'title' => $schema->string()->required()->description('Short headline for the issue. The agent should write this, not paste the user message verbatim.'),
            'body' => $schema->string()->required()->description('Full description, including any specific location details the user gave.'),
            'category' => $schema->string()->description('One of: roads, water, electricity, security, health, education, sanitation, other.'),
            'local_government_id' => $schema->integer(),
            'local_government_name' => $schema->string(),
        ];
    }
}
