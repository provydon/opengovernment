<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['country_id', 'name', 'slug', 'capital', 'zone'])]
class State extends Model
{
    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function localGovernments(): HasMany
    {
        return $this->hasMany(LocalGovernment::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
