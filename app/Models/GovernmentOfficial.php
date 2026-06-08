<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'local_government_id', 'name', 'email', 'official_title', 'password',
    'status', 'verification_notes', 'approved_at',
])]
#[Hidden(['password', 'remember_token'])]
class GovernmentOfficial extends Authenticatable
{
    use Notifiable;

    protected $table = 'government_officials';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'approved_at' => 'datetime',
        ];
    }

    public function localGovernment(): BelongsTo
    {
        return $this->belongsTo(LocalGovernment::class);
    }

    public function spendingRecords(): HasMany
    {
        return $this->hasMany(SpendingRecord::class, 'published_by');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
