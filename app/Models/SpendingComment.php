<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['spending_record_id', 'user_id', 'body', 'is_hidden'])]
class SpendingComment extends Model
{
    protected function casts(): array
    {
        return ['is_hidden' => 'boolean'];
    }

    public function spendingRecord(): BelongsTo
    {
        return $this->belongsTo(SpendingRecord::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
