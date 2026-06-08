<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password',
    'country_id', 'state_id', 'local_government_id', 'phone',
    'primary_id_hash', 'secondary_id_hash',
    'verification_provider', 'verification_reference', 'identity_verified_at',
])]
#[Hidden(['password', 'remember_token', 'primary_id_hash', 'secondary_id_hash'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'identity_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function localGovernment(): BelongsTo
    {
        return $this->belongsTo(LocalGovernment::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function issueVotes(): HasMany
    {
        return $this->hasMany(IssueVote::class);
    }

    public function spendingComments(): HasMany
    {
        return $this->hasMany(SpendingComment::class);
    }

    public function isVerified(): bool
    {
        return $this->identity_verified_at !== null && ! $this->is_banned;
    }
}
