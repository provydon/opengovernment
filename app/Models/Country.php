<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'iso2', 'iso3', 'name', 'slug',
    'currency_code', 'currency_symbol',
    'region_label', 'local_government_label',
    'identity_scheme', 'is_active',
])]
class Country extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
