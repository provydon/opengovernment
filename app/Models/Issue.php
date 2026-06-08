<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'local_government_id', 'user_id', 'issue_cluster_id',
    'title', 'slug', 'body', 'category',
    'upvotes', 'downvotes', 'score', 'status',
    'acknowledged_at', 'resolved_at',
])]
class Issue extends Model
{
    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function localGovernment(): BelongsTo
    {
        return $this->belongsTo(LocalGovernment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(IssueCluster::class, 'issue_cluster_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(IssueVote::class);
    }

    public function recomputeScore(): void
    {
        $this->upvotes = $this->votes()->where('value', 1)->count();
        $this->downvotes = $this->votes()->where('value', -1)->count();
        $this->score = $this->upvotes - $this->downvotes;
        $this->save();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
