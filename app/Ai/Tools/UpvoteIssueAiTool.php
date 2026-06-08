<?php

namespace App\Ai\Tools;

use App\Models\Issue;
use App\Models\User;
use App\Services\Civic\IssueRegistrarService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use RuntimeException;
use Stringable;

class UpvoteIssueAiTool implements Tool
{
    public function __construct(
        private IssueRegistrarService $registrar,
        private ?User $actor = null,
    ) {}

    public function description(): Stringable|string
    {
        return 'Add the user\'s upvote to an existing issue. Use this when the user confirms that one of the similar issues you showed them is the same problem they were going to report.';
    }

    public function handle(Request $request): Stringable|string
    {
        if (! $this->actor) {
            return json_encode(['error' => 'not_authenticated']);
        }

        $issue = Issue::find($request->get('issue_id'));
        if (! $issue) {
            return json_encode(['error' => 'issue_not_found']);
        }

        try {
            $updated = $this->registrar->vote($this->actor, $issue, 1);
        } catch (RuntimeException $e) {
            return json_encode(['error' => 'cannot_vote', 'reason' => $e->getMessage()]);
        }

        return json_encode([
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
            'issue_id' => $schema->integer()->required()->description('Numeric id of the issue to upvote — taken from a prior search_similar_issues result.'),
        ];
    }
}
