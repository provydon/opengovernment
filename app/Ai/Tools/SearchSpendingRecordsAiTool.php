<?php

namespace App\Ai\Tools;

use App\Models\User;
use App\Services\Civic\LgaResolverService;
use App\Services\Civic\SpendingSearchService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchSpendingRecordsAiTool implements Tool
{
    public function __construct(
        private SpendingSearchService $spending,
        private LgaResolverService $lgas,
        private ?User $actor = null,
    ) {}

    public function description(): Stringable|string
    {
        return 'Search published government spending records. Use this when the user asks how much was spent on something, or who was paid, or wants a summary of an LGA\'s spending.';
    }

    public function handle(Request $request): Stringable|string
    {
        $lga = $this->lgas->resolve(
            $this->actor,
            id: $request->get('local_government_id'),
            name: $request->get('local_government_name'),
        );

        $records = $this->spending->search(
            query: (string) $request->get('query', ''),
            lga: $lga,
            limit: (int) $request->get('limit', 10),
        );

        return json_encode([
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
            'query' => $schema->string()->description('Free-text search. Searches titles, descriptions, vendors and categories.'),
            'local_government_id' => $schema->integer(),
            'local_government_name' => $schema->string(),
            'limit' => $schema->integer()->description('Max results to return (default 10).'),
        ];
    }
}
