<?php

namespace App\Services\Civic;

use App\Models\Country;
use App\Models\LocalGovernment;
use App\Models\User;

class LgaResolverService
{
    /**
     * Resolve an LGA from one of: an explicit ID, the user's home LGA, or a
     * name match scoped to the deployment's home country. Returns null if
     * nothing matches — the agent should then ask the user to clarify.
     */
    public function resolve(?User $user, ?int $id = null, ?string $name = null): ?LocalGovernment
    {
        if ($id !== null) {
            return LocalGovernment::find($id);
        }

        if ($name !== null && trim($name) !== '') {
            $country = $user?->country ?? Country::where('iso2', config('opengovernment.default_country_iso2'))->first();

            $query = LocalGovernment::query();

            if ($country) {
                $query->whereHas('state', fn ($q) => $q->where('country_id', $country->id));
            }

            return $query->where('name', 'like', "%{$name}%")->first();
        }

        return $user?->localGovernment;
    }
}
