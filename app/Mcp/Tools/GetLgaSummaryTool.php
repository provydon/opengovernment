<?php

namespace App\Mcp\Tools;

use App\Services\Civic\LgaResolverService;
use App\Services\Civic\SpendingSearchService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get a rolling spending summary for a local government over the last N months, grouped by category.')]
class GetLgaSummaryTool extends Tool
{
    public function handle(Request $request): Response
    {
        $lga = app(LgaResolverService::class)->resolve(
            $request->user(),
            id: $request->get('local_government_id'),
            name: $request->get('local_government_name'),
        );

        if (! $lga) {
            return Response::error('Could not resolve a local government.');
        }

        $summary = app(SpendingSearchService::class)->lgaSummary(
            $lga,
            months: (int) $request->get('months', 12),
        );

        return Response::json($summary);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'local_government_id' => $schema->integer(),
            'local_government_name' => $schema->string(),
            'months' => $schema->integer()->description('Rolling window in months. Defaults to 12.'),
        ];
    }
}
