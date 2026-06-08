<?php

namespace App\Mcp\Tools;

use App\Models\Country;
use App\Models\LocalGovernment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List local government areas, optionally filtered by country (ISO2 code) or state slug.')]
class ListLocalGovernmentsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $query = LocalGovernment::query()->with('state.country');

        if ($iso = $request->get('country_iso2')) {
            $country = Country::where('iso2', strtoupper($iso))->first();
            if ($country) {
                $query->whereHas('state', fn ($q) => $q->where('country_id', $country->id));
            }
        }

        if ($stateSlug = $request->get('state_slug')) {
            $query->whereHas('state', fn ($q) => $q->where('slug', $stateSlug));
        }

        $lgas = $query->orderBy('name')->take((int) $request->get('limit', 100))->get();

        return Response::json([
            'count' => $lgas->count(),
            'local_governments' => $lgas->map(fn ($l) => [
                'id' => $l->id,
                'name' => $l->name,
                'slug' => $l->slug,
                'state' => $l->state?->name,
                'country' => $l->state?->country?->iso2,
            ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'country_iso2' => $schema->string()->description('Two-letter ISO country code, e.g. "NG".'),
            'state_slug' => $schema->string(),
            'limit' => $schema->integer(),
        ];
    }
}
