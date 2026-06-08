<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['state_id', 'name', 'slug'])]
class LocalGovernment extends Model
{
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function spendingRecords(): HasMany
    {
        return $this->hasMany(SpendingRecord::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function officials(): HasMany
    {
        return $this->hasMany(GovernmentOfficial::class);
    }
}
