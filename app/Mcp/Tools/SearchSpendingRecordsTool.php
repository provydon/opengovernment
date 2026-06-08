<?php

namespace App\Mcp\Tools;

use App\Services\Civic\LgaResolverService;
use App\Services\Civic\SpendingSearchService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Search published government spending records, optionally scoped to a specific local government area.')]
class SearchSpendingRecordsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $lga = app(LgaResolverService::class)->resolve(
            $request->user(),
            id: $request->get('local_government_id'),
            name: $request->get('local_government_name'),
        );

        $records = app(SpendingSearchService::class)->search(
            query: (string) $request->get('query', ''),
            lga: $lga,
            limit: (int) $request->get('limit', 10),
        );

        return Response::json([
            'local_government' => $lga ? ['id' => $lga->id, 'name' => $lga->name] : null,
            'records' => $records->map(fn ($r) => [
                'id' => $r->id,
                'slug' => $r->slug,
                'title' => $r->title,
                'category' => $r->category,
                'vendor' => $r->vendor,
                'amount_minor' => $r->amount_minor,
                'currency_code' => $r->currency_code,
                'spent_on' => $r->spent_on?->toDateString(),
            ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string(),
            'local_government_id' => $schema->integer(),
            'local_government_name' => $schema->string(),
            'limit' => $schema->integer(),
        ];
    }
}
