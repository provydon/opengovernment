<?php

namespace App\Ai\Tools;

use App\Services\Civic\IssueSearchService;
use App\Services\Civic\LgaResolverService;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchSimilarIssuesAiTool implements Tool
{
    public function __construct(
        private IssueSearchService $issues,
        private LgaResolverService $lgas,
        private ?User $actor = null,
    ) {}

    public function description(): Stringable|string
    {
        return 'Search for existing open issues in a local government area that may already describe the same problem the user is reporting. Always call this BEFORE creating a new issue, so duplicates fold into the existing one instead of competing for votes.';
    }

    public function handle(Request $request): Stringable|string
    {
        $description = (string) $request->get('description');
        $lga = $this->lgas->resolve(
            $this->actor,
            id: $request->get('local_government_id'),
            name: $request->get('local_government_name'),
        );

        if (! $lga) {
            return json_encode([
                'error' => 'no_lga_resolved',
                'hint' => 'Ask the user which local government area they live in, then retry with local_government_name.',
            ]);
        }

        $threshold = (float) config('opengovernment.ai.duplicate_similarity_threshold', 0.78);

        $matches = $this->issues->findSimilar($description, $lga, limit: 5);

        return json_encode([
            'local_government' => ['id' => $lga->id, 'name' => $lga->name],
            'duplicate_threshold' => $threshold,
            'matches' => $matches->map(fn ($m) => [
                'id' => $m['issue']->id,
                'slug' => $m['issue']->slug,
                'title' => $m['issue']->title,
                'status' => $m['issue']->status,
                'upvotes' => $m['issue']->upvotes,
                'similarity' => $m['similarity'],
                'likely_duplicate' => $m['similarity'] >= $threshold,
            ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'description' => $schema->string()
                ->required()
                ->description('The problem the user is reporting, in their own words.'),
            'local_government_id' => $schema->integer()
                ->description('Numeric LGA id if known. Prefer this if you have it.'),
            'local_government_name' => $schema->string()
                ->description('Name of the local government area, e.g. "Ikeja" or "Westlands". Used when the id is unknown.'),
        ];
    }
}
