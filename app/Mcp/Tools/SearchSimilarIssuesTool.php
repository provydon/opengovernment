<?php

namespace App\Mcp\Tools;

use App\Services\Civic\IssueSearchService;
use App\Services\Civic\LgaResolverService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Find existing open issues that may already describe the same problem the citizen is reporting in a given local government area.')]
class SearchSimilarIssuesTool extends Tool
{
    public function handle(Request $request): Response
    {
        $lgas = app(LgaResolverService::class);
        $issues = app(IssueSearchService::class);

        $lga = $lgas->resolve(
            null,
            id: $request->get('local_government_id'),
            name: $request->get('local_government_name'),
        );

        if (! $lga) {
            return Response::error('Could not resolve a local government. Pass local_government_id or local_government_name.');
        }

        $threshold = (float) config('opengovernment.ai.duplicate_similarity_threshold', 0.78);

        $matches = $issues->findSimilar((string) $request->get('description'), $lga, limit: 5);

        return Response::json([
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
            'description' => $schema->string()->required()->description('What the citizen is reporting, in plain language.'),
            'local_government_id' => $schema->integer(),
            'local_government_name' => $schema->string(),
        ];
    }
}
