<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'key',
        'prefix',
        'abilities',
        'rate_limit_per_minute',
        'rate_limit_per_hour',
        'total_requests',
        'last_used_at',
        'active',
        'expires_at',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    protected $hidden = [
        'key',
    ];

    public static function generateKey(): string
    {
        return 'cob_' . Str::random(40);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($apiKey) {
            if (empty($apiKey->key)) {
                $apiKey->key = self::generateKey();
            }
            if (empty($apiKey->prefix)) {
                $apiKey->prefix = substr($apiKey->key, 0, 10);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function incrementUsage(): void
    {
        $this->increment('total_requests');
        $this->update(['last_used_at' => now()]);
    }

    public function canMakeRequest(): bool
    {
        if (!$this->active || $this->isExpired()) {
            return false;
        }

        return true;
    }

    public function hasAbility(string $ability): bool
    {
        if (in_array('*', $this->abilities ?? [])) {
            return true;
        }

        return in_array($ability, $this->abilities ?? []);
    }
}
