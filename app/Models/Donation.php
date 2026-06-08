<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'donor_name', 'donor_email',
    'amount_minor', 'currency_code', 'provider', 'provider_reference',
    'status', 'message', 'display_publicly',
])]
class Donation extends Model
{
    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'display_publicly' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
