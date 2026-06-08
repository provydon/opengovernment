<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'local_government_id', 'published_by',
    'title', 'slug', 'category', 'description',
    'amount_minor', 'currency_code', 'vendor', 'spent_on',
    'source_document_url', 'published_at',
])]
class SpendingRecord extends Model
{
    protected function casts(): array
    {
        return [
            'spent_on' => 'date',
            'published_at' => 'datetime',
            'amount_minor' => 'integer',
        ];
    }

    public function localGovernment(): BelongsTo
    {
        return $this->belongsTo(LocalGovernment::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(GovernmentOfficial::class, 'published_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SpendingComment::class);
    }

    public function getAmountMajorAttribute(): float
    {
        return $this->amount_minor / 100;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
